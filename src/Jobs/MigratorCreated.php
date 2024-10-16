<?php

namespace Crumbls\Migrator\Jobs;

use Crumbls\Migrator\Models\Migrator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class MigratorCreated implements ShouldQueue
{
	use Queueable;

	/**
	 * Create a new job instance.
	 */
	public function __construct(
		public Migrator $record
	) {}

	/**
	 * Execute the job.
	 */
	public function handle(): void {

		$migrator = app('crumbls-migrator');

		/**
		 * If necessary, determine the driver.
		 */
		if (!$this->record->driver) {
			$type = $this->record->type;
			if ($type == 'connection') {
				if($this->isWordPressDatabase($this->record->source)) {
					$this->record->driver = 'database-wordpress';
				} else {
					$this->record->driver = 'database';
				}
			} else {
				dd(__LINE__);
			}

			if ($this->record->isDirty('driver')) {
				$this->record->update([
					'driver' => $this->record->driver
				]);


			}
		}

		$driver = $migrator
			->driver($this->record->driver)
			->initialize($this->record)
		;

		if ($this->record->tables->isEmpty()) {
			/**
			 * Create a table record for each table.
			 */
			$driver->generateTables();
		}
/*
		dd($this->record->tables->random()->columns->toArray());
		$migrator
			->driver($this->record->driver)
			->initialize([])
			->connection($this->record->source)
			->parse();
*/
	}

	protected function isWordPressDatabase($connectionName) : bool
	{
		try {
			$prefix = config('database.connections.'.$connectionName.'.prefix');
			$tables = Schema::connection($connectionName)->getTables();
			$tables = array_map(function($table) use ($prefix) {
				return Str::chopStart($table['name'], $prefix);
			}, $tables);

			$requiredTables = [
				'options',
				'users',
				'usermeta',
				'posts',
				'postmeta',
				'terms',
				'termmeta',
				'term_taxonomy',
				'term_relationships',
				'commentmeta',
				'comments',
				'links'
			];
			return !array_diff($requiredTables, $tables);
		} catch (\Exception $e) {
			return false;
		}
	}
}