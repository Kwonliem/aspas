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
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Siswa
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            // Jika ada assignment table, hubungkan. Jika ini untuk Final Project course, bisa nullable.
            $table->string('title'); // Judul tugas/submission
            $table->text('content')->nullable(); // Deskripsi atau teks jawaban
            $table->string('file_path')->nullable(); // File yang diupload
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->integer('grade')->nullable(); // Nilai
            $table->text('feedback')->nullable(); // Feedback guru
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};
