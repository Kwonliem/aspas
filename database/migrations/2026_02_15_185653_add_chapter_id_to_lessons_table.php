<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
           
            $table->foreignId('chapter_id')->nullable()->after('id')->constrained('chapters')->onDelete('cascade');

            
            $table->dropForeign(['course_id']); 
            $table->dropColumn('course_id');
        });
    }

    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            
            $table->foreignId('course_id')->nullable()->constrained('courses')->onDelete('cascade');

            
            $table->dropForeign(['chapter_id']);
            $table->dropColumn('chapter_id');
        });
    }
};