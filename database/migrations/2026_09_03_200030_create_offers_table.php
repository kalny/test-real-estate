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
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_id')
                ->constrained()
                ->restrictOnDelete();
            $table->foreignId('supplier_id')
                ->constrained()
                ->restrictOnDelete();
            $table->foreignId('property_id')
                ->constrained()
                ->restrictOnDelete();
            $table->string('external_id');
            $table->date('check_in');
            $table->date('check_out');
            $table->integer('max_guests');
            $table->integer('price');
            $table->string('currency', 3);
            $table->integer('available_units');
            $table->dateTime('expires_at');
            $table->timestamps();

            $table->unique([
                'supplier_id',
                'external_id',
            ]);

            $table->index([
                'property_id',
                'check_in',
                'check_out',
                'max_guests',
                'available_units',
                'expires_at',
                'price',
            ], 'offers-search-index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};
