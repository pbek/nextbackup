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
	private $config;

	public function __construct($AppName, IRequest $request, BackupService $backupService, $UserId, IConfig $config){
		parent::__construct($AppName, $request);
		$this->userId = $UserId;
		$this->backupService = $backupService;
		$this->config = $config;
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
				// enabled maintenance mode
				$this->config->setSystemValue('maintenance', true);

				// restore tables
				$this->backupService->doRestoreTables( $timestamp, $tables );
			}
			catch( \Exception $e )
			{
				$this->config->setSystemValue('maintenance', false);
				throw $e;
			}

			$this->config->setSystemValue('maintenance', false);

			$message = count( $tables ) . " table(s) have been restored.";
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

		$tableList = $this->backupService->fetchTablesFromBackupTimestamp( $timestamp );
		return new DataResponse(['tables' => $tableList]);
	}

	/**
	 * Creates a new backup
	 *
	 * @return DataResponse
	 */
	public function doCreateBackup()
	{
		// create a new backup
		$this->backupService->createDBBackup();

		// return all backup timestamps as formatted hash
		return new DataResponse(['timestamps' => $this->backupService->fetchFormattedBackupTimestampHash()]);
	}
}
