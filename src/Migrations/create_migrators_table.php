<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Crumbls\Comments\Facades\Comment;

return new class extends Migration
{
	public static function getTable() : string {
		return with(new \Crumbls\Migrator\Models\Migrator())->getTable();
	}

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up(): void
	{
		$table = static::getTable();

		Schema::create($table, function (Blueprint $table) {
			$table->id();
			$table->string('name');
			$table->string('type');
			$table->string('driver')
				->nullable()
				->default(null);
			$table->string('source');
			$table->string('destination');
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down(): void
	{
		Schema::dropIfExists(static::getTable());
	}
};
