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

namespace OCA\OwnBackup\Controller;

use OCA\OwnBackup\Service\BackupService;
use OCP\IConfig;
use OCP\IRequest;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Controller;

class PageController extends Controller {

	private $userId;
	private $backupService;

	public function __construct($AppName, IRequest $request, BackupService $backupService, $UserId){
		parent::__construct($AppName, $request);
		$this->userId = $UserId;
		$this->backupService = $backupService;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function index() {
		$params = [
			'backupDateHash' => $this->backupService->fetchFormattedBackupTimestampHash(),
			'isAdminUser' => \OC_User::isAdminUser( $this->userId )
		];

		return new TemplateResponse('ownbackup', 'main', $params);  // templates/main.php
	}

	/**
	 * Restores tables of array $tables for timestamp $timestamp
	 *
	 * @param int $timestamp
	 * @param array $tables
	 * @return DataResponse
	 * @throws \Exception
	 */
	public function doRestoreTables( $timestamp, array $tables )
	{
		$timestamp = (int) $timestamp;

		if ( is_array( $tables ) && ( count( $tables ) > 0 ) )
		{
			try
			{
				// restore tables
				$this->backupService->doRestoreTables( $timestamp, $tables );

				$message = count( $tables ) . " table(s) have been restored.";
			}
			catch( \Exception $e )
			{
				$message = "Table(s) could not be restored: " . $e->getMessage();
			}
		}
		else
		{
			$message = "No table have been restored.";
		}

		return new DataResponse(['message' => $message]);
	}

	/**
	 * Fetches the backup table names of a timestamp
	 *
	 * @param int $timestamp
	 * @return DataResponse
	 */
	public function doFetchTables( $timestamp )
	{
		$timestamp = (int) $timestamp;

		try
		{
			$tableList = $this->backupService->fetchTablesFromBackupTimestamp( $timestamp );
		}
		catch( \Exception $e )
		{
			$tableList = [];
		}

		return new DataResponse(['tables' => $tableList]);
	}

	/**
	 * Creates a new backup
	 *
	 * @return DataResponse
	 */
	public function doCreateBackup()
	{
		$message = "A new backup has been created.";

		try
		{
			// create a new backup
			$this->backupService->createDBBackup();
		}
		catch( \Exception $e )
		{
			$message = "Could not create backup: " . $e->getMessage();
		}

		// return all backup timestamps as formatted hash
		return new DataResponse([
			'message' => $message,
			'timestamps' => $this->backupService->fetchFormattedBackupTimestampHash()
		]);
	}
}
