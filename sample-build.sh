#!/bin/bash

REPO_NAME = $1
REPO_PATH = $2

case "$REPO_NAME" in
"wp-foundation")
    echo "Build script executing for $REPO_NAME"
    ;;
*)
    echo "No build setup for this $REPO_NAME"
    ;;
esac