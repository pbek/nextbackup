# NextBackup

[Changelog](https://github.com/pbek/nextbackup/blob/develop/CHANGELOG.md) | 
[Issues](https://github.com/pbek/nextbackup/issues) | 
[Download](https://github.com/pbek/nextbackup/releases) |
[ownCloud Marketplace page](https://marketplace.owncloud.com/apps/nextbackup) |
[Nextcloud Apps page](https://apps.nextcloud.com/apps/nextbackup)

[![Build Status](https://travis-ci.org/pbek/nextbackup.svg?branch=develop)](https://travis-ci.org/pbek/nextbackup)
[![Code Climate](https://codeclimate.com/github/pbek/nextbackup/badges/gpa.svg)](https://codeclimate.com/github/pbek/nextbackup)
[![Test Coverage](https://codeclimate.com/github/pbek/nextbackup/badges/coverage.svg)](https://codeclimate.com/github/pbek/nextbackup/coverage)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/04f33cd9-67b9-4a88-92d0-0c98944d1a8f/mini.png)](https://insight.sensiolabs.com/projects/04f33cd9-67b9-4a88-92d0-0c98944d1a8f)


**NextBackup** (formerly OwnBackup) is the **simple database backup solution** for your **[ownCloud](http://www.owncloud.org/)** 8.1+
and **[Nextcloud](http://www.nextcloud.com/)** installation.

Its main purpose is to provide an easy solution to restore the tables of an app in case you accidentally corrupted the data or did anything you regret later.  

**Beware**: This application depends on private (now partly deprecated) ownCloud / Nextcloud API and only works as good as this API!

**NextBackup is not compatible with Nextcloud 21+!**
See [#50](https://github.com/pbek/nextbackup/issues/50) for more information.

## Installation

Clone the app into your Nextcloud / ownCloud apps directory:

```bash
git clone https://github.com/pbek/nextbackup.git apps/nextbackup -b master
```

Activate the app:

```bash
occ app:enable nextbackup
```

The app will **automatically start running** from this point onward and will create a backup (approximately) every hour. **No additional configuration** is needed for that.

The only requirement is properly working background jobs, which are needed for a correctly working installation anyway. You'll find instructions on how to do this in the administration manuals ([Nextcloud](https://docs.nextcloud.com/server/latest/admin_manual/configuration_server/background_jobs_configuration.html?highlight=cron), [ownCloud](https://doc.owncloud.com/server/admin_manual/configuration/server/background_jobs_configuration.html)).

You'll find options to restore from previous backups and also to trigger the creation of new backups in the administration settings. See below screenshots for details.

## Screenshot
![Screenhot](screenshot.png)

## Features

- NextBackup creates backups of your Nextcloud / ownCloud tables hourly via your Nextcloud / ownCloud cronjob or manually.
- You are able to choose which tables from a certain backup you want to restore.
- NextBackup expires your backups automatically.
    - for 24h one backup every hour is kept
    - for 7d one backup per day is kept
    - for 4w one backup per week is kept
    - for 12m one backup per 30d is kept
    - for 2y one backup per year is kept
- If the Zlib library is present the backups get compressed with gzip.
- The backup should work with all databases, please report any problems. 

## Limitations

- I've only tested the app with [Cron](https://docs.nextcloud.com/server/stable/admin_manual/configuration_server/background_jobs_configuration.html#cron) so far, please report any troubles with *Webcron* or *AJAX*.
- One main limitation for the size that your DB can have to be backed up will be the `memory_limit` and the `max_execution_time` of your PHP installation!

## Disclaimer

- Use this app at your own risk! Data loss may occur!
- This app is no replacement for a more professional backup solution!

This SOFTWARE PRODUCT is provided by THE PROVIDER "as is" and "with all faults." THE PROVIDER makes no representations or warranties of any kind concerning the safety, suitability, lack of viruses, inaccuracies, typographical errors, or other harmful components of this SOFTWARE PRODUCT. 

There are inherent dangers in the use of any software, and you are solely responsible for determining whether this SOFTWARE PRODUCT is compatible with your equipment and other software installed on your equipment. You are also solely responsible for the protection of your equipment and backup of your data, and THE PROVIDER will not be liable for any damages you may suffer in connection with using, modifying, or distributing this SOFTWARE PRODUCT.
