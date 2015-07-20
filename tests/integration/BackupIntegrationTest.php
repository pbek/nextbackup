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

use OCA\OwnBackup\Service\BackupService;
use OCP\AppFramework\App;
use OCP\IDb;
use Test\TestCase;


/**
 * This test shows how to make a small Integration Test. Query your class
 * directly from the container, only pass in mocks if needed and run your tests
 * against the database
 */
class BackupIntegrationTest extends TestCase {

    private $container;

    /** @var BackupService $backupService */
    private $backupService;

    /** @var IDb $backupService */
    private $db;

    // the table we want to test
    const TEST_TABLE = "oc_jobs";


    public function setUp() {
        parent::setUp();
        $app = new App('ownbackup');
        $this->container = $app->getContainer();

        $this->backupService = $this->container->query('OCA\OwnBackup\Service\BackupService');
        $this->db = $this->container->query('OCP\IDb');
    }

    /**
     * Checks if a backup can be created
     *
     * @throws Exception
     */
    public function testBackup() {
        $timestamp = time();
        $this->backupService->createDBBackup();
        $timestampList = $this->backupService->fetchBackupTimestamps();

        // check if we got a backup
        $this->assertEquals( [$timestamp], $timestampList );

        // check if there are tables in the backup
        $tableList = $this->backupService->fetchTablesFromBackupTimestamp( $timestamp );
        $this->assertGreaterThan( 0, count( $tableList ) );

        // check if there is a table "oc_jobs";
        $this->assertEquals( true, in_array( self::TEST_TABLE, $tableList ) );

    }

    /**
     * Checks if a backup can be restored
     *
     * @depends testBackup
     */
    public function testRestore() {
        $timestampList = $this->backupService->fetchBackupTimestamps();
        $this->assertEquals( 1, count( $timestampList ) );

        // drop table
        $sql = "DROP TABLE " . self::TEST_TABLE;
        $query = $this->db->prepareQuery($sql);
        $query->execute();

        // check if table is gone
        $tableExists = $this->checkIfTableExists( self::TEST_TABLE );
        $this->assertEquals( false, $tableExists );

        // restore table
        $timestamp = $timestampList[0];
        $tableList = [self::TEST_TABLE];
        $this->backupService->doRestoreTables( $timestamp, $tableList );

        // check if table is present again
        $tableExists = $this->checkIfTableExists( self::TEST_TABLE );
        $this->assertEquals( true, $tableExists );
    }

    /**
     * Checks if a table exists
     *
     * @param $table
     * @return bool
     */
    private function checkIfTableExists( $table )
    {
        $sql = "SHOW TABLES LIKE '$table'";
        $query = $this->db->prepareQuery($sql);
        $result = $query->execute();
        return $result->fetchOne() == $table;
    }
}