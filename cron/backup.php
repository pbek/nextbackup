<?php

namespace OCA\OwnBackup\Cron;
use \OCA\OwnBackup\AppInfo\Application;
use OCA\OwnBackup\Service\BackupService;

class Backup {
    public static function run()
    {
        $app = new Application();
        $container = $app->getContainer();

        /** @var BackupService $backupService */
        $backupService = $container->query('OCA\OwnBackup\Service\BackupService');

        // check if we need a new backup
        if ( $backupService->needNewBackup() )
        {
            // create new backup
            $backupService->createDBBackup();
        }

        // expiring old backups
        $backupService->expireOldBackups();
    }
}
