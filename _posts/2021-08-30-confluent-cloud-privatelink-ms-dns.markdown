---
layout: post
title:  Integrating Microsoft DNS, AWS Route 53, and AWS Private Link networked Kafka clusters though Confluent Cloud
---

## Background
The Private Link networking option for dedicated clusters in Confluent Cloud
requires setting up some DNS changes.

This guide explains one method on how to achieve the DNS changes when your DNS
server is Microsoft DNS.

There is no advanced technical insight here, aside from a slight difference
to the [AWS Private Link Confluent Cloud documentation](https://docs.confluent.io/cloud/current/networking/aws-privatelink.html)
in how to configure the Microsoft DNS server.
Instead, hopefully this guide can help someone who is not familiar with how to
implement things in AWS, or are confused with why and how to implement the
required DNS settings.

**Why chose Microsoft DNS?** It is simply the on-premise DNS server used at $dayjob.

**Why chose AWS Route 53?** Since this is about integrating AWS, Route 53 is
available to use and works well.

**Why not setup a new zone in Microsoft DNS?** The DNS server admins at $dayjob
didn't want to do that. They are much more comfortable with adding conditional
forwarders.

## A primer on why DNS changes are needed
My Kafka bootstrap FQDN is:
{% highlight text %}
lkc-djn5o-4ygw7.eu-west-1.aws.glb.confluent.cloud
{% endhighlight %}
and example Kafka broker FQDN is:
{% highlight text %}
e-23ab-euw1-az2-4ygw7.eu-west-1.aws.glb.confluent.cloud
{% endhighlight %}

For Kafka traffic to be routed via a Private Link, the A record(s) for those
addresses has to match the endpoint IP address(s) on my side of the Private Link.

The IP addresses(s) that should be returned by DNS are visible in the VPC
Endpoint screen:
[![VPC endpoint for Private Link](/assets/images/2021-08/vpc-endpoint.png){: width="300" }](/assets/images/2021-08/vpc-endpoint.png)

However, without any DNS changes the A records for that address are:
{% highlight shell %}
$ dig +noall +answer lkc-djn5o-4ygw7.eu-west-1.aws.glb.confluent.cloud

lkc-djn5o-4ygw7.eu-west-1.aws.glb.confluent.cloud. 122 IN CNAME lkc-djn5o.4ygw7.eu-west-1.aws.confluent.cloud.
lkc-djn5o.4ygw7.eu-west-1.aws.confluent.cloud. 15 IN A 10.1.39.112
lkc-djn5o.4ygw7.eu-west-1.aws.confluent.cloud. 15 IN A 10.1.22.153
lkc-djn5o.4ygw7.eu-west-1.aws.confluent.cloud. 15 IN A 10.1.7.203
{% endhighlight %}

The output means:
1. The first address is a CNAME to a similar address, but **without** `.glb.`.
1. The non-glb address then contains 3 A records starting `10.1.`.

This DNS answer is wrong for my on-premise lookup, because I only have one VPC
endpoint (not 3) and the VPC endpoint is `10.228.7.185` which does not match any
of the `10.1.` addresses.

The 3 IP addresses are effectively nonsense. They are some private IPs,
presumable within some Confluent Cloud VPC that means nothing to us.

### Why doesn't Confluent Cloud automatically setup DNS?
Confluent Cloud cannot setup DNS for Private Link connectivity for two reasons:
1. Confluent Cloud does not know the VPC endpoint IP address(s) in your VPC.
Confluent Cloud setup up a VPC "Endpoint Service" which is effectively a load
balancer to Kafka brokers within Confluent Cloud's VPC, and AWS networking magic
make the Private Link connectivity work by creating an Elastic Network Interface
in your VPC and forwards the traffic to the load balancer.
2. A Kafka cluster can be linked to more than one VPC, and a VPC can chose any
private IP CIDR range. The source of the Kafka client traffic determines which
Private Link to use. Confluent Cloud cannot make that choice for you.


## Setting up the Route 53 side
The [Confluent Cloud documentation](https://docs.confluent.io/cloud/current/networking/aws-privatelink.html) does a fair job of explaining what DNS changes
are needed in Route 53.

[![Route 53 Zone](/assets/images/2021-08/route-53-zone-1.png){: width="300" }](/assets/images/2021-08/route-53-zone-1.png)
[![Route 53 Zone records](/assets/images/2021-08/route-53-zone-2.png){: width="300" }](/assets/images/2021-08/route-53-zone-2.png)

Add inbound endpoints to enable Microsoft DNS to connect to VPC's Route 53 DNS. At least 2 endpoints is recommended.
[![Route 53 inbound endpoints](/assets/images/2021-08/route-53-inbound-endpoint.png){: width="300" }](/assets/images/2021-08/route-53-inbound-endpoint.png)


## Setting up the Microsoft DNS side
Ensure UDP and TCP port 53 connectivity from the DNS servers to the inbound
endpoints (eg via some form of VPN tunnel).

Add the following conditional forwarders, using the inbound endpoints as the name servers.
* <region name, eg eu-west-1>.aws.confluent.cloud.
* <region name, eu-west-1>.aws.glb.confluent.cloud.

A more general conditional forwarder of `confluent.cloud.` could also work. However
you may not want AWS Route 53 controlling resolution of Kafka clusters in other
clouds like Azure and GCP.

Now DNS lookups to the Kafka bootstrap and example broker FQDN will work, eg:

{% highlight shell %}
$ dig +noall +answer lkc-djn5o-4ygw7.eu-west-1.aws.glb.confluent.cloud
lkc-djn5o-4ygw7.eu-west-1.aws.glb.confluent.cloud. 301 IN CNAME lkc-djn5o.4ygw7.eu-west-1.aws.confluent.cloud.
vpce-00000000000000000-00000000.vpce-svc-00000000000000000.eu-west-1.vpce.amazonaws.com. 60 IN A 10.228.7.185

$ dig +noall +answer e-23ab-euw1-az2-4ygw7.eu-west-1.aws.glb.confluent.cloud
e-23ab-euw1-az2-4ygw7.eu-west-1.aws.glb.confluent.cloud. 302 IN CNAME e-23ab.euw1-az2.4ygw7.eu-west-1.aws.confluent.cloud.
e-23ab.euw1-az2.4ygw7.eu-west-1.aws.confluent.cloud. 60 IN CNAME vpce-00000000000000000-00000000.vpce-svc-00000000000000000.eu-west-1.vpce.amazonaws.com.
vpce-00000000000000000-00000000.vpce-svc-00000000000000000.eu-west-1.vpce.amazonaws.com. 45 IN A 10.228.7.185
{% endhighlight %}
