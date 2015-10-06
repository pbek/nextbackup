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
use OCP\IDb;
use OCA\OwnBackup\Service;
use OCP\ILogger;

class BackupService {

    private $appName;
    private $db;
    private $odb;
    private $configService;
    private $logger;
    private $logContext;
    private $userId;

    private static $maxBackupTimestampsPerInterval = array(
        // next 24h, one backup every hour
        array('intervalEndsAfter' => 86400,     'step' => 3600),
        // next 7d, one backup per day
        array('intervalEndsAfter' => 604800,    'step' => 86400),
        // next 30d, one backup per week
        array('intervalEndsAfter' => 2592000,   'step' => 604800),
        // next 1y, one backup per 30d
        array('intervalEndsAfter' => 31536000,  'step' => 2592000),
        // next 10y, one backup per year
        array('intervalEndsAfter' => 315360000, 'step' => 31536000),
        // until end of time, one backup per 100y
        array('intervalEndsAfter' => -1,        'step' => 3153600000),
    );


    // the minimal interval for backups [s]
    const MIN_BACKUP_INTERVAL = 3600;


    public function __construct($appName, IDb $db, \OC_DB $odb, ConfigService $configService, ILogger $logger, $userId){
        $this->appName = $appName;
        $this->db = $db;
        $this->odb = $odb;
        $this->configService = $configService;
        $this->logger = $logger;
        $this->logContext = ['app' => 'ownbackup'];
        $this->userId = $userId;
    }

    /**
     * Returns the id of the user or the name of the app if no user is present (for example in a cronjob)
     *
     * @return string
     */
    private function getCallerName()
    {
        return is_null( $this->userId ) ? $this->appName : "user " . $this->userId;
    }

    /**
     * Returns the complete serialized dump of a table without field names
     *
     * @param string $table
     * @return array
     */
    private function getTableSerializedDataDump( $table )
    {
        $sql = "SELECT * FROM `$table`";
        $query = $this->db->prepareQuery( $sql );
        $result = $query->execute();

        return serialize( array_map( function( $array ) {
            // we want no field names, just the values, to safe space
            return array_values( $array );
        }, $result->fetchAll() ) );
    }

    /**
     * Runs a backup of all tables to sql files
     *
     * @throws Exception
     * @return int timestamp of backup
     */
    public function createDBBackup()
    {
        $timestamp = time();

        try
        {
            $backupDir = $this->configService->getBackupBaseDirectory(). "/$timestamp";

            // create new backup folder if it not exists
            if ( !file_exists( $backupDir ) )
            {
                if ( !mkdir( $backupDir ) )
                {
                    throw new Exception( "Cannot create backup dir: $backupDir" );
                }
            }

            $structureFile = "$backupDir/structure.xml";

            // get the db structure
            if ( !$this->odb->getDbStructure( $structureFile ) )
            {
                throw new Exception( "Cannot create db structure in file: $structureFile" );
            }

            // create a xml object from db structure
            $loadEntities = libxml_disable_entity_loader(false);
            $xml = simplexml_load_file( $structureFile );
            libxml_disable_entity_loader($loadEntities);

            $charset = (string) $xml->charset;

            /** @var \SimpleXMLElement $child */
            foreach ($xml->children() as $child)
            {
                // skip everything but tables
                if ( $child->getName() !== "table" )
                {
                    continue;
                }

                // find the table name
                $tableName = (string) $child->name;

                if ( $tableName === "" )
                {
                    throw new Exception( "No table name was set!" );
                }

                // build a structure xml for a single table
                $xmlDump = "<database><name>*dbname*</name><create>true</create><overwrite>false</overwrite><charset>$charset</charset>" . $child->asXML() . "</database>";

                // get name of table directory
                $tableDir = "$backupDir/$tableName";

                // create table directory if it does not exist
                if ( !file_exists( $tableDir ) )
                {
                    if ( !mkdir( $tableDir ) )
                    {
                        throw new Exception( "Cannot create table dir: $tableDir" );
                    }
                }

                // write structure to table structure file
                $tableStructureFile = "$tableDir/structure.xml";
                file_put_contents( $tableStructureFile, $xmlDump );

                // get a serialized dump of the table
                $tableDump = $this->getTableSerializedDataDump( $tableName );

                // write dump to table data file (compressed if possible)
                $tableDataFile = "$tableDir/data.dump";
                file_put_contents( $tableDataFile, $this->tryToCompressString( $tableDump ) );
            }

            $this->logger->notice( $this->getCallerName() . " created a backup to '$backupDir'", $this->logContext );

            return $timestamp;
        }
        catch ( Exception $e )
        {
            $this->logger->error( $this->getCallerName() . " thew an exception: " . $e->getMessage(), $this->logContext );
            throw( $e );
        }
    }

