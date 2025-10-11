import json

def lambda_handler(event, context):
    """
    Skeleton AWS Lambda function handler.
    Args:
        event (dict): Event data passed to the function
        context (LambdaContext): Runtime information
    Returns:
        dict: Response object
    """
    return {
        'statusCode': 200,
        'body': json.dumps('Hello from Lambda!')
    }
