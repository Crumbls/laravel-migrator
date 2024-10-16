<?php

namespace Crumbls\Migrator\Drivers;

use Crumbls\Migrator\Contracts\DriverInterface;
use Crumbls\Migrator\DataMappers\Column;
use Crumbls\Migrator\DataMappers\Table;
use Crumbls\Migrator\Models\Migrator;
use Illuminate\Database\Eloquent\Model;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Literal;
use Nette\PhpGenerator\PhpNamespace;

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
abstract class AbstractDriver implements DriverInterface {
	abstract public function getModelTables(string $env) : array;
	abstract public function generateTables() : void;

	protected Migrator $record;

	/**
	 * @param array $config
	 * @return $this
	 */
	public function initialize(Migrator $record) : self {
		$this->record = $record;
		return $this;
	}

	/**
	 * TODO: Add in Filament detection and appropriate exceptions.
	 * @param bool $force
	 * @param string $env
	 * @return $this
	 * @throws Exception
	 */
	public function generateFilamentResources(bool $force = false, string $env = 'destination') : self {
			if (!in_array($env, [
				'destination',
				'source'
			])) {
				throw new \Exception('Environment may only be source or destination.');
			}

			/**
			 * Write the maps.
			 */
			$map = $this->getModelTables($env);

			if (!$force) {
				$map = array_filter($map, function (Table $table) {
					return !class_exists($table->model);
				});
			}

			foreach ($map as $table) {
				$model = $table->getModel();
				\Artisan::call('make:filament-resource '.class_basename($model).' --generate');
			}

		return $this;
	}

	/**
	 * Generate models for destination classes.
	 *
	 * TODO: This is ugly.  Break it into a unique generator and simplify it.
	 *
	 * @param bool $force
	 * @return $this
	 */
	public function generateModels(bool $force = false, string $env = 'destination') : self {
		if (!in_array($env, [
			'destination',
			'source'
		])) {
			throw new \Exception('Environment may only be source or destination.');
		}

		/**
		 * Write the maps.
		 */
		$map = $this->getModelTables($env);

		if (!$force) {
			$map = array_filter($map, function(Table $table) {
				return !class_exists($table->model);
			});
		}

		foreach($map as $table) {

			$model = $table->getModel();

			$lastSlashPos = strrpos($model, '\\');

			$ns = ($lastSlashPos !== false) ? substr($model, 0, $lastSlashPos) : '';

			$model = class_basename($model);

			$destination = $this->getBasePath($ns).$model.'.php';

			if (!$force && file_exists($destination)) {
				return $this;
			}

			$fillable = $table->getFillable();

			$namespace = new PhpNamespace($ns);

			$namespace->addUse(Model::class);

			$class = new ClassType($model);

			$namespace->add($class);

			$class
				->setExtends(Model::class)
				->addComment('Auto-generated Model')
			;

			$class->addProperty('connection', $table->getConnection())
				->setProtected()
				->addComment("The conection associated with the model.\r\n\r\n@var string")
			;

			$class->addProperty('table', $table->getName())
				->setProtected()
				->addComment("The table associated with the model.\r\n\r\n@var string")
			;

			$class->addProperty('primaryKey', $table->getPrimary())
				->setProtected()
				->addComment("The primary key associated with the table.\r\n\r\n@var string")
			;

			$keyType = $table->getPrimaryInteger() ? 'int' : 'string';

			$class->addProperty('ketType', $keyType)
				->setProtected()
				->addComment("Define the primary key type as string\r\n\r\n@var string")
			;

			$class->addProperty('incrementing', $keyType == 'int')
				->setPublic()
				->addComment("Set to false if the primary key is not auto-incrementing\r\n\r\n@var string")
			;

			$class->addProperty('fillable', $fillable)
				->setProtected()
				->addComment("The attributes that are mass assignable.\r\n\r\n@var array")
			;

			file_put_contents($destination, '<?php'.PHP_EOL.$namespace);
		}
		return $this;
	}

