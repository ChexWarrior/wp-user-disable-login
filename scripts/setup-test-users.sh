#!/usr/bin/env bash

wp --path=web user create author1 author1@example.com \
	--role=author --user_pass=password

# Create app password and store it in a file so test runner can read it
echo $(wp --path=web user application-password create \
	--porcelain author1 test-app) > .test_app_password

wp --path=web user create admin2 admin2@example.com \
 	--role=administrator --user_pass=password

wp --path=web user create author2 author2@example.com \
	--role=author --user_pass=password
