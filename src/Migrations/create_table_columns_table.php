<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Crumbls\Comments\Facades\Comment;

return new class extends Migration
{
	public static function getTable() : string {
		return with(new \Crumbls\Migrator\Models\Column())->getTable();
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
			$table->foreignIdFor(\Crumbls\Migrator\Models\Table::class);
			$table->string('name');
			$table->string('source')
				->nullable()
				->default(null);
			$table->string('destination');
			$table->string('type_name');
			$table->string('type');
			$table->string('collation')
				->nullable()
				->default(null);
			$table->boolean('nullable')
				->default(false);
			$table->string('default')
				->nullable()
				->default(null);
			$table->boolean('auto_increment')
				->default(false);
			$table->string('comment')
				->nullable()
				->default(null);
			$table->string('generation')
				->nullable()
				->default(null);
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
