#!/usr/bin/env bash

ddev --dir=/var/www/html/web/wp-content/plugins/user-login-disable \
	exec ./vendor/bin/phpunit tests
