---
layout: post
title:  "Shortening the downtime of a MySQL to PostgreSQL migration for Bitbucket"
---

*From 2.5 hours of downtime to 5 minutes.*

I've been working on migrating an installation of Bitbucket Server to the "Data
Center" licence. MySQL appears to [not be supported](https://confluence.atlassian.com/bitbucketserver/connecting-bitbucket-server-to-mysql-776640382.html)
with the Data Center licence, so we decided to move to PostgreSQL.

The database migration tool built into Bitbucket is really slow though. It took
2 hours to move a 1.2GiB database and during that time Bitbucket is unavailable.

![1 hour into a migration](/assets/images/bitbucket-migration-unavailable.png)
It's possible to have a much shorter migration time using [pgloader](https://github.com/dimitri/pgloader).
In my experience, using pgloader turned the 2.5 hours of Bitbucket downtime to
5 minutes of downtime.

## Steps
### 1. Get a PostgreSQL schema for your Bitbucket installation
{% highlight shell %}
pg_dump --schema-only --create bitbucket > schema.sql
{% endhighlight %}

The safest method is to copy your production Bitbucket MySQL database into a
test environment and perform a migration using the built in (slow) method.

Another method is to stand up a Bitbucket installation in Docker.

{% highlight shell %}
# start a throw away postgresql server
docker run -it --rm --name postgres -e POSTGRES_PASSWORD=password postgres

# start a throw away Bitbucket server
docker run -e JDBC_DRIVER=org.postgresql.Driver \
           -e JDBC_URL=jdbc:postgresql://postgres/postgres \
           -e JDBC_USER=postgres -e JDBC_PASSWORD=password \
           -p 7990:7990 \
           --link postgres:postgres \
           atlassian/bitbucket-server

# wait for Bitbucket to finish initializing

# get the schema
docker run --rm --link postgres:postgres -e PGPASSWORD=password postgres \
       pg_dump --schema-only --create -h postgres -U postgres postgres > schema.sql
{% endhighlight %}

In any case, ensure the version of the test Bitbucket server is the same as your
production version. Also ensure the same plugins (and plugin versions) are
installed.

### 2. Copy the databasechangelog tables from PostgreSQL
The contents of `databasechangelog` are database product specific. If you copy
rows from the MySQL database, Bitbucket will fail to start up.

The contents of `databasechangeloglock` are not specific, and actually might
not need migrating at all. But for completeness sake I wanted to migrate it.
The way I used `pgloader` means it cannot automatically migrate the contents
from MySQL because the table name changed from upper case in MySQL to lower case
in PostgreSQL. Only these two table are renamed for some reason.

{% highlight shell %}
pg_dump --data-only --table databasechangelog --table databasechangeloglock bitbucket > databasechangelog.sql
{% endhighlight %}

### 3. Apply the .sql files to your PostgreSQL server
{% highlight shell %}
psql < schema.sql
psql bitbucket < databasechangelog.sql
{% endhighlight %}

### 4. Use pgloader to copy the rest of the data
Get [pgloader](https://github.com/dimitri/pgloader) in a place where it can
connect to both MySQL and PostgreSQL database.

Create a file like pgloader-commands.txt with these contents:
```
LOAD DATABASE
FROM mysql://username:password@some-mysql-hostname/bitbucket
INTO postgresql://username:password@some-postgresql-hostname/bitbucket

WITH quote identifiers,
     truncate,
     data only

EXCLUDING TABLE NAMES MATCHING 'databasechangelog', 'databasechangeloglock'

ALTER SCHEMA 'bitbucket' RENAME TO 'public'
```

Stop the production Bitbucket instance and run the migration using:
{% highlight shell %}
pgloader pgloader-commands.txt
{% endhighlight %}

A bunch of warnings will print about data type casting, but since pgloader
is only migrating rows and not building a schema, the warnings don't impact
the accuracy of the migration.

Here is the last output of pgloader, showing it migrated 2.1GB of rows in 4m32s.
```
                             table name     errors       rows      bytes      total time
---------------------------------------  ---------  ---------  ---------  --------------
[...]
---------------------------------------  ---------  ---------  ---------  --------------
                COPY Threads Completion          0          4                  3m55.702s
                        Reset Sequences          0         60                     0.228s
                    Create Foreign Keys          0        137                    36.924s
                       Install Comments          0         26                     0.019s
---------------------------------------  ---------  ---------  ---------  --------------
                      Total import time          ✓   25660619     2.1 GB       4m32.873s
```


Update the bitbucket.properties file to point to the new PostgreSQL database:
```
jdbc.driver=org.postgresql.Driver
jdbc.url=jdbc:postgresql://some-postgresql-hostname:5432/bitbucket?targetServerType=master
jdbc.user=username
jdbc.password=password
```
and start the Bitbucket instance.

### Verify migration accuracy
I wanted to compare the contents of the PostgreSQL database generated by the
database migration tool built into Bitbucket vs the database generated by this
faster method.

Diffing against `pg_dump` outputs is not fun as the order of row output can
change, presumably on tables with compound primary keys.

I adopted [this Stack Overflow answer](https://stackoverflow.com/questions/2204640/sorting-postgresql-database-dump-pg-dump/2207950#2207950)
to generate an ordered dump of a database. The only changes are:
1. quote the table names as some Bitbucket tables are in upper case
1. print the table name so you can find what table has a difference

Create a generator.sql file:
{% highlight sql %}
select
    'select '''||r.relname||''';'||E'\n'||'copy (select * from "'||r.relname||'" order by '||
    array_to_string(array_agg('"'||a.attname||'"'), ',')||
    ') to STDOUT;'
from
    pg_class r,
    pg_constraint c,
    pg_attribute a
where
    r.oid = c.conrelid
    and r.oid = a.attrelid
    and a.attnum = ANY(conkey)
    and contype = 'p'
    and relkind = 'r'
group by
    r.relname
order by
    r.relname
{% endhighlight %}

and run it against the source-of-truth database (the one generated by the
database migration tool built into Bitbucket).

{% highlight shell %}
psql old_bitbucket < generator.sql > dumper.sql
{% endhighlight %}

and then run the dumper.sql file against both the old and new database.

{% highlight shell %}
psql old_bitbucket < dumper.sql > old_bitbucket.sql
psql new_bitbucket < dumper.sql > new_bitbucket.sql
{% endhighlight %}

I found there was always *some* changes:
1. A different of 1 to the `last.licensed.user.count` field in the `app_property`
table.
1. A few changed rows in `bb_clusteredjob`. Likely some jobs are scheduled
between the time the migration finished and Bitbucket shutting down.
1. Changes to `com.atlassian.crowd.directory.sync.laststartsynctime` in
`cwd_directory_attribute` table.
1. A missing `MigrationSucceededEvent` row in `AO_C77861_AUDIT_ENTITY` table.
This row is written during the built in migration **to the new database**, not
the old database.
1. Some changes in `cwd_membership`, `cwd_group`, `cwd_tombstone` etc if an external
user directory sync has made some changes.
