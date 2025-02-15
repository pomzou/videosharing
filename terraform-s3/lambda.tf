resource "aws_iam_policy_attachment" "lambda_policy_attach" {
  name       = "lambda_policy_attachment"
  policy_arn = aws_iam_policy.lambda_role_policy.arn
  roles      = [aws_iam_role.lambda_exec_role.name]
}

resource "aws_iam_role_policy_attachment" "lambda_s3_policy_attach" {
  policy_arn = aws_iam_policy.lambda_s3_policy.arn
  role       = aws_iam_role.lambda_exec_role.name
}

resource "aws_iam_role_policy_attachment" "lambda_basic_execution_policy_attach" {
  policy_arn = aws_iam_policy.lambda_basic_execution_policy.arn
  role       = aws_iam_role.lambda_exec_role.name
}

