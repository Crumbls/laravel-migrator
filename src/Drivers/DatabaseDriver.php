<?php

namespace Crumbls\Migrator\Drivers;

use Crumbls\Migrator\Contracts\DriverInterface;
use Crumbls\Migrator\DataMappers\Column;
use Crumbls\Migrator\DataMappers\Container;
use Crumbls\Migrator\DataMappers\Table;

use Exception;
use Illuminate\Support\Facades\Storage;

use Illuminate\Console\Command;;
use Config;
use DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * This is a generic database driver that does not presume anything about the remote system.
 */
class DatabaseDriver extends AbstractDriver {

	private string $connection;

	/**
	 * @var array
	 */
	protected array $containers;

	/**
	 * @param array $config
	 * @return $this
	 */
	public function initialize(array $config) : self {
		return $this;
	}

	/**
	 * @return string|null
	 */
	protected function getConnectionPrefix() : string|null {
		return Config::get('database.connections.'.$this->connection.'.prefix');
	}

	public function parse() {
		if (!isset($this->connection)) {
			throw new \Exception('Connection not defined.');
		}

		$prefix = $this->getConnectionPrefix();

		if (!isset($this->connection)) {
			throw new \Exception('Connection not defined.');
		}

		/**
		 * To prevent duplicate iterations.
		 */
		if (isset($this->containers) && $this->containers) {
			return $this;
		}

		/**
		 * Verify the database.
		 * TODO: Remove. No longer needed.
		 */
		/*
		// Switch to the selected connection
//		Config::set('database.default', $this->connection);

//		DB::purge($this->connection);

//		DB::reconnect($this->connection);
		*/

		// List the tables in the selected database
		$tables = array_column(Schema::connection($this->connection)->getTables(), 'name');

		if (!$tables) {
			throw new \Exception('No tables in database.');
		}

		$tables = array_map(function($table) use ($prefix) {
			return Str::chopStart(array_values((array) $table)[0], $prefix);
		}, $tables);

		$this->containers = [];

		$namespace = $this->getModelNamespace();

		foreach($tables as $tableName) {
			$container = new Container();

			/**
			 * Add in any prebuilds.
			 * TODO: Use Laravel built in via Schema.
			 */
			$columns = \DB::connection($this->connection)
				->table(\DB::raw('INFORMATION_SCHEMA.COLUMNS'))
				->select([
					'COLUMN_NAME',
					'ORDINAL_POSITION',
					'COLUMN_DEFAULT',
					'IS_NULLABLE',
					'DATA_TYPE',
					'CHARACTER_MAXIMUM_LENGTH',
					'CHARACTER_OCTET_LENGTH',
					'NUMERIC_PRECISION',
					'NUMERIC_SCALE',
					'DATETIME_PRECISION',
//					'CHARACTER_SET_NAME',
					'COLUMN_TYPE',
					'COLUMN_KEY',
					'EXTRA',
					'COLUMN_COMMENT'
				])
				->where('TABLE_NAME', $prefix . $tableName)
				->where('TABLE_SCHEMA', Config::get('database.connections.' . $this->connection . '.database'))
				->orderBy('ORDINAL_POSITION', 'asc')
				->get();

			$table = Table::create()
				->connection($this->connection)
				->name($tableName)
				->model($this->generateModelNameFromTable($tableName, $namespace));

			foreach ($columns as $col) {
				$column = Column::create()
					->name($col->COLUMN_NAME)
					->order($col->ORDINAL_POSITION)
					->default($col->COLUMN_DEFAULT)
					->nullable($col->IS_NULLABLE)
					->type($col->DATA_TYPE)
					->typeDetailed($col->COLUMN_TYPE)
					->characterMaxLength($col->CHARACTER_MAXIMUM_LENGTH)
					->characterOctetLength($col->CHARACTER_OCTET_LENGTH)
					->numericPrecision($col->NUMERIC_PRECISION)
					->numericScale($col->NUMERIC_SCALE)
					->dateTimePrecision($col->DATETIME_PRECISION)
					;

				foreach($col as $k => $v) {
					if ($col->COLUMN_KEY == 'PRI') {
						$table->primary($column->getName());
						// Check if the primary key is numeric
						$table->primaryInteger(in_array($col->DATA_TYPE, [
							'integer',
							'bigint',
							'mediumint',
							'smallint',
							'tinyint',
							'decimal',
							'float',
							'double'
						]));
						$table->primaryAutoIncrement($col->EXTRA == 'auto_increment');
						break;
					}
				}

				$table->addChild($column);
			}

			/**
			 * Set source table.
			 */
			$container->source($table);

			$table->connection(Config::get('database.default'));

			$container->destination($table);

			/**
			 * Append
			 */
			$this->containers[] = $container;
		}

		return $this;
	}


