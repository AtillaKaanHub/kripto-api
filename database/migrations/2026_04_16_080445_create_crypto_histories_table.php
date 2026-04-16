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
        Schema::create('crypto_histories', function (Blueprint $table) {
          $table->id();
            $table->string('coin'); //ETHL,SOL,BTC
           $table->decimal('price', 15, 4); //15 bas. virgülden sonra 4 hane
            $table->timestamps(); //KAYIT TARİHİ STUNLARINI OTOMATIK AÇAR
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crypto_histories');
    }
};