    /**
     * Fetches all backup timestamp
     *
     * @return array
     * @throws Exception
     */
    public function fetchBackupTimestamps()
    {
        $backupBaseDir = $this->configService->getBackupBaseDirectory();
        $timestampList = array();

        $fileList = scandir( $backupBaseDir, 1 );
        foreach ( $fileList as $file )
        {
            // skip "." and ".."
            if ( $file === "." || $file === ".." )
            {
                continue;
            }

            $fullFileName = "$backupBaseDir/$file";

            // only add directories to the list
            if ( is_dir( $fullFileName ) && is_readable( $fullFileName ) )
            {
                $timestampList[] = (int) $file;
            }
        }

        rsort( $timestampList, SORT_NUMERIC );
        return $timestampList;
    }

    /**
     * Fetches all backup timestamps as hash with formatted date strings
     *
     * @return array
     */
    public function fetchFormattedBackupTimestampHash()
    {
        $timestampList = $this->fetchBackupTimestamps();

        $dateTimeFormatter = \OC::$server->query('DateTimeFormatter');
        $dateHash = [];
        foreach( $timestampList as $timestamp )
        {
            $dateHash[$timestamp] = $dateTimeFormatter->formatDateTime( $timestamp );
        }

        krsort( $dateHash, SORT_NUMERIC );
        return $dateHash;
    }

    /**
     * Fetches all table names of the backup with a certain timestamp
     *
     * @param int $timestamp
     * @return array|false
     * @throws Exception
     */
    public function fetchTablesFromBackupTimestamp( $timestamp )
    {
        $timestamp = (int) $timestamp;

        if ( $timestamp === 0 )
        {
            return false;
        }

        $backupDir = $this->configService->getBackupBaseDirectory() . "/$timestamp";
        $tableList = [];

        $fileList = scandir( $backupDir );
        foreach ( $fileList as $file )
        {
            // skip "." and ".."
            if ( $file === "." || $file === ".." )
            {
                continue;
            }

            $fullFileName = "$backupDir/$file";

            // only add directories to the list
            if ( is_dir( $fullFileName ) && is_readable( $fullFileName ) )
            {
                $tableList[] = $file;
            }
        }

        return $tableList;
    }

    /**
     * Restores a table for a timestamp
     *
     * @param int $timestamp
     * @param string $table
     * @return bool
     * @throws Exception
     */
    public function doRestoreTable( $timestamp, $table )
    {
        $timestamp = (int) $timestamp;

        if ( $timestamp === 0 )
        {
            return false;
        }

        // get the table structure file name
        $structureFile = $this->configService->getBackupBaseDirectory() . "/$timestamp/$table/structure.xml";

        $this->db->beginTransaction();

        if ( !is_file( $structureFile ) || !is_readable( $structureFile ) )
        {
            throw new Exception( "Cannot read table structure file: $structureFile" );
        }

        // drops the table
        $this->dropTable( $table );

        // update the table structure
        if ( !$this->odb->updateDbFromStructure( $structureFile ) )
        {
            throw new Exception( "Cannot restore table structure from file: $structureFile" );
        }

        // get the data dump file name
        $dataDumpFile = $this->configService->getBackupBaseDirectory() . "/$timestamp/$table/data.dump";

        if ( !is_file( $dataDumpFile ) || !is_readable( $dataDumpFile ) )
        {
            throw new Exception( "Cannot read table data dump file: $dataDumpFile" );
        }

        // try to get the data dump
        $dataDump = unserialize( $this->tryToUncompressString( file_get_contents( $dataDumpFile ) ) );
        if ( !is_array( $dataDump ) )
        {
            throw new Exception( "Data dump is no array in file: $dataDumpFile" );
        }

        // generate the field name list from the table structure file
        $fieldList = $this->getFieldListFromTableStructureFile( $structureFile );

        // insert all the data
        $connection = $this->odb->getConnection();

        foreach( $dataDump as $dataLine )
        {
            $dataHash = [];

            // generate the data hash
            foreach ( $fieldList as $key => $fieldName )
            {
                // we want to add the field names again, that we left out to save space
                $dataHash[$fieldName] = $dataLine[$key];
            }

            // insert the data into table
            $connection->insertIfNotExist( $table, $dataHash );
        }
        $this->db->commit();

        $this->logger->notice( $this->getCallerName() . " restored table '$table' from backup $timestamp", $this->logContext );

        return true;
    }

