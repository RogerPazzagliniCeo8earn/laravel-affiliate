<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use SoluzioneSoftware\LaravelAffiliate\Models\Feed;
use SoluzioneSoftware\LaravelAffiliate\Models\Product;

class CreateAffiliateProductsTable extends Migration
{
    public function getConnection()
    {
        return (new Product())->getConnectionName();
    }

    public static function getTable()
    {
        return (new Product())->getTable();
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
            $table->text('description')->nullable();
            $table->text('image_url')->nullable();
            $table->text('details_link');
            $table->decimal('price');
            $table->string('currency');
            $table->timestamps();

            $table->unique(['feed_id', 'product_id']);

            Schema::enableForeignKeyConstraints();

            $table
                ->foreign('feed_id')
                ->references('id')
                ->on((new Feed())->getTable());
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection($this->getConnection())->dropIfExists($this->getTable());
    }
}
