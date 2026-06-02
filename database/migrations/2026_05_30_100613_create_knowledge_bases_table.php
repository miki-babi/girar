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
        Schema::create('knowledge_topics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('users')->cascadeOnDelete();
            $table->string('topic')->index();
            $table->json('keywords')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->index(['business_id', 'is_active']);
        });

        Schema::create('knowledge_bases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('knowledge_topic_id')->constrained()->cascadeOnDelete();
            $table->string('sub_topic')->index();
            $table->text('description');
            $table->json('keywords')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->index(['knowledge_topic_id', 'is_active']);
            $table->index(['knowledge_topic_id', 'sub_topic']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knowledge_bases');
        Schema::dropIfExists('knowledge_topics');
    }
};
