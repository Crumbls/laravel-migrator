<?php

namespace Crumbls\Migrator\Models;

use Crumbls\Migrator\Jobs\MigratorCreated;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Column extends Model
{
    protected $table = 'migrator_table_columns';

    protected $fillable = [
		'name',
	    'source',
	    'destination',
	    'type_name',
	    'type',
	    'collation',
	    'nullable',
	    'default',
	    'auto_increment',
	    'comment',
	    'generation'
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
		static::creating(function(Model $record) {
			if (!$record->source) {
				$record->source = $record->name;
			}
			if (!$record->destination) {
				$record->destination = $record->source;
			}
		});
	}

	public function table() : BelongsTo {
		return $this->belongsTo(Table::class);
	}
}
