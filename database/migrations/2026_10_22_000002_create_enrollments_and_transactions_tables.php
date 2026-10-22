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
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->timestamp('enrolled_at')->useCurrent();
            $table->timestamps();

            // Anti-tampering: prevent duplicate enrollment records
            $table->unique(['user_id', 'course_id']);
        });

        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number')->unique(); // UUID or SEC-TRX-XXXX
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('course_id')->nullable()->constrained('courses')->nullOnDelete();
            $table->enum('type', ['topup', 'purchase', 'refund']);
            $table->decimal('amount', 12, 2)->unsigned();
            $table->decimal('balance_before', 12, 2)->unsigned();
            $table->decimal('balance_after', 12, 2)->unsigned();
            $table->enum('status', ['pending', 'completed', 'failed'])->default('completed');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event_type'); // e.g. 'auth.failed', 'file.upload_rejected', 'race_condition_detected'
            $table->string('ip_address', 45);
            $table->text('user_agent')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('wallet_transactions');
        Schema::dropIfExists('enrollments');
    }
};
