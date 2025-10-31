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
        Schema::table('documents', function (Blueprint $table) {
            // AI analiz alanları
            $table->integer('ai_risk_score')->nullable()->after('rejection_note');
            $table->string('ai_validity', 20)->nullable()->after('ai_risk_score'); // ok, warning, critical
            $table->json('ai_warnings')->nullable()->after('ai_validity');
            $table->text('ai_summary')->nullable()->after('ai_warnings');
            
            // AI karar alanları
            $table->string('ai_decision', 20)->nullable()->after('ai_summary'); // approved, rejected, pending
            $table->timestamp('ai_notified_at')->nullable()->after('ai_decision');
            $table->timestamp('ai_decided_at')->nullable()->after('ai_notified_at');
            
            // Öğrenme alanları
            $table->timestamp('ai_learned_at')->nullable()->after('ai_decided_at');
            $table->boolean('ai_was_correct')->nullable()->after('ai_learned_at');
            
            // Index'ler
            $table->index('ai_decision');
            $table->index('ai_validity');
            $table->index('ai_risk_score');
            $table->index('ai_learned_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropIndex(['ai_decision']);
            $table->dropIndex(['ai_validity']);
            $table->dropIndex(['ai_risk_score']);
            $table->dropIndex(['ai_learned_at']);
            
            $table->dropColumn([
                'ai_risk_score',
                'ai_validity',
                'ai_warnings',
                'ai_summary',
                'ai_decision',
                'ai_notified_at',
                'ai_decided_at',
                'ai_learned_at',
                'ai_was_correct'
            ]);
        });
    }
};