	/**
	 * Generate migrations for models.
	 *
	 * TODO: This is ugly.  Break it into a unique generator and simplify it.
	 * TODO: Allow creation of migrations when models are not defined.
	 * TODO: Add a connection option. Adhere to non-existent model possibility.
	 *
	 * @param bool $force
	 * @return $this
	 */
	public function generateMigrations(bool $force = false, string $env = 'destination') : self {
		if (!in_array($env, [
			'destination',
			'source'
		])) {
			throw new \Exception('Environment may only be source or destination.');
		}

		/**
		 * Write the maps.
		 */
		$map = $this->getModelTables($env);

		if (!$force) {
			$map = array_filter($map, function(Table $table) {
				$destination = database_path('migrations/'.$table->getMigrationName().'.php');
				return true;
				return !file_exists($destination);
			});
		}

		/**
		 * TODO: Remove this.
		 */
//		uksort($map, fn () => rand() - rand());

		foreach($map as $table) {

			$destination = database_path('migrations/'.$table->getMigrationName().'.php');

			$model = $table->getModel();

//			$lastSlashPos = strrpos($model, '\\');

//			$ns = ($lastSlashPos !== false) ? substr($model, 0, $lastSlashPos) : '';

//			$model = class_basename($model);

//			$destination = $this->getBasePath($ns).$model.'.php';

			$namespace = new PhpNamespace('');

			$class = new ClassType(null);

//			$namespace->addUse($model, 'Model');
			$namespace->addUse(\Illuminate\Database\Migrations\Migration::class);
			$namespace->addUse(\Illuminate\Database\Schema\Blueprint::class);
			$namespace->addUse(\Illuminate\Support\Facades\Schema::class);

			$class
				->setExtends(\Migration::class)
			;

			$class->addMethod('getTable')
				->addComment('Get the model\'s table.')
				->setStatic()
				->setReturnType('string')
				->addBody('$model = \\'.$model.'::class;')
				->addBody('return with(new $model)->getTable();')
			;

			$method = $class->addMethod('up')
				->addComment("Run the migrations.\r\n\r\n@return void")
				->setReturnType('void')
				->addBody('$table = static::getTable();')
				->addBody("if (Schema::hasTable(\$table)) {\r\n\treturn;\r\n}")
			;

			$closure = new \Nette\PhpGenerator\Closure;
			$closure->addParameter('table')
				->setType('Blueprint');

			foreach($table->getChildren() as $column)  {
				$closure->addBody($this->getMigrationDefinition($column).';');
			}

			$method->addBody('Schema::create($table, '.$closure.');');

			$class->addMethod('down')
				->addComment("Reverse the migrations.\r\n\r\n@return void")
				->setReturnType('void')
				->addBody('Schema::dropIfExists(static::getTable());')
			;

			$dump = $namespace.'return new class '.$class.';';

			file_put_contents($destination, '<?php'.PHP_EOL.$dump);
		}

		return $this;
	}

	protected function getModelNamespace() : string {
		return app()->getNamespace().'Models';
	}
	protected function generateModelNameFromTable(string $tableName, string $namespace) : string
	{
		$modelName = Str::singular(Str::studly($tableName));
		return $namespace . '\\' . $modelName;
	}

	/**
	 * @deprecated
	 * @param $class
	 * @param $baseDir
	 * @return string|null
	 */
	protected function getBasePath($class, $baseDir = null)
	{
		$autoloadFile = base_path('vendor/composer/autoload_psr4.php');

		$baseNamespace = app()->getNamespace();

		if (strpos($class, $baseNamespace) === 0) {
			return app_path(substr($class, strlen($baseNamespace))).DIRECTORY_SEPARATOR;
		}

		dd(__LINE__);
		/**
		 * If it matches the app
		 */

		// Load the Composer autoload PSR-4 mappings
		$mappings = require $autoloadFile;

		// Remove leading namespace separator if present
		$namespace = ltrim($class, '\\');

		// Replace namespace separators with directory separators
		$relativePath = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . '.php';



		// Determine the base directory for the given namespace prefix
		foreach ($mappings as $prefix => $baseDirs) {
			if (strpos($namespace, $prefix) === 0) {
				foreach ((array)$baseDirs as $baseDir) {
					$filePath = $baseDir . DIRECTORY_SEPARATOR . $relativePath;
					if (file_exists($filePath)) {
						return $filePath;
					}
				}
			}
		}

		// Try to find the most specific matching base directory
		foreach ($mappings as $prefix => $baseDirs) {
			if (strpos($namespace, $prefix) === 0) {
				foreach ((array)$baseDirs as $baseDir) {
					$potentialPath = str_replace($prefix, $baseDir, $namespace);
					$potentialPath = str_replace('\\', DIRECTORY_SEPARATOR, $potentialPath) . '.php';
					if (file_exists($potentialPath)) {
						return $potentialPath;
					}
				}
			}
		}

		// If no match is found, return null or an appropriate default
		return null;
	}


