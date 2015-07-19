# OwnBackup

OwnBackup is the simple database backup solutions for your [ownCloud](http://www.owncloud.com/) 8.1+ installation.

It's main purpose is to provide an easy solution to restore the tables of an app in case you accidentally corrupted the data or did anything you regret later.  

Place this app in your `owncloud/apps/` directory.

## Disclaimer

- Use this app at your own risk! Data loss may occur!
- This app is no replacement for a more professional backup solution!

## Features

- OwnBackup creates backups of your ownCloud tables hourly via cronjob or manually.
- You are able to choose which tables from a certain backup you want to restore.
- Currently all backups older than 24h will be removed by the cronjob.
- If the Zlib library is present the backups get compressed by gzip.

## Limitations

- Currently OwnBackup only works with table that have non-binary field types, all values will be escaped as string in the backup!
- Currently all users can access the app.
- I've only tested the app with MySQL so far, please report any troubles with other databases.
- I've only tested the app with *Cron* so far, please report any troubles with *Webcron* or *AJAX*.
- One main limitation for the size that your DB can have to be backed up will be the `memory_limit` and the `max_execution_time` of your PHP installation!
