#!/usr/bin/env bash

rsync -az --delete \
	--exclude /user-disable/ packages/plugins/ web/wp-content/plugins