import json
import boto3
import os

# S3クライアントを作成
s3 = boto3.client("s3")

# Lambdaの環境変数からバケット名を取得
BUCKET_NAME = os.environ["BUCKET_NAME"]

def lambda_handler(event, context):
    # queryStringParametersが存在するかをチェック
    query_params = event.get("queryStringParameters", {})
    
    # 'file'パラメータが存在するかチェック
    object_key = query_params.get("file")
    if not object_key:
        return {
            "statusCode": 400,
            "body": json.dumps({"error": "Missing 'file' parameter"})
        }

    expiration = 3600  # 1時間 (3600秒)

    # 署名付きURLを生成
    try:
        url = s3.generate_presigned_url(
            "get_object",
            Params={"Bucket": BUCKET_NAME, "Key": object_key},
            ExpiresIn=expiration
        )
    except Exception as e:
        return {
            "statusCode": 500,
            "body": json.dumps({"error": "Could not generate the signed URL"})
        }

    return {
        "statusCode": 200,
        "body": json.dumps({"url": url})
    }
