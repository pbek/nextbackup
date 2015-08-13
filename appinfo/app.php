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

use OCA\OwnBackup\Jobs\BackupJob;

\OC::$server->getJobList()->add( new BackupJob() );