	/**
	 * @return array
	 */
	private function extractGeneric(XMLReader $reader, ?Generic $fluent)
	{
		if (!$fluent) {
			dd($reader);
		}

		$depth = $reader->depth;
		$localName = $reader->localName;

		while ($reader->read()) {
			if ($reader->nodeType == XMLReader::END_ELEMENT && $reader->localName == $localName) {
				break;
			} elseif ($reader->depth < $depth) {
				break;
			} else if ($reader->nodeType == XMLReader::SIGNIFICANT_WHITESPACE) {
				continue;
			} else if (in_array($reader->localName, [
				'creator',
				'link',
				'pubDate',
				'title'
			])) {
					$attr = $reader->localName;
					$cd = $reader->depth;

					$temp = '';
					while ($reader->read() && $reader->depth > $cd) {
						$temp .= $reader->value;
					}
					$fluent->$attr = trim($temp);
					continue;
				}
			foreach($this->getAttributes($reader) as $k => $v) {
				$fluent->$k = $v;
			}
			dd($fluent);
			$depth = $reader->depth;

			if (!$reader->isEmptyElement) {
				dd($reader);
				dd(__LINE__);
				$childs = $this->iterate($reader);
				$node['type'] = is_array($childs) ? 'element' : 'text';
				$node['value'] = $childs;
			}

			dd($reader->localName);
			switch ($reader->nodeType) {
				case XMLReader::ELEMENT:

					$node = null;
					$node_name = false;
					if ($reader->depth == 2) {
						if ($reader->name == 'item') {
							$this->export->addPost($this->extractPost($reader));
//							return $tree;
						} else if ($reader->name == 'wp:author') {
//							return $tree;
						} else if ($reader->name == 'wp:wxr_version') {
							/**
							 * Define the generator version.
							 */
							$value = $this->iterate($reader);
							$this->export->version($value);
//return $tree;
						}
					}

					if (!$node) {
						$node = array();
						$node['tag'] = $node_name = $reader->name;
						$node['attributes'] = $this->getAttributes($reader);
						$node['depth'] = $reader->depth;

						if (!$reader->isEmptyElement) {
							$childs = $this->iterate($reader);
							$node['type'] = is_array($childs) ? 'element' : 'text';
							$node['value'] = $childs;
						}
					}

					if (array_key_exists($node_name, $tree))
					{
						if (is_object($tree[$node_name])) {

							$temp = $tree[$node_name];
							unset($tree[$node_name]);

							$tree[$node_name][] = $temp;
							// TODO: Check and fix.

						} else if (!array_key_exists(0, $tree[$node_name]))
						{
							$temp = $tree[$node_name];
							unset($tree[$node_name]);
							$tree[$node_name][] = $temp;
						}

						$tree[$node_name][] = $node;
					}
					else
					{
						$tree[$node_name] = $node;
					}

				case XMLReader::TEXT:
					if (trim($reader->value))
					{
						$tree = trim($reader->value);
					}

				default:
					break;
			}
		}
		return $fluent;
	}

	public function connection(string $connection) : self {
		if (isset($this->connection)) {
			throw new \Exception('Connection is already defined.');
		}
		$this->connection = $connection;
		return $this;
	}

	protected function convertToCamelCase($string) : string
	{
		$string = preg_replace_callback('/([A-Z])([A-Z]+)/', function ($matches) {
			return strtolower($matches[1]) . ucfirst(strtolower($matches[2]));
		}, $string);

		$string = strtolower($string);

		$string = preg_replace_callback('/_([a-z])/', function ($matches) {
			return strtoupper($matches[1]);
		}, $string);

		return $string;
	}

	public function getModelTables(string $env): array
	{
		if (!isset($this->containers)) {
			throw new \Exception('Containers are not yet defined.');
		}

		if (!in_array($env, [
			'destination',
			'source'
		])) {
			throw new \Exception('Environment may only be source or destination.');
		}

		return array_filter(array_map(function(Container $container) use ($env) {
			return $container->$env;
		}, $this->containers));
	}

	public function getTableFillable(): array
	{
		dd($this->tables);
		// TODO: Implement getTableFillable() method.
	}
}