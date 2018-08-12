# OwnBackup Change Log

## 18.8.1
- more changes for Nextcloud 14

## 18.8.0
- enabled and tested app for Nextcloud 14

## 17.7.0
- changes to get the app into the ownCloud Marketplace

## 17.5.0
- migrated from IDB to IDBConnection for Nextcloud 12
  (for [Issue #17](https://github.com/pbek/ownbackup/issues/17))
- enabled and tested app for ownCloud 10 and Nextcloud 12

## 17.3.0
- integrity check fixes

## 16.11.0
- fixed an `internal server error` on ownCloud 9.1.2
  (for [Issue #7](https://github.com/pbek/ownbackup/issues/7))
- added a notice to setup a cronjob to get periodic backups
- switched release versioning to a more *rolling release style*,
  so it doesn't get confused with semantic versioning
    - `<year of release>.<month of release>.<release number in the month>` 

## v0.3.8
- made the database restore functionality work again on ownCloud 9.0

## v0.3.7
- more improvements for the automatic backup expiration

## v0.3.6
- small change in admin.php after a user reported problems with it

## v0.3.5
- some improvements and fixes for the automatic backup expiration

## v0.3.4
- the auto expiration of backups was rewritten again, because the old algorithm didn't work for hourly backups 
    - for 24h, keep one backup every hour
    - for 7d, keep one backup per day
    - for 4w, keep one backup per week
    - for 12m, keep one backup per 30d
    - next 2y, keep one backup per year

## v0.3.3
- fixed sort order of backups in selector on admin page after creating a manual backup
- new backup expiry scheme 
    - for the next 24h, one backup every hour will be kept (like in the old behaviour)
    - for the next 7d, one backup per day will be kept
    - for the next 30d, one backup per week will be kept
    - for the next 1y, one backup per 30d will be kept
    - for the next 10y, one backup per year will be kept
    - until end of time, one backup per 100y will be kept
- fixed some logging issues

## v0.3.2
- fixed the problem that OwnBackup might be shown twice in the admin menu
- fixed all compliance issues

## v0.3.1
- tried to fix a problem with the OwnBackup section not showing up on the admin page

## v0.3
- re-implemented the backup and restore page in the admin section

## v0.2.2.1
- fixed a problem with the build

## v0.2.2
- now showing a special page for non-admin users
- using maintenance mode during restore
- some user interaction and security improvements

## v0.2.1
- Fixed removing of old backups 

## v0.2
- The backup and restore process was completely rewritten, the new backup sets are incompatible with version 0.1 backup sets! 
- The app should work with all databases and data types now, please report any problems. 

## v0.1
- First release
