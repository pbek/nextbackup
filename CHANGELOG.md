# OwnBackup Change Log

## v0.3.3
- fixed sort order of backups in selector on admin page after creating a manual backup
- new backup expiry scheme 
    - next 24h, one backup every hour (like in the old behaviour)
    - next 7d, one backup per day
    - next 30d, one backup per week
    - next 1y, one backup per 30d
    - next 10y, one backup per year
    - until end of time, one backup per 100y
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
