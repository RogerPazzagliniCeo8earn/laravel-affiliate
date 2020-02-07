<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAffiliateFeedsTable extends Migration
{
    public function getConnection()
    {
        return Config::get('affiliate.db.connection');
    }

    public static function getTable()
    {
        return Config::get('affiliate.db.tables.feeds');
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
                $table->unsignedInteger('advertiser_id');
                $table->string('advertiser_name');
                $table->unsignedInteger('feed_id')->unique(); // note: for now, we are not considering affiliate network
                $table->boolean('joined');
                $table->string('region');
                $table->char('language', 2);
                $table->dateTime('imported_at')->nullable();
                $table->dateTime('products_updated_at')->nullable();
                $table->timestamps();
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
