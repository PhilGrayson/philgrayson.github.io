---
layout: post
title:  Bitbucket is stuck on "Started Bitbucket Japanese (Japan) Language Pack"
---

You may find that after you've performed a Bitbucket upgrade, the Bitbucket UI
is stuck starting on "Started Bitbucket Japanese (Japan) Language Pack". The last
entry in the Bitbucket logs look like:
```
[spring-startup]  c.a.s.i.p.s.OsgiBundledPathScanner Cannot scan directory /extension/build-status/ in bundle tac.bitbucket.languages.ja_JP; it does not exist
[spring-startup]  c.a.s.i.p.s.OsgiBundledPathScanner Cannot scan directory /com/atlassian/oauth/shared/servlet/ in bundle tac.bitbucket.languages.ja_JP; it does not exist
```
which might lead you to think there is an error loading something.

One possibility is that Bitbucket is performing database migrations. Check for
activity on your database, is it under load? If so, you'll just have to wait for
the migrations to complete.

In my experience it took 10 minutes for the migrations to complete. Most of the
time was spent altering the `AO_C77861_AUDIT_ENTITY` table which had 1.25 million
rows.

A Bitbucket upgrade from 7.3 to 7.8 had the following ALTER TABLE queries:
{% highlight sql %}
ALTER TABLE AO_C77861_AUDIT_ENTITY ADD COLUMN ACTION_T_KEY VARCHAR(255)
ALTER TABLE AO_C77861_AUDIT_ENTITY ADD COLUMN RESOURCE_TYPE_3 VARCHAR(255)
ALTER TABLE AO_C77861_AUDIT_ENTITY ADD COLUMN RESOURCE_ID_3 VARCHAR(255)
ALTER TABLE AO_C77861_AUDIT_ENTITY ADD COLUMN RESOURCE_TYPE_4 VARCHAR(255)
ALTER TABLE AO_C77861_AUDIT_ENTITY ADD COLUMN RESOURCE_ID_4 VARCHAR(255)
ALTER TABLE AO_C77861_AUDIT_ENTITY ADD COLUMN RESOURCE_TYPE_5 VARCHAR(255)
ALTER TABLE AO_C77861_AUDIT_ENTITY ADD COLUMN RESOURCE_ID_5 VARCHAR(255)
ALTER TABLE AO_C77861_AUDIT_ENTITY ADD COLUMN CATEGORY_T_KEY VARCHAR(255)
{% endhighlight %}
