<?php
/**
 * ownCloud - ownbackup
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Patrizio Bekerle <patrizio@bekerle.com>
 * @copyright Patrizio Bekerle 2015
 */

namespace OCA\OwnBackup\Jobs;

use OC\BackgroundJob\TimedJob;
use OCA\OwnBackup\AppInfo\Application;
use OCA\OwnBackup\Service\BackupService;
use OCP\App;

class BackupJob extends TimedJob
{
    public function __construct()
    {
        $this->setInterval( 600 ); // check every 10 minutes if we need a new backup
    }

    /**
     * @param array $arguments
     */
    public function run( $arguments )
    {
        if ( !App::isEnabled( 'ownbackup' ) )
        {
            return;
        }

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
