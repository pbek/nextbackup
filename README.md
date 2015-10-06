# OwnBackup

[![Build Status](https://travis-ci.org/pbek/ownbackup.svg?branch=master)](https://travis-ci.org/pbek/ownbackup)
[![Code Climate](https://codeclimate.com/github/pbek/ownbackup/badges/gpa.svg)](https://codeclimate.com/github/pbek/ownbackup)
[![Test Coverage](https://codeclimate.com/github/pbek/ownbackup/badges/coverage.svg)](https://codeclimate.com/github/pbek/ownbackup/coverage)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/04f33cd9-67b9-4a88-92d0-0c98944d1a8f/mini.png)](https://insight.sensiolabs.com/projects/04f33cd9-67b9-4a88-92d0-0c98944d1a8f)


**OwnBackup** is the **simple database backup solution** for your **[ownCloud](http://www.owncloud.com/)** 8.1+ installation.

It's main purpose is to provide an easy solution to restore the tables of an app in case you accidentally corrupted the data or did anything you regret later.  

Place this app in your `owncloud/apps/` directory.

## Screenshot
![Screenhot](screenshot.png)

## Disclaimer

- Use this app at your own risk! Data loss may occur!
- This app is no replacement for a more professional backup solution!

## Features

- OwnBackup creates backups of your ownCloud tables hourly via cronjob or manually.
- You are able to choose which tables from a certain backup you want to restore.
- This is OwnBackup's backup expiry scheme:
    - for 24h, keep one backup every hour
    - for 7d, keep one backup per day
    - for 4w, keep one backup per week
    - for 12m, keep one backup per 30d
    - next 2y, keep one backup per year
- If the Zlib library is present the backups get compressed with gzip.
- The backup should work with all databases, please report any problems. 

## Limitations

- I've only tested the app with *Cron* so far, please report any troubles with *Webcron* or *AJAX*.
- One main limitation for the size that your DB can have to be backed up will be the `memory_limit` and the `max_execution_time` of your PHP installation!
