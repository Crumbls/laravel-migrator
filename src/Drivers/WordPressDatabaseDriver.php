<?php

namespace Crumbls\Migrator\Drivers;

use Crumbls\Migrator\Commands\Migrate;
use Crumbls\Migrator\Contracts\DriverInterface;
use Crumbls\Migrator\DataMappers\Column;
use Crumbls\Migrator\DataMappers\Container;
use Crumbls\Migrator\DataMappers\Table;

use Crumbls\Migrator\Models\Migrator;
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
class WordPressDatabaseDriver extends DatabaseDriver {

	public function generateTableCommentmeta() : void {
		$tableName = 'commentmeta';
		$table = $this->record->tables()->create([
			'name' => $tableName,
			'source' => $tableName,
			'destination' => 'comment_meta'
		]);
		foreach(Schema::connection($this->record->source)
			        ->getColumns($tableName) as $col) {
			$col['source'] = $col['name'];
			$table->columns()->create($col);
		}
	}

	public function generateTableUsermeta() : void {
		$tableName = 'usermeta';
		$table = $this->record->tables()->create([
			'name' => $tableName,
			'source' => $tableName,
			'destination' => 'user_meta'
		]);
		foreach(Schema::connection($this->record->source)
			        ->getColumns($tableName) as $col) {
			$col['source'] = $col['name'];
			$table->columns()->create($col);
		}
	}

}