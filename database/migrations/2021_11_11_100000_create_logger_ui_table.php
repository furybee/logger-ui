<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoggerUiTable extends Migration
{
    /**
     * The database schema.
     *
     * @var \Illuminate\Database\Schema\Builder
     */
    protected $schema;

    protected $table;

    /**
     * Create a new migration instance.
     *
     * @return void
     */
    public function __construct()
    {
        $dbConfig = config('logger-ui.db');

        $this->table = $dbConfig['table'];

        $this->schema = Schema::connection($dbConfig['connection']);
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->create($this->table, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('channel')->index();
            $table->string('level_name')->index();
            $table->string('level')->index();
            $table->longText('message')->nullable();
            $table->longText('context')->nullable();
            $table->longText('extra')->nullable();
            $table->longText('formatted')->nullable();
            $table->string('user_id')->nullable();
            $table->dateTime('logged_at', 6);
            $table->dateTime('created_at', 6);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->dropIfExists($this->table);
    }
}
