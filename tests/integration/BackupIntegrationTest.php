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

        // test fetchFormattedBackupTimestampHash
        $tableList = $this->backupService->fetchFormattedBackupTimestampHash();
        $this->assertTrue( is_array( $tableList ) && ( count( $tableList ) == 1 ) );

        // check 0 timestamp
        $tableList = $this->backupService->fetchTablesFromBackupTimestamp( 0 );
        $this->assertFalse( $tableList );

        // check if we need a new backup now
        $result = $this->backupService->needNewBackup();
        $this->assertFalse( $result );
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

        // test if 0 timestamp is handled
        $result = $this->backupService->doRestoreTables( 0, $tableList );
        $this->assertFalse( $result );

        // test if 0 timestamp is handled
        $result = $this->backupService->doRestoreTable( 0, self::TEST_TABLE );
        $this->assertFalse( $result );
    }

    /**
     * Checks if an invalid backup can be restored
     *
     * @expectedException Exception
     */
    public function testInvalidRestore() {
        $tableList = [self::TEST_TABLE];
        $this->backupService->doRestoreTables( 1, $tableList );
    }

    /**
     * Checks if a backup can be removed
     *
     * @depends testBackup
     */
    public function testRemove() {

        $timestamp = $this->backupService->fetchLastBackupTimestamp();
        $this->assertTrue( is_int( $timestamp ), "no backup was found" );

        $result = $this->backupService->removeBackup( $timestamp );
        $this->assertTrue( $result, "backup could not be removed" );

        if ( $result )
        {
            $newTimestamp = $this->backupService->fetchLastBackupTimestamp();
            $this->assertNotEquals( $newTimestamp, $timestamp, "backup was still found" );
        }

        // test if 0 timestamp is handled
        $result = $this->backupService->removeBackup( 0 );
        $this->assertFalse( $result );
    }

    /**
     * Tests expiring of backups
     */
    public function testExpire() {

        $timestampList = $this->backupService->expireOldBackups();
        $this->assertTrue( is_array( $timestampList ) );
    }

    /**
     * Tests the auto expire list
     */
    public function testAutoExpireList()
    {
        // test with 5 hourly backups
        $timestampList = $this->getTestTimestampList( 5, 3600 );
        $expiryTimestampList = BackupServiceToTest::callProtectedGetAutoExpireList( $timestampList );
        $this->assertTrue( is_array( $expiryTimestampList ), "test if is array" );
        $this->assertEquals( 0, count( $expiryTimestampList ), "test with 5 hourly backups" );

        // test with 26 hourly backups
        $timestampList = $this->getTestTimestampList( 26, 3600 );
        $expiryTimestampList = BackupServiceToTest::callProtectedGetAutoExpireList( $timestampList );
        $this->assertEquals( 0, count( $expiryTimestampList ), "test with 26 hourly backups" );

        // test with 27 hourly backups
        $timestampList = $this->getTestTimestampList( 27, 3600 );
        $expiryTimestampList = BackupServiceToTest::callProtectedGetAutoExpireList( $timestampList );
        $this->assertEquals( 1, count( $expiryTimestampList ), "test with 27 hourly backups" );

        // test with 48 hourly backups
        $timestampList = $this->getTestTimestampList( 48, 3600 );
        $expiryTimestampList = BackupServiceToTest::callProtectedGetAutoExpireList( $timestampList );
        $this->assertEquals( 22, count( $expiryTimestampList ), "test with 48 hourly backups" );

        // test with 5000 hourly backups
        $timestampList = $this->getTestTimestampList( 5000, 3600 );
        $expiryTimestampList = BackupServiceToTest::callProtectedGetAutoExpireList( $timestampList );
        $this->assertEquals( 4960, count( $expiryTimestampList ), "test with 5000 hourly backups" );
    }

    /**
     * Returns a list of timestamps
     *
     * @param integer $amount
     * @param integer $interval
     * @return integer[]
     */
    private function getTestTimestampList( $amount, $interval )
    {
        $time = time();
        $timestampList = [];

        for ( $i = 0; $i < $amount; $i++ )
        {
            $timestampList[] = $time - ( $interval * $i );
        }

        return $timestampList;
    }
}

/**
 * Class BackupServiceToTest extends the original class to make it possible to test protected methods
 */
class BackupServiceToTest extends OCA\OwnBackup\Service\BackupService
{
    /**
     * @param integer[] $timestamps list of timestamps
     * @return integer[] containing the list of to be deleted timestamps
     */
    public static function callProtectedGetAutoExpireList(array $timestamps) {
        return self::getAutoExpireList($timestamps);
    }
}
