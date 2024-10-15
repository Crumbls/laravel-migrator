<?php

namespace Crumbls\Migrator\Managers;

use Crumbls\Migrator\Drivers\DatabaseDriver;
use Crumbls\Migrator\Drivers\RegexXmlDriver;
use Crumbls\Migrator\Drivers\SimpleXmlDriver;
use Crumbls\Migrator\Drivers\XmlDriver;
use Illuminate\Support\Manager;
class MigrationManager extends Manager {
	public function getDefaultDriver()
	{
		return 'database';
	}

	public function createXmlDriver() {
		if (extension_loaded( 'simplexml' ) ) {
			return new SimpleXmlDriver();
		} elseif ( extension_loaded( 'xml' ) ) {
			return new XmlDriver();
		}
		return new RegexXmlDriver();
	}
	public function createDatabaseDriver()
	{
		return new DatabaseDriver();
	}
}