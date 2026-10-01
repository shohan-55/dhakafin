<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('legal_name')->nullable();
            $table->string('tin')->nullable()->index();
            $table->string('bin')->nullable()->index();
            $table->string('entity_type')->nullable();
            $table->string('status')->default('active')->index();
            $table->string('timezone')->default('Asia/Dhaka');
            $table->timestamps();
        });

        Schema::create('organization_user', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role')->default('member')->index();
            $table->string('status')->default('active')->index();
            $table->timestamps();
            $table->unique(['organization_id','user_id']);
        });

        Schema::create('compliance_obligations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('type')->index();
            $table->string('title');
            $table->string('period_key')->nullable()->index();
            $table->date('due_date')->index();
            $table->string('status')->default('open')->index();
            $table->foreignId('responsible_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->string('evidence_reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['organization_id','type','period_key'], 'org_obligation_period_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compliance_obligations');
        Schema::dropIfExists('organization_user');
        Schema::dropIfExists('organizations');
    }
};
