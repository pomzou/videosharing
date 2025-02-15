resource "aws_iam_policy" "lambda_basic_execution_policy" {
  name        = "lambda_basic_execution_policy"
  description = "Policy to allow Lambda basic execution role"

  policy = jsonencode({
    Version = "2012-10-17"
    Statement = [
      {
        Action   = [
          "logs:CreateLogGroup",
          "logs:CreateLogStream",
          "logs:PutLogEvents"
        ]
        Effect   = "Allow"
        Resource = "*"
      }
    ]
  })
}
