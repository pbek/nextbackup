# OwnBackup

OwnBackup is the simple database backup solutions for your [ownCloud](http://www.owncloud.com/) installation.

Place this app in your `owncloud/apps/` directory.

Use this app at your own risk! Data loss may occur!

## Features
- creates backups of your ownCloud tables via cronjob or manually
- you are able to choose which tables from a certain backup you want to restore

## Limitations
- currently OwnBackup only works with table that have non-binary field types, all values will be escaped as string in the backup

## Running tests
After [Installing PHPUnit](http://phpunit.de/getting-started.html) run:

```bash
phpunit -c phpunit.xml
```
