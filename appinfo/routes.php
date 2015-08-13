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

$application = new \OCA\OwnBackup\AppInfo\Application();

/** @var $this \OC\Route\Router */
$application->registerRoutes($this, [
    'routes' => [
	   ['name' => 'admin#index', 'url' => '/', 'verb' => 'GET'],
	   ['name' => 'admin#do_restore_tables', 'url' => '/restore-tables', 'verb' => 'POST'],
	   ['name' => 'admin#do_fetch_tables', 'url' => '/fetch-tables', 'verb' => 'POST'],
	   ['name' => 'admin#do_create_backup', 'url' => '/create-backup', 'verb' => 'POST'],
    ]
]);
