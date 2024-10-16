<?php

namespace Crumbls\Migrator\Models;

use Crumbls\Migrator\Jobs\MigratorCreated;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Migrator extends Model
{
    protected $table = 'migrators';

    protected $fillable = [
		'name',
		'type',
	    'source',
	    'destination'
    ];

	/**
	 * Get the attributes that should be cast.
	 *
	 * @return array<string, string>
	 */
	protected function casts(): array
	{
		return [
//			'created_at' => 'datetime:Y-m-d',
		];
	}


	public static function booted() : void {
		/**
		 * Execute the job to initialize this record, if necessary.
		 * Right now, we are avoiding observers.
		 */
		static::created(function(Model $record) {
			MigratorCreated::dispatchSync($record);
		});
	}

	public function tables() : HasMany {
		return $this->hasMany(Table::class);
	}
}
