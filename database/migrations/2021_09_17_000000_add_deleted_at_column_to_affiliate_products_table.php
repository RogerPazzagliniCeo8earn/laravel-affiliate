<?php

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use SoluzioneSoftware\LaravelAffiliate\Traits\ResolvesBindings;

class AddDeletedAtColumnToAffiliateProductsTable extends Migration
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
        Schema::connection($this->getConnection())->table($this->getTable(), function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * @return mixed|string|null
     * @throws BindingResolutionException
     */
    public function getConnection()
    {
        return static::resolveProductModelBinding()->getConnectionName();
    }

    /**
     * @return mixed|string
     * @throws BindingResolutionException
     */
    public static function getTable()
    {
        return static::resolveProductModelBinding()->getTable();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     * @throws BindingResolutionException
     */
    public function down()
    {
        Schema::connection($this->getConnection())->table($this->getTable(), function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
}