    /**
     * Generates a field name list from a table structure file
     *
     * @param string $tableStructureFile
     * @return array
     */
    private function getFieldListFromTableStructureFile( $tableStructureFile )
    {
        // create a xml object from table structure
        $loadEntities = libxml_disable_entity_loader(false);
        $xml = simplexml_load_file( $tableStructureFile );
        libxml_disable_entity_loader($loadEntities);

        $fieldList = [];

        /** @var \SimpleXMLElement $tableDeclaration */
        $tableDeclaration = $xml->table->declaration;

        /** @var \SimpleXMLElement $child */
        foreach( $tableDeclaration->children() as $child )
        {
            // skip everything but fields
            if ( $child->getName() !== "field" )
            {
                continue;
            }

            $fieldName = (string) $child->name;
            $fieldList[] = $fieldName;
        }

        return $fieldList;
    }

    /**
     * Drops a table
     *
     * @param $table
     * @return mixed
     * @throws Exception
     */
    private function dropTable( $table )
    {
        // remove the prefix from the table name
        $filterExpression = '/^' . preg_quote( $this->configService->getSystemValue( 'dbtableprefix', 'oc_' ) ) . '/';
        $tableNoPrefix = preg_replace( $filterExpression, "", $table );

        if ( $tableNoPrefix === "" )
        {
            throw new Exception( "Cannot remove prefix from table name: $table" );
        }

        return $this->db->dropTable( $tableNoPrefix );
    }

    /**
     * Restores a list of tables for a timestamp
     *
     * @param int $timestamp
     * @param array $tables
     * @return bool
     * @throws Exception
     */
    public function doRestoreTables( $timestamp, array $tables )
    {
        $timestamp = (int) $timestamp;

        if ( $timestamp === 0 )
        {
            return false;
        }

        try
        {
            // enabled maintenance mode
            $this->configService->setSystemValue('maintenance', true);

            $this->db->beginTransaction();

            foreach ( $tables as $table )
            {
                // restore a table
                $this->doRestoreTable( $timestamp, $table );
            }

            $this->db->commit();
        }
        catch( \Exception $e )
        {
            // do a rollback
            $this->db->rollBack();

            // disable maintenance mode
            $this->configService->setSystemValue('maintenance', false);

            throw $e;
        }

        // disable maintenance mode
        $this->configService->setSystemValue('maintenance', false);

        return true;
    }

    /**
     * Fetches the timestamp of the last backup
     *
     * @return int|bool
     */
    public function fetchLastBackupTimestamp()
    {
        // fetch all backup timestamps
        $backupTimestamps = $this->fetchBackupTimestamps();

        // sort them descending
        rsort( $backupTimestamps );

        return isset( $backupTimestamps[0] ) ? (int) $backupTimestamps[0] : false;
    }

    /**
     * Checks if we need a new backup
     *
     * @return bool
     */
    public function needNewBackup()
    {
        return ( (int) $this->fetchLastBackupTimestamp() ) < ( time() - self::MIN_BACKUP_INTERVAL );
    }

    /**
     * Expires all old backups
     *
     * @return array timestamp list of removed backups
     */
    public function expireOldBackups()
    {
        // fetch all backup timestamps
        $backupTimestamps = $this->fetchBackupTimestamps();

        $time = time();
        // get the list of backup timestamps we want to expire
        $removeTimestampList = self::getAutoExpireList( $time, $backupTimestamps );
        $removedTimestampList = [];

        // expire old backups
        foreach ( $removeTimestampList as $timestamp )
        {
            // remove backup
            if ( $this->removeBackup( $timestamp ) )
            {
                $removedTimestampList[] = $timestamp;
            }
        }

        return $removedTimestampList;
    }

