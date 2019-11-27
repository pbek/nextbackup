<?php
/**
 * ownCloud - nextbackup
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Patrizio Bekerle <patrizio@bekerle.com>
 * @copyright Patrizio Bekerle 2015
 */

namespace OCA\NextBackup\Jobs;

use OC\BackgroundJob\TimedJob;
use OCA\NextBackup\AppInfo\Application;
use OCA\NextBackup\Service\BackupService;

class BackupJob extends TimedJob
{
    public function __construct()
    {
        $this->setInterval( 600 ); // check every 10 minutes if we need a new backup
    }

    /**
     * @param array $arguments
     * @throws \OCP\AppFramework\QueryException
     */
    public function run( $arguments )
    {
        if ( !\OC::$server->getAppManager()->isInstalled( 'nextbackup' ) )
        {
            return;
        }

        $app = new Application();
        $container = $app->getContainer();

        /** @var BackupService $backupService */
        $backupService = $container->query('OCA\NextBackup\Service\BackupService');

        // check if we need a new backup
        if ( $backupService->needNewBackup() )
        {
            // create new backup
            $backupService->createDBBackup();

            // expiring old backups
            $backupService->expireOldBackups();
        }
    }
}
