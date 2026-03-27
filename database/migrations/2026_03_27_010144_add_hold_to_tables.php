<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DEPRECATED: Kolom hold_until dan hold_by_customer_id
 * sudah digabungkan ke dalam 2026_03_26_222848_create_system_tables.php
 * Migration ini dipertahankan agar history tetap ada,
 * namun tidak melakukan apa-apa (idempotent).
 */
return new class extends Migration
{
    public function up(): void
    {
        // No-op: hold columns already defined in create_system_tables migration
    }

    public function down(): void
    {
        // No-op
    }
};
