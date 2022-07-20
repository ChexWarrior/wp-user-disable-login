#!/usr/bin/env bash

PLUGIN_PATH='/var/www/html/web/wp-content/plugins/user-login-disable'
DDEV_EXEC="ddev --dir=$PLUGIN_PATH exec"

# Ensure we don't include any dev composer packages
$DDEV_EXEC rm -rf vendor tests
$DDEV_EXEC composer install --no-dev -o
