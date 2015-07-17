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
namespace OCA\OwnBackup\Service;

use Exception;
use \OCP\BackgroundJob;
use \OCP\IConfig;

class ConfigService {

    /**
     *
     * @var \OCP\IConfig
     */
    protected $owncloudConfig;

    /**
     *
     * @var string
     */
    protected $appName;

    public function __construct($appName, IConfig $owncloudConfig) {
        $this->appName = $appName;
        $this->owncloudConfig = $owncloudConfig;
    }

    /**
     *
     * @return string
     */
    public function getLogfileName() {
        return $this->getDataDir() . '/backup.log';
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getBackupBaseDirectory() {
        $backupDir = $this->getDataDir() . '/ownbackup';

        // create new base backup folder if it not exists
        if ( !file_exists( $backupDir ) )
        {
            if ( !mkdir( $backupDir ) )
            {
                throw new Exception( "Cannot create base backup dir: $backupDir" );
            }
        }

        return $backupDir;
    }

    /**
     *
     * @param string $key
     * @param string[optional] $default
     * @return string
     */
    public function getAppValue($key, $default = null) {
        return $this->owncloudConfig->getAppValue($this->appName, $key, $default);
    }

    /**
     *
     * @param string $key
     * @param string $value
     */
    public function setAppValue($key, $value) {
        $this->owncloudConfig->setAppValue($this->appName, $key, $value);
    }

    /**
     *
     * @return string
     */
    public function getDataDir() {
        return \OC::$server->getConfig()->getSystemValue("datadirectory", \OC::$SERVERROOT . '/data');
    }
}
