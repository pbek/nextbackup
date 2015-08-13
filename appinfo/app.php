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

// I need this line so OwnBackup shows up in ownCloud 8.1.1, but in my ownCloud 8.1.1 development version from git
// it shows up two times because of it
new Application();

\OC::$server->getJobList()->add( new BackupJob() );
