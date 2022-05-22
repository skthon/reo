<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePropertiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->comment('Globally unique identifier for a property');
            $table->uuid('user_uuid')->nullable()->comment('Globally Unique identifier for user');
            $table->uuid('property_type_uuid')->nullable()->comment('Globally Unique identifier for property type');

            $table->string('name')->nullable()->default(null);
            $table->text('address')->nullable()->default(null);
            $table->integer('price')->nullable()->default(null);
            $table->integer('area')->nullable()->default(null);
            $table->integer('year_of_construction')->nullable()->default(null);
            $table->integer('rooms')->nullable()->default(null);
            $table->string('heating_type')->nullable()->default(null);
            $table->boolean('parking')
                ->nullable()
                ->default(null)
                ->comment('Toggles whether parking is available or not');
            $table->decimal('return_actual')->nullable()->default(null);
            $table->boolean('status')->nullable()->default(true);

            $table->foreign('property_type_uuid')
                ->references('uuid')->on('property_types')
                ->onDelete('SET NULL')
                ->onUpdate('no action');

            $table->foreign('user_uuid')
                ->references('uuid')->on('users')
                ->onDelete('SET NULL')
                ->onUpdate('no action');

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
        Schema::dropIfExists('properties');
    }
}
