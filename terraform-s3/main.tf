terraform {
  required_providers {
    aws = {
      source  = "hashicorp/aws"
      version = "~> 5.0"
    }
  }
}

provider "aws" {
  region = "ap-northeast-1"
}

resource "aws_s3_bucket" "videosharing" {
  bucket = "videosharing-bucket-mv1vaylj"
}

resource "aws_s3_object" "public_object" {
  bucket = aws_s3_bucket.videosharing.bucket
  key    = "public-file.txt"
  source = "/home/admin/video-sharing-app/S3/file2.txt"
}

resource "aws_iam_role_policy" "lambda_policy" {
  name   = "lambda_policy"
  role   = aws_iam_role.lambda_exec_role.id
  policy = jsonencode({
    Version = "2012-10-17"
    Statement = [
      {
        Action   = ["s3:GetObject"]
        Effect   = "Allow"
        Resource = "arn:aws:s3:::${aws_s3_bucket.videosharing.bucket}/*"
      },
      {
        Action   = "logs:*"
        Effect   = "Allow"
        Resource = "*"
      }
    ]
  })
}

resource "aws_lambda_function" "lambda_function" {
  filename         = "lambda_function.zip"  # Lambdaコードを自動的にアップロードする
  function_name    = "my_lambda_function"
  role             = aws_iam_role.lambda_exec_role.arn  # 既存のロールを参照
  handler          = "lambda_function.lambda_handler"  # lambda_function.py 内の関数名
  runtime          = "python3.8"

  # Lambdaコードの自動更新：ZIP化せず、ファイルをアップロードする
  source_code_hash = filebase64sha256("lambda_function.py")

  environment {
    variables = {
      BUCKET_NAME = aws_s3_bucket.videosharing.bucket  # S3 バケット名を直接参照
    }
  }
}

resource "aws_api_gateway_rest_api" "api" {
  name        = "video-sharing-api"
  description = "API for video sharing Lambda function"
}

resource "aws_api_gateway_resource" "lambda_resource" {
  rest_api_id = aws_api_gateway_rest_api.api.id
  parent_id   = aws_api_gateway_rest_api.api.root_resource_id
  path_part   = "get-url"
}

resource "aws_api_gateway_method" "get_url_method" {
  rest_api_id   = aws_api_gateway_rest_api.api.id
  resource_id   = aws_api_gateway_resource.lambda_resource.id
  http_method   = "GET"
  authorization = "NONE"
}

resource "aws_api_gateway_integration" "lambda_integration" {
  rest_api_id = aws_api_gateway_rest_api.api.id
  resource_id = aws_api_gateway_resource.lambda_resource.id
  http_method = aws_api_gateway_method.get_url_method.http_method
  integration_http_method = "POST"
  type                      = "AWS_PROXY"
  uri                       = "arn:aws:apigateway:${var.aws_region}:lambda:path/2015-03-31/functions/${aws_lambda_function.lambda_function.arn}/invocations"
}

resource "aws_api_gateway_deployment" "api_deployment" {
  rest_api_id = aws_api_gateway_rest_api.api.id
  depends_on  = [
    aws_api_gateway_integration.lambda_integration,
    aws_api_gateway_method.get_url_method
  ]
}

resource "aws_api_gateway_stage" "api_stage" {
  stage_name    = "prod"
  rest_api_id   = aws_api_gateway_rest_api.api.id
  deployment_id = aws_api_gateway_deployment.api_deployment.id
}

resource "aws_lambda_permission" "api_gateway_lambda" {
  statement_id  = "AllowAPIGatewayInvoke"
  action        = "lambda:InvokeFunction"
  principal     = "apigateway.amazonaws.com"
  function_name = aws_lambda_function.lambda_function.function_name
}
