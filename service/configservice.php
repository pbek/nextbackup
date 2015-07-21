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

    /**
     * Looks up a system wide defined value
     *
     * @param string $key the key of the value, under which it was saved
     * @param mixed $default the default value to be returned if the value isn't set
     * @return mixed the value or $default
     */
    public function getSystemValue( $key, $default = '' ) {
        return $this->owncloudConfig->getSystemValue( $key, $default );
    }

    /**
     * Sets a new system wide value
     *
     * @param string $key the key of the value, under which will be saved
     * @param mixed $value the value that should be stored
     */
    public function setSystemValue($key, $value) {
        return $this->owncloudConfig->setSystemValue( $key, $value );
    }
}
