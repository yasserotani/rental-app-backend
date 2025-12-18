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
        Schema::create('apartments', function (Blueprint $table) {
            $table->id();

            // علاقة مع المستخدم
            $table->foreignId('user_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->string('adress');          // عنوان الشقة
            $table->text('description');      // وصف الشقة
            $table->string('governorate');     // المحافظة
            $table->string('city');            // المدينة
            $table->decimal('price', 10, 2);   // السعر
            $table->boolean('is_rented')->default(false); // هل مؤجرة

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apartments');
    }
};
