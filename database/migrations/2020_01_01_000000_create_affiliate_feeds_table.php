<?php

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use SoluzioneSoftware\LaravelAffiliate\Traits\ResolvesBindings;

class CreateAffiliateFeedsTable extends Migration
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
            $table->unsignedInteger('advertiser_id');
            $table->string('advertiser_name');
            $table->unsignedInteger('feed_id')->unique(); // note: for now, we are not considering affiliate network
            $table->boolean('joined');
            $table->boolean('enabled')->default(false);
            $table->string('region');
            $table->char('language', 2);
            $table->dateTime('imported_at')->nullable();
            $table->dateTime('downloaded_at')->nullable();
            $table->unsignedInteger('products_count');
            $table->dateTime('products_updated_at')->nullable();
            $table->json('original_data');
            $table->timestamps();
        });
    }

    /**
     * @return mixed|string|null
     * @throws BindingResolutionException
     */
    public function getConnection()
    {
        return static::resolveFeedModelBinding()->getConnectionName();
    }

    /**
     * @return string
     * @throws BindingResolutionException
     */
    public static function getTable()
    {
        return static::resolveFeedModelBinding()->getTable();
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
