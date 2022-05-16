<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSearchProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('search_profiles', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->comment('Globally unique identifier for a property');
            $table->uuid('user_uuid')->nullable()->comment('Globally Unique identifier for user who created this profile');
            $table->uuid('property_type_uuid')->nullable()->comment('Globally Unique identifier for property type');

            $table->string('name')->nullable()->default(null);
            $table->integer('min_price')->nullable()->default(null);
            $table->integer('max_price')->nullable()->default(null);
            $table->integer('min_area')->nullable()->default(null);
            $table->integer('max_area')->nullable()->default(null);
            $table->integer('min_year_of_construction')->nullable()->default(null);
            $table->integer('max_year_of_construction')->nullable()->default(null);
            $table->integer('min_rooms')->nullable()->default(null);
            $table->integer('max_rooms')->nullable()->default(null);
            $table->decimal('min_return_actual')->nullable()->default(null);
            $table->decimal('max_return_actual')->nullable()->default(null);

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
        Schema::dropIfExists('search_profiles');
    }
}
