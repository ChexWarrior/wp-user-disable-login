#!/usr/bin/env bash

SITE_URL="$1"

if [ -z "$SITE_URL" ]; then
	echo 'You must specify a url for the testing site!'
	exit 1
fi

docker run -it --rm --net=host \
	-e BASE_URL="$SITE_URL" \
	-e APP_PASSWORD="E933qT3tzhxdrSXLwsfncvYE" \
	-v $PWD:/usr/src/app \
	-w /usr/src/app \
	mcr.microsoft.com/playwright:v1.23.1-focal \
	npx playwright test --config=playwright.config.js
