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

namespace OCA\OwnBackup\AppInfo;

use OCP\AppFramework\App;
use OCA\OwnBackup\Controller\AdminController;

class Application extends App
{
    public function __construct(array $urlParams = [])
    {
        parent::__construct('ownbackup', $urlParams);

        $container = $this->getContainer();
        $container->registerService('AdminController', function($c) {
            return new AdminController(
                $c->query('AppName'),
                $c->query('Request'),
                $c->query('BackupService')
            );
        });
    }

    public function registerSettings() {
        // Register settings scripts
        // TODO: implement with new admin system?
        \OCP\App::registerAdmin('ownbackup', 'admin');
    }

}
