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

/**
 * Create your routes in here. The name is the lowercase name of the controller
 * without the controller part, the stuff after the hash is the method.
 * e.g. page#index -> OCA\OwnBackup\Controller\PageController->index()
 *
 * The controller class has to be registered in the application.php file since
 * it's instantiated in there
 */
return [
    'routes' => [
	   ['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
	   ['name' => 'page#do_restore_tables', 'url' => '/restore-tables', 'verb' => 'POST'],
	   ['name' => 'page#do_fetch_tables', 'url' => '/fetch-tables', 'verb' => 'POST'],
	   ['name' => 'page#do_create_backup', 'url' => '/create-backup', 'verb' => 'POST'],
    ]
];