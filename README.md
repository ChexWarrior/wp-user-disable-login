# User Disable WordPress Plugin

## Description
This plugin simply allows administrators to enable or disable other user accounts.  If a user is disabled they will be unable to login to the site (and any existing sessions between the site and that user will be destroyed).

Users can be enabled or disabled in their user profile and within the admin user list page.

### WP-CLI Integration
This plugin integrates with WP-CLI and provides the following two commands:

```bash
wp user enable <List of User IDs>

wp user disable <List of User IDs>
```