<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // ── Tabel untuk template broadcast ───────────────────────────────────
        Schema::create('broadcast_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('message');
            $table->json('variables')->nullable(); // e.g., ["name", "company"]
            $table->string('type')->default('promotion'); // promotion, info, greeting
            $table->timestamps();
        });

        // ── Tabel untuk log kampanye broadcast ───────────────────────────────
        Schema::create('broadcast_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('template_id')
                ->nullable()
                ->constrained('broadcast_templates')
                ->nullOnDelete();
            $table->text('message_content');
            $table->enum('status', ['draft', 'running', 'completed', 'failed'])->default('draft')->index();
            $table->integer('total_recipients')->default(0);
            $table->integer('successful_sends')->default(0);
            $table->integer('failed_sends')->default(0);
            $table->timestamps();
        });

        // ── Tabel untuk detail log per pesan ─────────────────────────────────
        Schema::create('broadcast_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')
                ->constrained('broadcast_campaigns', 'id', 'logs_campaign_id_fk')
                ->cascadeOnDelete();
            $table->foreignId('customer_id')
                ->nullable()
                ->constrained('customers', 'id', 'logs_customer_id_fk')
                ->nullOnDelete();
            $table->string('phone_number')->index();
            $table->enum('status', ['pending', 'success', 'failed'])->default('pending')->index();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('broadcast_logs');
        Schema::dropIfExists('broadcast_campaigns');
        Schema::dropIfExists('broadcast_templates');
    }
};
