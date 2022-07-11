#!/usr/bin/env bash

rsync -avz --delete \
	--exclude web/wp-content/plugins/user-disable packages/plugins/ web/wp-content/plugins