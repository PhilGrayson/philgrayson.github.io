---
layout: post
title:  "Diffing Active Directory group membership"
---

[philgrayson/ldap-group-missing-users.git](https://github.com/PhilGrayson/ldap-group-missing-users).

At *$work* there was a project to bring order to an old but still actively used
Active Directory. Part of the project was to replace group objects with new
groups that followed a naming convention and usage standard.

Some of the old group names were really vaguely named and used by many services
for group based authorisation. The thought at the time was "Lets piggyback off
this generic group since most people already belong to it". That wasn't allowed
any more, services have to authorise users using a specific group.

To support the migration of users to the new groups, I wrote a script that will
compare the membership of two groups (including nesting) and print user objects
that do not belong to the new group. Any output the group meant that those users
would lose access to a service once the old group was decommissioned.

An example of the output might look like:
```
92 users in the original group(s), 2 missing users (2% reduction)
    CN=John Smith,OU=Users,DC=example,DC=com
    CN=Jane Smith,OU=Users,DC=example,DC=com
```
I could then work towards getting those two users added to the new group.

[philgrayson/ldap-group-missing-users.git](https://github.com/PhilGrayson/ldap-group-missing-users).
