# OwnBackup

OwnBackup is the simple database backup solutions for your [ownCloud](http://www.owncloud.com/) installation.

Place this app in your `owncloud/apps/` directory.

## Disclaimer

- Use this app at your own risk! Data loss may occur!
- This app is no replacement for a more professional backup solution!

## Features

- OwnBackup creates backups of your ownCloud tables via cronjob or manually.
- You are able to choose which tables from a certain backup you want to restore.
- Currently all backups older than 24h will be removed by the cronjob.

## Limitations

- Currently OwnBackup only works with table that have non-binary field types, all values will be escaped as string in the backup!
- Currently all users can access the app.
