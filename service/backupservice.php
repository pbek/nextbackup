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
    private $configService;
    private $logger;
    private $userId;

    // the minimal interval for backups [s]
    const MIN_BACKUP_INTERVAL = 3600;


    public function __construct($appName, IDb $db, ConfigService $configService, ILogger $logger, $userId){
        $this->appName = $appName;
        $this->db = $db;
        $this->configService = $configService;
        $this->logger = $logger;
        $this->userId = $userId;
    }

    /**
     * Returns the id of the user or the name of the app if no user is present (for example in a cronjob)
     *
     * @return string
     */
    public function getCallerName()
    {
        return is_null( $this->userId ) ? $this->appName : "user " . $this->userId;
    }

    /**
     * Returns all table names of the ownCloud database
     *
     * @return array
     */
    public function getTableNames()
    {
        $sql = "SHOW TABLES";
        $query = $this->db->prepareQuery($sql);
        $result = $query->execute();

        $resultList = array();
        while ($row = $result->fetchRow())
        {
            $rowValues = array_values( $row );
            $resultList[] = $rowValues[0];
        }

        return $resultList;
    }

    /**
     * Returns the create statement of a table
     *
     * @param $table
     * @return bool|string
     */
    public function getTableCreate( $table )
    {
        $sql = "SHOW CREATE TABLE $table";
        $query = $this->db->prepareQuery($sql);
        $result = $query->execute();

        if ( $row = $result->fetchRow() )
        {
            return $row["Create Table"] . ";";
        }

        return false;
    }

    /**
     * Returns the complete dump of a table
     *
     * @param $table
     * @return string
     */
    public function getTableDumpSql( $table )
    {
        $tableCreateSql = self::getTableCreate( $table );

        $sql = "SELECT * FROM $table";
        $query = $this->db->prepareQuery($sql);
        $result = $query->execute();

        $rowSqlList = array();
        while ( $row = $result->fetchRow() )
        {
            $valueList = array();
            foreach( $row as $value )
            {
                // TODO: make this compatible with binary file types <http://www.php.net/manual/en/pdostatement.getcolumnmeta.php>
                $valueList[] = $this->db->quote( $value );
            }

            $rowSql = "INSERT INTO $table VALUES(" . implode( ",", $valueList ) . ");";
            $rowSqlList[] = $rowSql;
        }

        $tableSql = "DROP TABLE $table;\n\n$tableCreateSql\n\n" . implode( "\n", $rowSqlList );

        return $tableSql;
    }

    /**
     * Runs a backup of all tables to sql files
     *
     * @throws Exception
     */
    public function createDBBackup()
    {
        // get the names of all tables
        $tables = self::getTableNames();
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

            foreach( $tables as $table )
            {
                $tableSql = self::getTableDumpSql( $table );

                $tableBackupFile = "$backupDir/$table.sql";
                // write db dump to sql file
                if ( !file_put_contents( $tableBackupFile, $tableSql ) )
                {
                    throw new Exception( "Cannot write to backup file: $tableBackupFile" );
                }
            }

            $this->logger->log( 10, $this->getCallerName() . " created a backup to '$backupDir'" );
        }
        catch ( Exception $e )
        {
            $this->logger->error( $this->getCallerName() . " thew an exception: " . $e->getMessage() );
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
            if ( $file == "." || $file == ".." )
            {
                continue;
            }

            $fullFileName = "$backupBaseDir/$file";

            // only add directories to the list
            if ( is_dir( $fullFileName ) && is_readable( $fullFileName ) )
            {
                $timestampList[] = $file;
            }
        }

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

        return $dateHash;
    }

    /**
     * Fetches all table names of the backup with a certain timestamp
     *
     * @param int $timestamp
     * @return array
     * @throws Exception
     */
    public function fetchTablesFromBackupTimestamp( $timestamp )
    {
        $backupDir = $this->configService->getBackupBaseDirectory() . "/$timestamp";
        $tableList = [];

        $fileList = scandir( $backupDir );
        foreach ( $fileList as $file )
        {
            // skip "." and ".."
            if ( $file == "." || $file == ".." )
            {
                continue;
            }

            $fullFileName = "$backupDir/$file";

            // only add files to the list
            if ( is_file( $fullFileName ) && is_readable( $fullFileName ) )
            {
                $tableList[] = str_replace( ".sql", "", $file );
            }
        }

        return $tableList;
    }

    /**
     * Restores a table for a timestamp
     *
     * @param int $timestamp
     * @param string $table
     * @throws Exception
     */
    public function doRestoreTable( $timestamp, $table )
    {
        $backupSqlFile = $this->configService->getBackupBaseDirectory() . "/$timestamp/$table.sql";

        if ( !is_file( $backupSqlFile ) || !is_readable( $backupSqlFile ) )
        {
            throw new Exception( "Cannot read backup sql file: $backupSqlFile" );
        }

        // get the content of the sql file and add a \n on the end for splitting
        $sqlDump = file_get_contents( $backupSqlFile ) . "\n";

        // get all statements from the sql file
        $sqlStatements = explode( ";\n", $sqlDump );

        $this->db->beginTransaction();

        // execute all sql statements
        foreach ( $sqlStatements as $sqlStatement )
        {
            $sqlStatement = trim( $sqlStatement );
            if ( $sqlStatement == "" )
            {
                continue;
            }

            // execute a sql statement
            $query = $this->db->prepareQuery( $sqlStatement );
            $query->execute();
        }

        $this->db->commit();
        $this->logger->log( 10, $this->getCallerName() . " restored table '$table' from backup $timestamp" );
    }

    /**
     * Restores a list of tables for a timestamp
     *
     * @param int $timestamp
     * @param array $tables
     * @throws Exception
     */
    public function doRestoreTables( $timestamp, $tables )
    {
        $this->db->beginTransaction();

        foreach ( $tables as $table )
        {
            // restore a table
            $this->doRestoreTable( $timestamp, $table );
        }

        $this->db->commit();
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
     * @return array
     */
    public function expireOldBackups()
    {
        // fetch all backup timestamps
        $backupTimestamps = $this->fetchBackupTimestamps();

        $keepBackupTimestamps = [];
        $time = time();

        // keep all backups of the last 24h
        foreach ( $backupTimestamps as $timestamp )
        {
            if ( $timestamp > ( $time - 86400 )  )
            {
                $keepBackupTimestamps[] = $timestamp;
            }
        }

        // TODO: implement other cases

        // keep at least one backup per day for one week

        // keep at least one backup per week for one month

        // keep at least one backup per month for one year

        // keep one backup per year for 10 years

        $removeBackupTimestamps = array_diff( $backupTimestamps, $keepBackupTimestamps );
        $removedBackups = [];

        // expire old backups
        foreach ( $removeBackupTimestamps as $timestamp )
        {
            // remove backup
            if ( $this->removeBackup( $timestamp ) )
            {
                $removedBackups[] = $timestamp;
            }
        }

        return $removedBackups;
    }

    /**
     * Removes a backup
     *
     * @param $timestamp
     * @return bool
     * @throws Exception
     */
    public function removeBackup( $timestamp )
    {
        $backupDir = $this->configService->getBackupBaseDirectory() . "/$timestamp";
        $success = false;

        if ( is_dir( $backupDir ) && is_writeable( $backupDir ) )
        {
            $success = rmdir( $backupDir );
        }

        if ( $success )
        {
            $this->logger->log( 10, $this->getCallerName() . " removed backup $timestamp" );
        }
        else
        {
            $this->logger->error( $this->getCallerName() . " could not remove backup directory '$backupDir'!" );
        }

        return $success;
    }
}
