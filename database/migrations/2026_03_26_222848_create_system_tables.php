<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // customers: profil pelanggan dengan level loyalitas
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();

            $table->string('age', 20)->nullable();
            $table->string('gender', 10)->nullable();

            // Finansial & Level
            $table->decimal('total_spending', 15, 2)->default(0);
            $table->foreignId('master_level_id')
                  ->default(1)                       // ← default Bronze (id=1)
                  ->constrained('master_levels')
                  ->onDelete('restrict');             // ← FIXED: restrict, bukan cascade

            // Tracking kunjungan terakhir
            $table->unsignedInteger('total_visits')->default(0);
            $table->timestamp('last_visit')->nullable();

            $table->timestamps();

            // Index untuk lookup upsert (name + phone)
            $table->index(['name', 'phone'], 'idx_customers_lookup');
        });

        // tables: representasi meja fisik di venue
        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->float('x_pos');
            $table->float('y_pos');
            $table->string('shape', 20);
            $table->string('status', 20)->default('available')->index('idx_tables_status');
            $table->string('area_id', 50)->index('idx_tables_area');

            // Hold state (nullable — hanya terisi saat meja di-hold)
            $table->timestamp('hold_until')->nullable();
            $table->foreignId('hold_by_customer_id')
                  ->nullable()
                  ->constrained('customers')
                  ->nullOnDelete();

            $table->timestamps();
        });

        // bookings: transaksi reservasi antara pelanggan dan meja
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('table_id')
                  ->constrained('tables')
                  ->onDelete('cascade');

            $table->foreignId('customer_id')
                  ->constrained('customers')
                  ->onDelete('cascade');

            $table->unsignedSmallInteger('pax');

            $table->timestamp('start_time');
            $table->timestamp('end_time');

            // Billing info — diisi hanya saat status completed
            $table->timestamp('billed_at')->nullable();
            $table->decimal('billed_price', 15, 2)->nullable();  // ← decimal, bukan float

            // Status sebagai string (sync dengan enum FastAPI)
            $table->string('status', 20)->default('pending');
            $table->string('category', 50)->default('reguler');

            $table->text('notes')->nullable();
            $table->text('cancel_reason')->nullable();

            $table->timestamps();

            // Index performa — kolom yang paling sering di-query
            $table->index('status', 'idx_bookings_status');
            $table->index('table_id', 'idx_bookings_table');
            $table->index('customer_id', 'idx_bookings_customer');
            $table->index(['start_time', 'end_time'], 'idx_bookings_time_range');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('tables');
        Schema::dropIfExists('customers');
    }
};
