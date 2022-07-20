# User Login Disable WordPress Plugin

## Description
This plugin allows administrators to enable or disable other user account's logins.

When a user is disabled:
* They can not login to the site
* They are immediately logged out of the site
* Any [application passwords](https://make.wordpress.org/core/2020/11/05/application-passwords-integration-guide/) created with this user will no longer authenticate with the WP REST API

## Usage
Users can be enabled or disabled in their user profile and within the admin user list page.

### WP-CLI Integration
This plugin integrates with WP-CLI and provides the following two commands:

```bash
# Enables the target users
wp user enable <List of User IDs, Logins or Emails> [--all]

# Disables the target users
wp user disable <List of User IDs, Logins or Emails> [--all]
```

## Development
This site uses [DDEV](https://ddev.com/) for local development, see that project's instructions for installation and setup.

### Plugin Files
The actual plugin files that appear on the WP Plugin directory are located in `web/wp-content/plugins/user-login-disable`.

### Tests
This site uses [Playwright](https://playwright.dev/) for its integration tests and [PHPUnit](https://phpunit.de/) for its unit tests.

#### Integration Tests
To run the integration tests first ensure you've installed the required dependencies, since this project uses DDEV it is recommended to use a Docker container to install the dependencies:
```bash
# From repository root
docker run --rm -it \
  -v $PWD:/usr/src/app -w /usr/src/app \
  node:16 npm install
```

To run the actual integration tests:
```bash
npm test $DEV_SITE_URL
```

#### Unit Tests
**TBD**
