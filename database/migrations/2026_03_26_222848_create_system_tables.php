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
        // Drop existing tables created by FastAPI (if any) to re-create via Laravel
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('tables');
        Schema::dropIfExists('customers');

        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('category')->default('reguler');
            $table->integer('age')->nullable();
            $table->string('gender')->nullable();
            $table->decimal('total_spending', 15, 2)->default(0);
            $table->foreignId('master_level_id')->constrained('master_levels')->onDelete('cascade');
            $table->string('last_status')->nullable();
            $table->timestamp('last_visit')->nullable();
            $table->timestamps();
        });

        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->float('x_pos');
            $table->float('y_pos');
            $table->string('shape');
            $table->string('status')->default('available');
            $table->string('area_id');
            $table->timestamps();
        });

        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('table_id')->constrained('tables')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->integer('pax');
            $table->timestamp('start_time');
            $table->timestamp('end_time');
            $table->timestamp('billed_at')->nullable();
            $table->decimal('billed_price', 15, 2)->nullable();
            $table->string('status')->default('pending');
            $table->text('notes')->nullable();
            $table->text('cancel_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('tables');
        Schema::dropIfExists('customers');
    }
};