	protected function getLaravelType(string $type) : string {

//		$type = preg_replace('#\(([\'|a-z|0-9].*)\)\s?#', '', $type);
		$type = preg_replace('/\(([^()]*+|(?R))*\)/', '', $type);

		$typeMapping = [
			'binary' => 'binary',
			'bigint' => 'bigInteger',
			'bigint unsigned' => 'unsignedBigInteger',
			'blob' => 'binary',
			'char' => 'char',
			'datetime' => 'dateTime',
			'double' => 'double',
			'double unsigned' => 'double,unsigned',
			'enum' => 'enum',
			'float' => 'float',
			'int' => 'integer',
			'int unsigned' => 'unsignedInteger',
			'longblob' => 'longText,binary',
			'longtext' => 'longText',
			'mediumtext' => 'mediumText',
			'text' => 'text',
			'timestamp' => 'timestamp',
			'tinyblob' => 'tinyText,binary',
			'tinyint' => 'tinyInteger',
			'tinyint unsigned' => 'unsignedTinyInteger',
			'tinytext' => 'tinyText',
			'unsigned bigint' => 'unsignedBigInteger',
			'varbinary' => 'var,binary',
			'varchar' => 'string'
		];

		if (!array_key_exists($type, $typeMapping)) {
			throw new \Exception($type);
		}

		$temp = $typeMapping[$type] ?? 'string';

		$this->unsigned(stripos($temp, 'unsigned') !== false);

		return $temp;
	}


	public function getMigrationDefinition(Column $column)
	{
		$columnType = null;

		if (!$columnType) {
			$columnType = $this->getMigrationMethod($column->getType());
		}


		$ret = null;

		$numeric = $columnType == 'integer' || in_array($column->getType(), [
			'integer',
			'bigint',
			'mediumint',
			'smallint',
			'tinyint',
			'decimal',
			'float',
			'double'
		]);

		if ($numeric) {
			$ret = '$table->'.$columnType.'(\''.$column->getName().'\')';
		} else {

		$ret = '$table->'.$columnType.'(\''.$column->getName().'\'';

		if ($temp = $column->getCharacterMaxLength()) {
			$ret .= ', '.$temp;
		} else if ($temp = $column->getNumericPrecision()) {
			$ret .= ', '.$temp;
			$temp = $column->getNumericScale();
			$ret .= ', '.$temp;
		}

		$ret .= ')';
		}

		if ($column->getUnsigned()) {
			$ret .= "\r\n\t->unsigned()";
		}

		if ($column->getNullable()) {
			$ret .= "\r\n\t->nullable()";
		}
		$temp = $column->getDefault();
		if ($temp || $temp === null) {
			if ($temp === null) {
				$temp = 'null';
			} else if (!is_numeric($temp)) {
				$temp = "'".$temp."'";
			}
			$ret .= "\r\n\t".'->default('.$temp.')';
		}

		return $ret;
	}

	/**
	 * TODO: Move these to Column.
	 * @param $type
	 * @return string
	 */
	protected function getMigrationMethod($type)
	{
		$typeMapping = [
			'bigint' => 'bigInteger',
			'varchar' => 'string',
			'int' => 'integer',
			'boolean' => 'boolean',
			'date' => 'date',
			'datetime' => 'dateTime',
			'text' => 'text',
			'char' => 'char',
			'decimal' => 'decimal',
			// Add more type mappings as needed...
		];

		return $typeMapping[$type] ?? 'string';
	}
}