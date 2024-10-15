<?php


namespace Crumbls\Migrator\Commands;

use Illuminate\Console\Command;;
use Config;
use DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class Migrate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrator:execute {type?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
		throw new \Exception('Sorry, we are not including this command yet.  It is here just for testing.');

	    $choice = $this->argument('type');

	    if (!$choice) {
		    $choice = $this->choice(
			    'Pick a source for migration',
			    [
				    'Database Connection',
//				    'Use existing file',
			    ],
			    0
		    );
	    }

		$method = \Str::camel('initialize '.$choice);
dd($method);
		$this->$method();
    }

	protected function initializeDatabaseConnection() : void {
		$this->info(__METHOD__);
		/*
		$connections = array_keys(Config::get('database.connections'));

		$choice = $this->choice(
			'Choose a database connection:',
			$connections,
			0
		);
		*/
		$choice = 'devilsdictionary';

		$this->info("You selected: {$choice}");

		$prefix = Config::get('database.connections.'.$choice.'.prefix');

		/**
		 * Verify the database.
		 */
		// Switch to the selected connection
		Config::set('database.default', $choice);
		DB::purge($choice);
		DB::reconnect($choice);

		// List the tables in the selected database
		$tables = DB::select('SHOW TABLES');

		if (!$tables) {
			throw new \Exception('No tables in database.');
		}

		$tables = array_map(function($table) use ($prefix) {
			return Str::chopStart(array_values((array) $table)[0], $prefix);
		}, $tables);

		$required = [
			'posts',
			'postmeta',
			'options',
			'comments',
			'commentmeta',
			'terms',
			'termmeta',
			'term_taxonomy',
			'term_relationships',
			'users',
			'usermeta'
		];

		$missing = array_diff($required, $tables);
		if ($missing) {
			throw new \Exception('Missing the following tables: '.implode(', ', $missing));
		}

		$migrator = app('crumbls-migrator');

		$migrator
			->driver('database')
			->initialize([])
			->connection($choice)
			->parse();;
	}
}
