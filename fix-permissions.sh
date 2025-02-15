#!/bin/bash
sudo find src/ -type f -exec chmod 666 {} \;
sudo find src/ -type d -exec chmod 777 {} \;
sudo chown -R $USER:$USER src/
