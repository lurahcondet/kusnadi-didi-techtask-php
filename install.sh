#!/bin/bash

composer install --ignore-platform-reqs -vvv

docker build -t mysite .

docker run -p 8080:80 -d mysite

docker ps