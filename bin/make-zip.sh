#!/usr/bin/env bash

# MAKE SURE THIS FILE HAS 'LF' LINE-ENDINGS FOR IT TO RUN!

SOURCE_DIR=/app
META_INF_DIR=/meta
BUILD_DIR=/build-tmp
LANGUAGE_DIR=$BUILD_DIR/languages
OUTPUT_DIR=/build

echo "clearing ${BUILD_DIR}"
rm -rf $BUILD_DIR

echo "clearing existing build artifacts in ${OUTPUT_DIR}"
rm -rf $OUTPUT_DIR/*

echo "copying source files from ${SOURCE_DIR} to ${BUILD_DIR}"
cp -r $SOURCE_DIR $BUILD_DIR

echo "copying meta files from ${META_INF_DIR} to ${BUILD_DIR}"
cp -r $META_INF_DIR/* $BUILD_DIR

echo "entering ${BUILD_DIR}"
cd $BUILD_DIR

echo "removing unnecessary files"
rm -rf tests
rm composer.json
rm composer.lock
rm phpunit.xml

echo "removing language backup files"
rm $LANGUAGE_DIR/*-backup-*
rm $LANGUAGE_DIR/.gitignore

echo "Listing content of ${LANGUAGE_DIR}"
ls $LANGUAGE_DIR

echo "Listing content of ${BUILD_DIR}"
ls -l

echo "Zipping ${BUILD_DIR}"

zip -r $OUTPUT_DIR/yesticket.zip ./*
