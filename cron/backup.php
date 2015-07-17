<?php

namespace OCA\OwnBackup\Cron;
use \OCA\OwnBackup\AppInfo\Application;

class Backup {
    public static function run()
    {
        $app = new Application();
        $container = $app->getContainer();
        $container->query('OCA\OwnBackup\Service\BackupService')->backupDB();
    }
}
