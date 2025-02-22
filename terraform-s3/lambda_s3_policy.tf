resource "aws_iam_policy" "lambda_s3_policy" {
  name        = "lambda_s3_policy"
  description = "Policy to allow Lambda full access to S3"

  policy = jsonencode({
    Version = "2012-10-17"
    Statement = [
      {
        Action   = "s3:*"
        Effect   = "Allow"
        Resource = "*"
      }
    ]
  })
}

resource "aws_iam_policy" "lambda_s3_revoke_policy" {
  name        = "lambda_s3_revoke_policy"
  description = "Policy to limit Lambda's access to revoke video access"

  policy = jsonencode({
    Version = "2012-10-17"
    Statement = [
      {
        Action   = "s3:GetObject"
        Effect   = "Allow"
        Resource = "arn:aws:s3:::videosharing-bucket-mv1vaylj/*"  # バケット内のファイルへのアクセス許可
      },
      {
        Action   = "s3:PutObject"
        Effect   = "Deny"  # PutObjectを拒否（オブジェクトのアップロードを防ぐ）
        Resource = "arn:aws:s3:::videosharing-bucket-mv1vaylj/*"
      },
      {
        Action   = "s3:DeleteObject"
        Effect   = "Deny"  # オブジェクトの削除を拒否
        Resource = "arn:aws:s3:::videosharing-bucket-mv1vaylj/*"
      },
      {
        Action   = "s3:DeleteObjectVersion"
        Effect   = "Deny"  # オブジェクトのバージョン削除を拒否
        Resource = "arn:aws:s3:::videosharing-bucket-mv1vaylj/*"
      }
    ]
  })
}

resource "aws_lambda_function" "video_access_lambda" {
  function_name = "videoAccessLambda"
  role          = aws_iam_role.lambda_exec_role.arn
  handler       = "lambda_function.lambda_handler"
  runtime       = "python3.8"

  # 正しいZIPファイルのパスを指定
  s3_bucket     = "videosharing-bucket-mv1vaylj"
  s3_key        = "lambda_function.zip"
  source_code_hash = filebase64sha256("lambda_function.zip")  # 正しいファイルパス

  environment {
    variables = {
      VIDEO_BUCKET = "videosharing-bucket-mv1vaylj"
    }
  }

  depends_on = [
    aws_iam_policy.lambda_s3_revoke_policy
  ]
}

