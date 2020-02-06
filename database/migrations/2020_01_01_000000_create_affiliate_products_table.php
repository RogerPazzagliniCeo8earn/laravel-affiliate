<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAffiliateProductsTable extends Migration
{
    public function getConnection()
    {
        return Config::get('affiliate.db.connection');
    }

    public static function getTable()
    {
        return Config::get('affiliate.db.tables.products');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection($this->getConnection())->create($this->getTable(), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('feed_id');
            $table->string('product_id');
            $table->string('title');
            $table->text('description');
            $table->text('image_url')->nullable();
            $table->decimal('price');
            $table->string('currency');
            $table->timestamps();

            $table->unique(['feed_id', 'product_id']);

            Schema::enableForeignKeyConstraints();

            $table
                ->foreign('feed_id')
                ->references('id')
                ->on(Config::get('affiliate.db.tables.feeds'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists($this->getTable());
    }
}
