<?php

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use SoluzioneSoftware\LaravelAffiliate\Traits\ResolvesBindings;

class CreateAffiliateProductsTable extends Migration
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
            $table->unsignedBigInteger('feed_id');
            $table->string('product_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('image_url')->nullable();
            $table->text('details_link');
            $table->decimal('price');
            $table->string('currency');
            $table->dateTime('last_updated_at')->nullable()->comment('provided by affiliate network');
            $table->timestamps();

            $table->unique(['feed_id', 'product_id']);

            Schema::enableForeignKeyConstraints();

            $table
                ->foreign('feed_id')
                ->references('id')
                ->on(static::resolveFeedModelBinding()->getTable())
                ->onDelete('cascade');
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
        Schema::connection($this->getConnection())->dropIfExists($this->getTable());
    }
}
