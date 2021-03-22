<?php

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use SoluzioneSoftware\LaravelAffiliate\Traits\ResolvesBindings;

class CreateAffiliateAdvertisersTable extends Migration
{
    use ResolvesBindings;

    /**
     * Run the migrations.
     *
     * @return void
     * @throws BindingResolutionException
     */
    public function up()
    {
        Schema::connection($this->getConnection())->create($this->getTable(), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->string('network');
            $table->string('advertiser_id');
            $table->string('url');
            $table->string('name');
            $table->string('region');
            $table->char('currency', 3);
            $table->json('original_data');

            $table->unique(['network', 'advertiser_id']);
        });
    }

    /**
     * @return mixed|string|null
     * @throws BindingResolutionException
     */
    public function getConnection()
    {
        return static::resolveAdvertiserModelBinding()->getConnectionName();
    }

    /**
     * @return string
     * @throws BindingResolutionException
     */
    public static function getTable()
    {
        return static::resolveAdvertiserModelBinding()->getTable();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     * @throws BindingResolutionException
     */
    public function down()
    {
        Schema::connection($this->getConnection())->dropIfExists($this->getTable());
    }
}
