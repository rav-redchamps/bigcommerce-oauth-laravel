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
        Schema::table('stores', function (Blueprint $table) {
            $table->string('admin_email')->nullable();
            $table->string('order_email')->nullable();
            $table->string('domain')->nullable();
            $table->string('industry')->nullable();
            $table->string('status')->nullable();
            $table->string('timezone')->nullable();
            $table->string('language')->nullable();
            $table->string('currency')->nullable();
            $table->string('plan_name')->nullable();
            $table->boolean('plan_is_trial')->default(false);
            $table->string('default_channel_id')->nullable();
            $table->boolean('multi_storefront_enabled')->default(false);
            $table->boolean('storefronts_active')->default(false);
            $table->boolean('stencil_enabled')->default(true);
            $table->longText('raw_information')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            //
        });
    }
};
