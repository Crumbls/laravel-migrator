<?php

namespace Crumbls\Migrator\Drivers;

use Crumbls\Migrator\Contracts\DriverInterface;

class SqlDriver implements DriverInterface {

	public function initialize(array $config) : self {
		throw new \Exception('This driver is not yet supported.');
		return $this;
	}
}