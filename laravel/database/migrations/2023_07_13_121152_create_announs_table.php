<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('announs', function (Blueprint $table) {
            $table->id();
            $table->string('deal_type')->nullable();
            $table->string('type_real_estate')->nullable();
            // $table->string('city')->nullable();
            $table->float('coord_x')->nullable();
            $table->float('coord_y')->nullable();
            $table->integer('floor')->nullable();
            $table->integer('floor_in_house')->nullable();
            $table->string('year_of_construction')->nullable();
            $table->integer('quantity_room')->nullable();
            $table->integer('total_area')->nullable();
            $table->integer('living_area')->nullable();
            $table->integer('kitchen_area')->nullable();
            $table->text('link_video')->nullable();
            $table->string('parking')->nullable();
            $table->string('elevator')->nullable();
            $table->string('Balcony')->nullable();
            $table->integer('number_of_rent')->nullable();
            $table->integer('land_area')->nullable();
            $table->string('Rent')->nullable();
            $table->string('Land_category')->nullable();
            $table->integer('house_area')->nullable();
            $table->integer('quantity_bedrooms')->nullable();
            $table->integer('numberSeats')->nullable();
            $table->string('condition_of_the_home')->nullable();//состояние дома
            $table->string('electricity')->default('есть');
            $table->string('rental_period')->nullable(); //срок аренды
            $table->string('repair')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('layout')->nullable();
            $table->integer('CeilingHeight')->nullable();
            $table->string('furniture')->nullable();
            $table->integer('price')->nullable();
            $table->string('rental_type')->nullable();
            $table->string('house_type')->nullable();
            $table->string('type_object')->nullable();
            $table->integer('building_area')->nullable();
            $table->string('state')->nullable();
            $table->string('allowKids')->default('нет');
            $table->string('allowAnimal')->default('нет');
            $table->integer('owner_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announs');
    }
};