#!/usr/bin/env bash

docker run -it --rm --net=host \
	-v $PWD:/usr/src/app \
	-w /usr/src/app \
	mcr.microsoft.com/playwright:v1.23.1-focal \
	npx playwright test --config=playwright.config.js
