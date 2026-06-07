<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── daily_habits: enriquecer ───────────────────────────────
        Schema::table('daily_habits', function (Blueprint $table) {
            if (!Schema::hasColumn('daily_habits', 'type')) {
                $table->string('type', 20)->default('boolean')->after('color'); // boolean|quantity|duration
            }
            if (!Schema::hasColumn('daily_habits', 'target_value')) {
                $table->decimal('target_value', 10, 2)->nullable()->after('type');
            }
            if (!Schema::hasColumn('daily_habits', 'unit')) {
                $table->string('unit', 30)->nullable()->after('target_value');
            }
            if (!Schema::hasColumn('daily_habits', 'frequency_type')) {
                $table->string('frequency_type', 20)->default('daily')->after('goal_frequency'); // daily|weekly|specific_days|times_per_week
            }
            if (!Schema::hasColumn('daily_habits', 'frequency_days')) {
                $table->json('frequency_days')->nullable()->after('frequency_type');
            }
            if (!Schema::hasColumn('daily_habits', 'start_date')) {
                $table->date('start_date')->nullable()->after('frequency_days');
            }
            if (!Schema::hasColumn('daily_habits', 'end_date')) {
                $table->date('end_date')->nullable()->after('start_date');
            }
            if (!Schema::hasColumn('daily_habits', 'is_archived')) {
                $table->boolean('is_archived')->default(false)->after('is_active');
            }
        });

        // ── goals: tipo de meta ────────────────────────────────────
        Schema::table('goals', function (Blueprint $table) {
            if (!Schema::hasColumn('goals', 'tipo_meta')) {
                $table->string('tipo_meta', 20)->default('checklist')->after('prioridade'); // checklist|numeric|financeira|habito
            }
        });

        // ── goal_milestones ────────────────────────────────────────
        if (!Schema::hasTable('goal_milestones')) {
            Schema::create('goal_milestones', function (Blueprint $table) {
                $table->id();
                $table->foreignId('goal_id')->constrained('goals')->cascadeOnDelete();
                $table->string('titulo');
                $table->decimal('valor_alvo', 12, 2)->nullable();
                $table->date('data_alvo')->nullable();
                $table->timestamp('atingido_em')->nullable();
                $table->unsignedInteger('order')->default(0);
                $table->timestamps();
                $table->index(['goal_id', 'order']);
            });
        }

        // ── goal_reminders ─────────────────────────────────────────
        if (!Schema::hasTable('goal_reminders')) {
            Schema::create('goal_reminders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('goal_id')->constrained('goals')->cascadeOnDelete();
                $table->dateTime('remind_at');
                $table->string('canal', 20)->default('database'); // database|mail|webpush
                $table->timestamp('enviado_em')->nullable();
                $table->timestamps();
                $table->index(['goal_id', 'remind_at']);
                $table->index('enviado_em');
            });
        }

        // ── user_levels (gamificação) ──────────────────────────────
        if (!Schema::hasTable('user_levels')) {
            Schema::create('user_levels', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
                $table->unsignedBigInteger('xp')->default(0);
                $table->unsignedInteger('level')->default(1);
                $table->unsignedInteger('current_streak')->default(0);
                $table->unsignedInteger('best_streak')->default(0);
                $table->timestamp('last_activity_date')->nullable();
                $table->timestamps();
            });
        }

        // ── xp_logs (auditoria de XP, opcional mas útil) ──────────
        if (!Schema::hasTable('xp_logs')) {
            Schema::create('xp_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->integer('amount');
                $table->string('reason', 60);
                $table->string('source_type')->nullable();
                $table->unsignedBigInteger('source_id')->nullable();
                $table->timestamps();
                $table->index(['user_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('xp_logs');
        Schema::dropIfExists('user_levels');
        Schema::dropIfExists('goal_reminders');
        Schema::dropIfExists('goal_milestones');

        Schema::table('goals', function (Blueprint $table) {
            if (Schema::hasColumn('goals', 'tipo_meta')) $table->dropColumn('tipo_meta');
        });

        Schema::table('daily_habits', function (Blueprint $table) {
            foreach (['type','target_value','unit','frequency_type','frequency_days','start_date','end_date','is_archived'] as $col) {
                if (Schema::hasColumn('daily_habits', $col)) $table->dropColumn($col);
            }
        });
    }
};
