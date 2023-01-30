#!/usr/bin/env bash

# MAKE SURE THIS FILE HAS 'LF' LINE-ENDINGS IF RUNNING IN DOCKER FOR IT TO RUN!

if [ $# -lt 1 ]; then
	echo "usage: $0 <src-dir>"
	exit 1
fi

SOURCE_DIR=$1
BUILD_DIR=$1/dist

LANGUAGE_DIR=$BUILD_DIR/languages
CODE_DIR=$SOURCE_DIR/yesticket/*
OUT_ZIP_PATH=$SOURCE_DIR/yesticket.zip
CHANGELOG_PATH=$SOURCE_DIR/CHANGELOG.md

echo "preparing build-dir: ${BUILD_DIR}"
rm -rf $BUILD_DIR
mkdir $BUILD_DIR

echo "clearing existing build artifact ${OUT_ZIP_PATH}"
rm -f $OUT_ZIP_PATH

echo "copying source code files from ${CODE_DIR} to ${BUILD_DIR}"
cp -r $CODE_DIR $BUILD_DIR

echo "copying CHANGELOG from ${CHANGELOG_PATH} to ${BUILD_DIR}/"
cp $CHANGELOG_PATH $BUILD_DIR/

echo "entering ${BUILD_DIR}"
cd $BUILD_DIR

echo "removing unnecessary files"
rm -rf tests
rm composer.json
rm composer.lock
rm phpunit.xml

echo "removing language backup files"
rm -f $LANGUAGE_DIR/*-backup-*
rm -f $LANGUAGE_DIR/.gitignore

echo "Listing content of ${LANGUAGE_DIR}"
ls $LANGUAGE_DIR

echo "Listing content of ${BUILD_DIR}"
ls -l
