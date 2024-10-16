<?php

namespace Crumbls\Migrator\Models;

use Crumbls\Migrator\Jobs\MigratorCreated;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Table extends Model
{
    protected $table = 'migrator_tables';

    protected $fillable = [
		'name',
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
		static::creating(function(Model $record) {
			if (!$record->destination) {
				$record->destination = $record->source;
			}
//			MigratorCreated::dispatchSync($record);
		});
	}

	public function columns() : HasMany {
		return $this->hasMany(Column::class);
	}

	public function migrator() : BelongsTo {
		return $this->belongsTo(Migrator::class);
	}
}
