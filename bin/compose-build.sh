#!/usr/bin/env bash
# MAKE SURE THIS FILE HAS 'LF' LINE-ENDINGS IF RUNNING IN DOCKER FOR IT TO RUN!
# For use in docker-compose.zip.yml

DIST_DIR=/src/dist

bash prepare-zip.sh /src
cd $DIST_DIR
zip -r /src/yesticket.zip ./*
rm -rf $DIST_DIR
