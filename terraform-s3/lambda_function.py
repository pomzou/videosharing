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

    # 'action'パラメータを取得して、アクセスをリボークするか、署名付きURLを生成するかを決定
    action = query_params.get("action")
    
    if action == "revoke":
        # アクセスをリボークする
        try:
            policy = {
                "Version": "2012-10-17",
                "Statement": [
                    {
                        "Effect": "Deny",
                        "Action": "s3:GetObject",
                        "Resource": f"arn:aws:s3:::{BUCKET_NAME}/{object_key}"  # 指定されたオブジェクトへのアクセスを拒否
                    }
                ]
            }
            # バケットポリシーを更新
            s3.put_bucket_policy(
                Bucket=BUCKET_NAME,
                Policy=json.dumps(policy)
            )
            return {
                "statusCode": 200,
                "body": json.dumps({"message": f"Access to {object_key} has been revoked"})
            }
        except Exception as e:
            return {
                "statusCode": 500,
                "body": json.dumps({"error": f"Could not revoke access: {str(e)}"})
            }
    
    # 署名付きURLを生成
    expiration = 3600  # 1時間 (3600秒)
    try:
        url = s3.generate_presigned_url(
            "get_object",
            Params={"Bucket": BUCKET_NAME, "Key": object_key},
            ExpiresIn=expiration
        )
        return {
            "statusCode": 200,
            "body": json.dumps({"url": url})
        }
    except Exception as e:
        return {
            "statusCode": 500,
            "body": json.dumps({"error": "Could not generate the signed URL"})
        }
