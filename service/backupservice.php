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
    public function restoreTable( $timestamp, $table )
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
    }

    /**
     * Restores a list of tables for a timestamp
     *
     * @param int $timestamp
     * @param array $tables
     * @throws Exception
     */
    public function restoreTables( $timestamp, $tables )
    {
        $this->db->beginTransaction();

        foreach ( $tables as $table )
        {
            // restore a table
            $this->restoreTable( $timestamp, $table );
        }

        $this->db->commit();
    }
}
