<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Curriculum Builder: a course has ordered sections (bab); a lesson may belong to a section.
     */
    public function up(): void
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->string('title');
            $table->unsignedInteger('section_order')->default(1);
            $table->timestamps();
        });

        Schema::table('lessons', function (Blueprint $table) {
            $table->foreignId('section_id')->nullable()->after('course_id')
                ->constrained('sections')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropConstrainedForeignId('section_id');
        });
        Schema::dropIfExists('sections');
    }
};
