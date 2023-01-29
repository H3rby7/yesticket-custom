#!/usr/bin/env bash

BUILD_DIR=/build-tmp
OUTPUT_DIR=/build

echo "clearing ${BUILD_DIR}"
rm -rf $BUILD_DIR

echo "clearing existing build artifacts in ${OUTPUT_DIR}"
rm -rf $OUTPUT_DIR/*

echo "copying source files to ${BUILD_DIR}"
cp -r /app $BUILD_DIR

echo "entering ${BUILD_DIR}"
cd $BUILD_DIR

echo "removing unnecessary files"
rm -rf tests
rm composer.json
rm composer.lock
rm phpunit.xml

echo "Listing content of ${BUILD_DIR}"
ls -l

echo "Zipping ${BUILD_DIR}"

zip -r $OUTPUT_DIR/yesticket.zip ./*
