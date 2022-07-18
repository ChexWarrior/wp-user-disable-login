#!/usr/bin/env bash

rsync -az --delete \
	--exclude /user-login-disable/ packages/plugins/ web/wp-content/plugins