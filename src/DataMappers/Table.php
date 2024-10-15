<?php

namespace Crumbls\Migrator\DataMappers;

use Illuminate\Support\Str;

class Table extends Generic {
	private static array $migrationsUsed;
	/**
	 * Connection name.
	 *
	 * @var string
	 */
	protected string $connection = 'default';

	/**
	 * Table name.
	 *
	 * @var string
	 */
	protected string $name;

	/**
	 * Migration name.
	 *
	 * @var string
	 */
	protected string $migrationName;

	/**
	 * Model name.
	 *
	 * @var string
	 */
	protected string $model;

	/**
	 * Primary key.
	 *
	 * @var string
	 */
	protected string $primaryKey;

	/**
	 * Is the primary key numeric?
	 *
	 * @var string
	 */
	protected bool $primaryKeyInteger;

	/**
	 * Is the primary key auto-incrementing?
	 *
	 * @var string
	 */
	protected bool $primaryKeyAutoIncrement;


	/**
	 * Set the connection name.

	 * @param string $value
	 * @return $this
	 */
	public function connection(string $value = 'default') : self {
		$this->connection = $value;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getConnection() : string {
		return $this->connection;
	}


	/**
	 * Set the table name.
	 *
	 * @param string $value
	 * @return $this
	 */
	public function name(string $value) : self {
		$this->name = $value;
		return $this;
	}


	/**
	 * @return string
	 */
	public function getName() : string {
		return $this->name;
	}

	/**
	 * Set the migration name.
	 *
	 * @param string $value
	 * @return self
	 */
	public function migrationName(string $value) : self {
		$this->migrationName = $value;
	}

	/**
	 * @return string
	 */
	public function getMigrationName() : string {
		if (isset($this->migrationName) && $this->migrationName) {
			return $this->migrationName;
		}

		$tableName = Str::snake(Str::pluralStudly(class_basename($this->getModel())));

		if (!isset(static::$migrationsUsed)) {
			/**
			 * Get the highest migrations
			 */
			$basis = date('Y_m_d_');
			$path = database_path('migrations') . DIRECTORY_SEPARATOR;
			$files = glob($path . $basis . '*_create_*_table.php');

			if ($files) {
				static::$migrationsUsed = preg_grep('#^\d{4}\_\d{2}\_\d{2}\_\d{6}\_#', array_map(function($file) {
					return basename($file, '.php');
				}, $files));

				if ($existing = preg_grep('#\_create\_'.$tableName.'\_table#', static::$migrationsUsed)) {
					$this->migrationName = array_values($existing)[0];
					return $this->migrationName;
				}
			} else {
				static::$migrationsUsed = [];
			}
		} else if ($existing = preg_grep('#\_create\_'.$tableName.'\_table#', static::$migrationsUsed)) {
			$this->migrationName = array_values($existing)[0];
			return $this->migrationName;
		}

		/**
		 * Get maximum prefix.
		 */

		$basis = static::$migrationsUsed ? (int)substr(static::$migrationsUsed[count(static::$migrationsUsed)-1], 11, 6) : 0;

		$basis += 10;

		$basis = str_pad($basis, 6, '0', STR_PAD_LEFT);

//		static::$migrationsUsed[] = '2024_'

		if ($basis > 999999) {
			/**
			 * Find random, unused integer.
			 */
			do {
				$basis = date('Y_m_d').'_'.rand(100000, 999999);
			} while (\Arr::first(static::$migrationsUsed, function($v) use ($basis) {
				return substr($v, 11, 6) == $basis;
			}));

		}

		$this->migrationName = date('Y_m_d').'_'.$basis . '_create_' . $tableName . '_table';

		static::$migrationsUsed = [
			$this->migrationName
		];

		return $this->migrationName;
	}


	/**
	 * Set the model name.
	 *
	 * @param string $value
	 * @return $this
	 */
	public function model(string $value) : self {
		$this->model = $value;
		return $this;
	}

	/**
	 * Get the model name.
	 * @return string
	 */
	public function getModel() : string {
		return $this->model;
	}

	/**
	 * Set the primary key.
	 *
	 * @param string $value
	 * @return $this
	 */
	public function primary(string $value) : self {
		$this->primaryKey = $value;
		return $this;
	}

	public function getPrimary() : string {
		return $this->primaryKey;
	}

	/**
	 * Set the primary key auto-increment enabled status.
	 *
	 * @param bool $value
	 * @return $this
	 */
	public function primaryAutoIncrement(bool $value) : self {
		$this->primaryKeyAutoIncrement = $value;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function getPrimaryAutoIncrement() : bool {
		return $this->primaryKeyAutoIncrement ?? false;
	}

	/**
	 * Define the primary key as an integer.
	 *
	 * @param bool $value
	 * @return $this
	 */
	public function primaryInteger(bool $value) : self {
		$this->primaryKeyInteger = $value;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function getPrimaryInteger() : bool {
		return $this->primaryKeyInteger;
	}


	/**
	 * Get fillable properties.
	 *
	 * @return array
	 */
	public function getFillable() : array {
		if (!isset($this->children) || !$this->children) {
			return [];
		}

		$ret = array_map(function(Column $child) {
			return $child->getName();
		}, $this->children);

		/**
		 * Remove primary key if auto-increment.
		 */
		if ($this->getPrimaryAutoIncrement()) {
			$key = $this->getPrimary();
			$x = array_search($key, $ret);
			if ($x !== false) {
				unset($ret[$x]);
			}
		}

		return array_values($ret);
	}


	/**
	 * TODO: Clean below.
	 */

	public function toArray()
	{
		$result = [
			'fromConnection' => $this->fromConnection,
			'toConnection' => $this->toConnection,
			'fromName' => $this->fromName,
			'toName' => $this->toName,
			'children' => []
		];

		foreach ($this->children as $child) {
			$result['children'][] = $child->toArray();
		}

		$r = \Arr::random($result['children']);

//		'temp' => $this->arrayToMigrationColumn('a', 'b', 'c')


		return $result;
	}
}