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

namespace OCA\NextBackup;

use OCA\NextBackup\AppInfo\Application;

$app = new Application();
$container = $app->getContainer();
$response = $container->query('\OCA\NextBackup\Controller\AdminController')->index();
return $response->render();