    /**
     * Returns a list of backup timestamps we want to expire
     * Code was borrowed from Storage::getAutoExpireList() and modified
     * @see OCA\Files_Versions\Storage::getAutoExpireList()
     *
     * @param integer $time
     * @param integer[] $timestamps list of timestamps
     * @return integer[] containing the list of to be deleted timestamps
     */
    protected static function getAutoExpireList( $time, array $timestamps )
    {
        // descending order seems crucial here
        rsort( $timestamps, SORT_NUMERIC );

        // timestamps we want to delete
        $toDelete = array();

        $interval = 0;
        $step = self::$maxBackupTimestampsPerInterval[$interval]['step'];
        if (self::$maxBackupTimestampsPerInterval[$interval]['intervalEndsAfter'] === -1) {
            $nextInterval = -1;
        } else {
            $nextInterval = $time - self::$maxBackupTimestampsPerInterval[$interval]['intervalEndsAfter'];
        }

        $firstTimestamp = reset($timestamps);
        $firstKey = key($timestamps);
        $prevTimestamp = $firstTimestamp;
        $nextTimestamp = $firstTimestamp - $step;
        unset($timestamps[$firstKey]);

        foreach ($timestamps as $timestamp)
        {
            $newInterval = true;
            while ($newInterval) {
                if ($nextInterval === -1 || $prevTimestamp > $nextInterval)
                {
                    if ($timestamp > $nextTimestamp) {
                        // distance between two timestamps too small, mark to delete
                        $toDelete[] = $timestamp;
                        \OCP\Util::writeLog('ownbackup', 'Mark to expire backup '. $timestamp .', next timestamp should be ' . $nextTimestamp . " or smaller. (prevTimestamp: " . $prevTimestamp . "; step: " . $step, \OCP\Util::DEBUG);
                    }
                    else
                    {
                        $nextTimestamp = $timestamp - $step;
                        $prevTimestamp = $timestamp;
                    }
                    // timestamp checked so we can move to the next one
                    $newInterval = false;
                }
                // time to move on to the next interval
                else
                {
                    $interval++;
                    $step = self::$maxBackupTimestampsPerInterval[$interval]['step'];
                    $nextTimestamp = $prevTimestamp - $step;
                    if (self::$maxBackupTimestampsPerInterval[$interval]['intervalEndsAfter'] === -1)
                    {
                        $nextInterval = -1;
                    } else {
                        $nextInterval = $time - self::$maxBackupTimestampsPerInterval[$interval]['intervalEndsAfter'];
                    }
                    $newInterval = true; // we changed the interval -> check same timestamp with new interval
                }
            }
        }

        return $toDelete;
    }


    /**
     * Removes a backup
     *
     * @param int $timestamp
     * @return bool
     * @throws Exception
     */
    public function removeBackup( $timestamp )
    {
        $timestamp = (int) $timestamp;

        if ( $timestamp === 0 )
        {
            return false;
        }

        $backupDir = $this->configService->getBackupBaseDirectory() . "/$timestamp";
        $success = false;

        if ( is_dir( $backupDir ) && is_writeable( $backupDir ) )
        {
            $success = $this->recursivelyRemoveDir( $backupDir );
        }

        if ( $success )
        {
            $this->logger->notice( $this->getCallerName() . " removed backup $timestamp", $this->logContext );
        }
        else
        {
            $this->logger->error( $this->getCallerName() . " could not remove backup directory '$backupDir'!", $this->logContext );
        }

        return $success;
    }

    /**
     * Attempts to compress a string
     *
     * @param $text
     * @return string
     */
    private function tryToCompressString( $text )
    {
        if ( function_exists( "gzencode" ) )
        {
            $compressedText = gzencode( $text );
            if ( $compressedText !== false )
            {
                return $compressedText;
            }
        }

        return $text;
    }

    /**
     * Attempts to uncompress a string
     *
     * @param $compressedText
     * @return string
     */
    private function tryToUncompressString( $compressedText )
    {
        if ( function_exists( "gzdecode" ) )
        {
            $text = gzdecode( $compressedText );
            if ( $text !== false )
            {
                return $text;
            }
        }

        return $compressedText;
    }

    /**
     * Recursively removes a directory
     *
     * @param string $dir
     * @return bool
     */
    private function recursivelyRemoveDir( $dir )
    {
        $success = false;
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object !== "." && $object !== "..") {
                    if (filetype($dir."/".$object) === "dir") $this->recursivelyRemoveDir($dir."/".$object); else unlink($dir."/".$object);
                }
            }
            reset($objects);
            $success = rmdir($dir);
        }

        return $success;
    }
}
