# [OwnBackup](https://apps.owncloud.com/content/show.php?content=170674 "OwnBackup ownCloud App page") 

[Changelog](https://github.com/pbek/ownbackup/blob/develop/CHANGELOG.md) | 
[Issues](https://github.com/pbek/ownbackup/issues) | 
[Download](https://apps.owncloud.com/content/show.php?content=170674) |
[ownCloud Apps page](https://apps.owncloud.com/content/show.php?content=170674)

[![Build Status](https://travis-ci.org/pbek/ownbackup.svg?branch=master)](https://travis-ci.org/pbek/ownbackup)
[![Code Climate](https://codeclimate.com/github/pbek/ownbackup/badges/gpa.svg)](https://codeclimate.com/github/pbek/ownbackup)
[![Test Coverage](https://codeclimate.com/github/pbek/ownbackup/badges/coverage.svg)](https://codeclimate.com/github/pbek/ownbackup/coverage)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/04f33cd9-67b9-4a88-92d0-0c98944d1a8f/mini.png)](https://insight.sensiolabs.com/projects/04f33cd9-67b9-4a88-92d0-0c98944d1a8f)


**OwnBackup** is the **simple database backup solution** for your **[ownCloud](http://www.owncloud.com/)** 8.1+ installation.

It's main purpose is to provide an easy solution to restore the tables of an app in case you accidentally corrupted the data or did anything you regret later.  

[Download](https://apps.owncloud.com/content/show.php/QOwnNotesAPI?content=170674) the app package into your ownCloud app folder (`owncloud/apps/`) and unpack it there with:

```bash
tar xvfz 170674-ownbackup.tar.gz
```

## Screenshot
![Screenhot](screenshot.png)

## Disclaimer

- Use this app at your own risk! Data loss may occur!
- This app is no replacement for a more professional backup solution!

## Features

- OwnBackup creates backups of your ownCloud tables hourly via cronjob or manually.
- You are able to choose which tables from a certain backup you want to restore.
- OwnBackup expires your backups automatically.
    - for 24h one backup every hour is kept
    - for 7d one backup per day is kept
    - for 4w one backup per week is kept
    - for 12m one backup per 30d is kept
    - for 2y one backup per year is kept
- If the Zlib library is present the backups get compressed with gzip.
- The backup should work with all databases, please report any problems. 

## Limitations

- I've only tested the app with *Cron* so far, please report any troubles with *Webcron* or *AJAX*.
- One main limitation for the size that your DB can have to be backed up will be the `memory_limit` and the `max_execution_time` of your PHP installation!
