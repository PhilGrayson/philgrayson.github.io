---
layout: post
title:  "Shortening the downtime of a MySQL to PostgreSQL migration for Bitbucket"
---

*From 2 hours of downtime to 3 minutes.*

I've been working on migrating an installation of Bitbucket Server to the "Data
Center" licence. MySQL appears to [not be supported](https://confluence.atlassian.com/bitbucketserver/connecting-bitbucket-server-to-mysql-776640382.html)
with the Data Center licence, so we decided to move to PostgreSQL.

The database migration tool built into Bitbucket is really slow though. It took
2 hours to move a 1.2GiB database and during that time Bitbucket is unavailable.

![1 hour into a migration](/assets/images/bitbucket-migration-unavailable.png)
It's possible to have a much shorter migration time using [pgloader](https://github.com/dimitri/pgloader).
In my experience, using pgloader turned the 2 hours of Bitbucket downtime to
3 minutes of downtime.

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
not need migrating at all. But for completness sake I wanted to migrate it.
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

Update the bitbucket.properties file to point to the new PostgreSQL database:
```
jdbc.driver=org.postgresql.Driver
jdbc.url=jdbc:postgresql://some-postgresql-hostname:5432/bitbucket?targetServerType=master
jdbc.user=username
jdbc.password=password
```
and start the Bitbucket instance.
