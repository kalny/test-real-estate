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
        Schema::create('imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')
                ->constrained()
                ->restrictOnDelete();
            $table->string('external_import_id');
            $table->string('status', 16)
                ->default('pending')
                ->comment('pending, processing, completed, failed');
            $table->text('error')
                ->nullable();
            $table->json('payload');
            $table->integer('total_offers')
                ->default(0);
            $table->dateTime('sent_at');
            $table->dateTime('completed_at')
                ->nullable();
            $table->timestamps();

            $table->unique([
                'supplier_id',
                'external_import_id',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imports');
    }
};
