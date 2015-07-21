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
use OCA\OwnBackup\AppInfo\Application;
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
    const TEST_TABLE_NO_PREFIX = "jobs";
    const TEST_TABLE = "oc_jobs";


    public function setUp() {
        parent::setUp();
        $app = new Application();
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
        $this->assertTrue( in_array( self::TEST_TABLE, $tableList ) );

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
        $this->db->dropTable( self::TEST_TABLE_NO_PREFIX );

        // check if table is gone
        $tableExists = $this->db->tableExists( self::TEST_TABLE_NO_PREFIX );
        $this->assertFalse( $tableExists );

        // restore table
        $timestamp = $timestampList[0];
        $tableList = [self::TEST_TABLE];
        $this->backupService->doRestoreTables( $timestamp, $tableList );

        // check if table is present again
        $tableExists = $this->db->tableExists( self::TEST_TABLE_NO_PREFIX );
        $this->assertTrue( $tableExists );
    }

    /**
     * Checks if a backup can be removed
     *
     * @depends testBackup
     */
    public function testRemove() {

//        $this->backupService->removeBackup( $timestamp );

        $timestamp = $this->backupService->fetchLastBackupTimestamp();
        $this->assertTrue( is_int( $timestamp ), "no backup was found" );

        $result = $this->backupService->removeBackup( $timestamp );
        $this->assertTrue( $result, "backup could not be removed" );

        if ( $result )
        {
            $newTimestamp = $this->backupService->fetchLastBackupTimestamp();
            $this->assertNotEquals( $newTimestamp, $timestamp, "backup was still found" );
        }
    }
}
