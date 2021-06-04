#!/bin/bash
docker pull mainnet/mainnet-rest
docker kill $(docker ps -q)
docker run -d --rm --env WORKERS=5 -p 127.0.0.1:3000:80 mainnet/mainnet-rest
