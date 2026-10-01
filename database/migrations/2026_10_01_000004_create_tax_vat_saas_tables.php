<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compliance_rules', function (Blueprint $table): void {
            $table->id();
            $table->string('jurisdiction')->default('BD')->index();
            $table->string('code')->unique();
            $table->string('category')->index();
            $table->string('title');
            $table->string('frequency')->index();
            $table->json('rule_data');
            $table->date('effective_from')->nullable()->index();
            $table->date('effective_to')->nullable()->index();
            $table->text('source_url')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('published_at')->nullable()->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::table('compliance_obligations', function (Blueprint $table): void {
            $table->foreignId('compliance_rule_id')
                ->nullable()
                ->after('organization_id')
                ->constrained('compliance_rules')
                ->nullOnDelete();
            $table->json('metadata')->nullable()->after('notes');
        });

        Schema::create('withholding_transactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('kind')->index();
            $table->string('reference')->nullable()->index();
            $table->date('transaction_date')->index();
            $table->string('counterparty_name')->nullable();
            $table->string('counterparty_tin')->nullable()->index();
            $table->unsignedBigInteger('base_amount_minor');
            $table->unsignedInteger('rate_ppm');
            $table->unsignedBigInteger('withheld_amount_minor');
            $table->string('challan_reference')->nullable()->index();
            $table->date('challan_date')->nullable();
            $table->string('status')->default('draft')->index();
            $table->json('metadata')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('mushak_forms', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('form_type')->default('6.3')->index();
            $table->string('serial_no')->nullable()->index();
            $table->date('issue_date')->nullable()->index();
            $table->string('buyer_name')->nullable();
            $table->string('buyer_bin')->nullable()->index();
            $table->text('buyer_address')->nullable();
            $table->json('lines')->nullable();
            $table->unsignedBigInteger('total_value_minor')->default(0);
            $table->unsignedBigInteger('total_vat_minor')->default(0);
            $table->string('status')->default('draft')->index();
            $table->timestamp('finalized_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mushak_forms');
        Schema::dropIfExists('withholding_transactions');

        Schema::table('compliance_obligations', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('compliance_rule_id');
            $table->dropColumn('metadata');
        });

        Schema::dropIfExists('compliance_rules');
    }
};
