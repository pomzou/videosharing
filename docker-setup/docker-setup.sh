#!/bin/bash

# システムパッケージの更新
sudo apt-get update
sudo apt-get upgrade -y

# 必要なパッケージのインストール
sudo apt-get install -y \
    apt-transport-https \
    ca-certificates \
    curl \
    software-properties-common \
    git

# Dockerの公式GPGキーを追加
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /usr/share/keyrings/docker-archive-keyring.gpg

# Dockerのリポジトリを追加
echo \
  "deb [arch=$(dpkg --print-architecture) signed-by=/usr/share/keyrings/docker-archive-keyring.gpg] https://download.docker.com/linux/ubuntu \
  $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

# Dockerをインストール
sudo apt-get update
sudo apt-get install -y docker-ce docker-ce-cli containerd.io

# 現在のユーザーをdockerグループに追加
sudo usermod -aG docker $USER

# Docker Composeのインストール
sudo curl -L "https://github.com/docker/compose/releases/download/v2.23.0/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose

# Dockerサービスを開始
sudo systemctl start docker
sudo systemctl enable docker

# バージョン確認
docker --version
docker-compose --version

echo "Dockerのインストールが完了しました。システムを再起動してください。"
