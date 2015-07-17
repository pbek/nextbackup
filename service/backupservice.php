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

class BackupService {

    private $db;
    private $configService;

    public function __construct(IDb $db, ConfigService $configService){
        $this->db = $db;
        $this->configService = $configService;
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
                // TODO: make this binary compatible
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
    public function backupDB()
    {
        // get the names of all tables
        $tables = self::getTableNames();
        $timestamp = time();

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

        $fileList = scandir( $backupBaseDir );
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
}
