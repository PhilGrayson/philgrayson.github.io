---
layout: post
title: A way to pretty format Slack messages from AWS EventBridge (formally CloudWatch Events)
---

I decided to setup a Slack message when an AWS KMS key is scheduled for deletion.
This is part of a defence-in-depth approach to preventing production issues due
to key deletions.

The KMS key is being used by Confluent Cloud, a 3rd party supplier of Kafka. Therefore
the [recipe to detect usage of a AWS KMS key pending deletion](https://docs.aws.amazon.com/kms/latest/developerguide/deleting-keys-creating-cloudwatch-alarm.html)
wouldn't be useful since usage of the KMS key would be from an AWS account I
don't control.

The default Slack message is effectively a json dump:
[![Before](/assets/images/2021-09/cloudwatch-slack-before.png)](/assets/images/2021-09/cloudwatch-slack-before.png)

With some configuration, the message format now looks like:
[![After](/assets/images/2021-09/cloudwatch-slack-after.png)](/assets/images/2021-09/cloudwatch-slack-after.png)

## Show me the code!
The configuration is managed by Terraform. Here it is!
{% highlight terraform %}
resource "aws_cloudwatch_event_rule" "kms-delete" {
  name        = "detect-kms-key-deletion"
  description = "A CloudWatch Event Rule that triggers on AWS KMS key deletion events."
  is_enabled  = true
  event_pattern = jsonencode({
    source      = ["aws.kms"]
    detail-type = ["AWS API Call via CloudTrail"],
    detail = {
      eventSource = ["kms.amazonaws.com"],
      eventName   = ["ScheduleKeyDeletion"]
    }
  })
}

resource "aws_cloudwatch_event_target" "kms-delete-slack" {
  rule      = aws_cloudwatch_event_rule.kms-delete.name
  target_id = "kms-delete-event"
  arn       = module.notify_slack.this_slack_topic_arn

  input_transformer {
    input_paths = {
      pendingDays  = "$.detail.requestParameters.pendingWindowInDays",
      keyId        = "$.detail.requestParameters.keyId",
      region       = "$.detail.awsRegion",
      deletionDate = "$.detail.responseElements.deletionDate"
    }

    // This is a Slack message body
    // using `jsonencode()` is possible here, but that function mangles < and >
    // characters into \u003 and \u003e because of compatibility with Terraform
    // 0.11.
    // https://github.com/hashicorp/terraform/pull/18871
    input_template = <<-EOF
      {
        "text": "The KMS key <https://<region>.console.aws.amazon.com/kms/home?region=<region>#/kms/keys/<keyId>|<keyId>> has been scheduled for deletion on <deletionDate> (in <pendingDays> days)",
        "attachments": [
          {
            "fields": [
              {
                "short": false,
                "title": "Action",
                "value": "Please verify this is expected and the key not being used by a Confluent Cloud Kafka cluster."
              }, {
                "short": true,
                "title": "Runbook",
                "value": "<https://example.com|Some link>"
              }, {
                "short": true,
                "title": "AWS account name",
                "value": "Confluent Cloud Prod"
              }, {
                "short": true,
                "title": "Configuration Source",
                "value": "<https://example.com|Link to git repo>"
              }
            ]
          }
        ]
      }
    EOF
  }
}

module "notify_slack" {
  source  = "terraform-aws-modules/notify-slack/aws"
  version = "~> 4.0"

  sns_topic_name = "slack-notifications"

  slack_webhook_url = "https://hooks.slack.com/services/aaaa/bbbb/ccccc"
  slack_channel     = "some-slack-channel"
  slack_username    = "CloudWatch for Confluent Cloud Kafka"
  slack_emoji       = ":cloudwatch:"

  cloudwatch_log_group_retention_in_days = 1
}
{% endhighlight %}
