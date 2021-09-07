---
layout: post
title: An implementation difference in CNAME lookups between BIND 9 and Microsoft DNS
---

There is an interesting difference in DNS result between BIND 9 and Microsoft
DNS when handling an admittedly unusual DNS lookup.

This will be known by anyone who's:
1. configured a dedicated Confluent Cloud Kafka cluster via AWS/Azure Private Link
2. AND integrated with Microsoft DNS

Confluent Cloud [Azure Private Link integration](https://docs.confluent.io/cloud/current/networking/azure-privatelink.html)
documentation briefly mentions this issue (CTRL+F "Windows DNS").

Imagine the following lookup:
{% highlight shell %}
$ dig +noall +answer lkc-djn5o-4ygw7.eu-west-1.aws.glb.confluent.cloud

lkc-djn5o-4ygw7.eu-west-1.aws.glb.confluent.cloud. 122 IN CNAME lkc-djn5o.4ygw7.eu-west-1.aws.confluent.cloud.
lkc-djn5o.4ygw7.eu-west-1.aws.confluent.cloud. 15 IN A 1.1.1.1
{% endhighlight %}

and a conditional forwarder (MS DNS) or forward zone (BIND 9) on the<br />
`4ygw7.eu-west-1.aws.confluent.cloud.` domain which points to a zone like:
{% highlight text %}
@  IN  SOA    example.com. example.com (
               1            ; serial number
               3600         ; refresh period
               600          ; retry period
               604800       ; expire time
               1800 )       ; minimum ttl
@  IN  NS     example.com.

*  IN  CNAME  example.com
{% endhighlight %}

If querying a Microsoft DNS server, the answer will be
{% highlight shell %}
$ dig +noall +answer lkc-djn5o-4ygw7.eu-west-1.aws.glb.confluent.cloud

lkc-djn5o-4ygw7.eu-west-1.aws.glb.confluent.cloud. 122 IN CNAME lkc-djn5o.4ygw7.eu-west-1.aws.confluent.cloud.
lkc-djn5o.4ygw7.eu-west-1.aws.confluent.cloud. 15 IN A 1.1.1.1
{% endhighlight %}

If querying a BIND 9 DNS server, the answer will be
{% highlight shell %}
$ dig +noall +answer lkc-djn5o-4ygw7.eu-west-1.aws.glb.confluent.cloud

lkc-djn5o-4ygw7.eu-west-1.aws.glb.confluent.cloud. 259 IN CNAME lkc-djn5o.4ygw7.eu-west-1.aws.confluent.cloud.
lkc-djn5o.4ygw7.eu-west-1.aws.confluent.cloud. 1758 IN CNAME google.com.
google.com.             258     IN      A       2.2.2.2
{% endhighlight %}

Microsoft DNS does not consult the conditional forwards when a DNS lookup
contains a CNAME.

There is one of three explanations for this behaviour:
1. Microsoft DNS is not following the DNS specification
1. BIND 9 is not following the DNS specification
1. Forwarders is not part of the DNS specification

In my untrained opinion, the last option seems most likely. In [the latest DNS RFC](https://datatracker.ietf.org/doc/html/rfc1035)
the only mention of "forward" is related to mail.

## Diagrams showing the lookup process
The following activity diagrams may help aid you to understand in the difference.

### Looking up via BIND 9
[![BIND 9 lookup activity diagram](/assets/images/2021-09/bind9-msdns-difference.bind9.png)](/assets/images/2021-09/bind9-msdns-difference.bind9.png)
The diagram is generated using PlantUML with [this markup](/assets/documents/2021-09/bind9-msdns-difference.bind9.plantuml).

### Looking up via Microsoft DNS
[![MS DNS lookup activity diagram](/assets/images/2021-09/bind9-msdns-difference.ms.png)](/assets/images/2021-09/bind9-msdns-difference.ms.png)
The diagram is generated using PlantUML with [this markup](/assets/documents/2021-09/bind9-msdns-difference.ms.plantuml).


#### When the A record expires
The Conditional forwarder in Microsoft DNS will be used when the A record expires.

[![MS DNS lookup activity diagram](/assets/images/2021-09/bind9-msdns-difference.ttlexpire.png)](/assets/images/2021-09/bind9-msdns-difference.ttlexpire.png)

This causes a flip-flopping of DNS results based on TTL expiry. The following video
(2.7MB, 55 seconds) shows the behaviour.

<video id="video" controls style="width: 100%;">
  <source src="/assets/documents/2021-09/bind9-msdns-difference.webm" type="video/webm">
  Your browser does not support the <code>video</code> element.
</video>
