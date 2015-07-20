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

use PHPUnit_Framework_TestCase;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\TemplateResponse;


class PageControllerTest extends PHPUnit_Framework_TestCase {

	private $controller;
	private $userId = 'john';

	public function setUp() {
		$request = $this->getMockBuilder('OCP\IRequest')->getMock();
		$backupService = $this->getMockBuilder('OCA\OwnBackup\Service\BackupService')->getMock();

		$this->controller = new PageController(
			'ownbackup', $request, $backupService, $this->userId
		);
	}

	public function testIndex() {
		$result = $this->controller->index();

		$this->assertEquals(['backupDateHash' => NULL], $result->getParams());
		$this->assertEquals('main', $result->getTemplateName());
		$this->assertTrue($result instanceof TemplateResponse);
	}

	public function testDoRestoreTables() {
		$timestamp = time();
		$tables = ["some_table"];

		$result = $this->controller->doRestoreTables( $timestamp, $tables );

		$this->assertEquals(['message' => '1 table(s) have been restored.'], $result->getData());
		$this->assertTrue($result instanceof DataResponse);
	}

	public function testDoFetchTables() {
		$timestamp = time();

		$result = $this->controller->doFetchTables( $timestamp );

		$this->assertEquals(['tables' => NULL], $result->getData());
		$this->assertTrue($result instanceof DataResponse);
	}

	public function testDoCreateBackup() {
		$result = $this->controller->doCreateBackup();

		$this->assertEquals(['timestamps' => NULL], $result->getData());
		$this->assertTrue($result instanceof DataResponse);
	}
}
