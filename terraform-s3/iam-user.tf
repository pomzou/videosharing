resource "aws_iam_user" "my_user" {
  name = "my-terraform-user"
}

resource "aws_iam_access_key" "my_user_access_key" {
  user = aws_iam_user.my_user.name
}

resource "aws_iam_user_policy_attachment" "my_user_policy" {
  user       = aws_iam_user.my_user.name
  policy_arn = "arn:aws:iam::aws:policy/AmazonS3FullAccess"  # S3アクセス権限
}

output "aws_access_key_id" {
  value     = aws_iam_access_key.my_user_access_key.id
  sensitive = false  # 設定しないと表示されないので、falseにして表示させる
}

output "aws_secret_access_key" {
  value     = aws_iam_access_key.my_user_access_key.secret
  sensitive = true
}