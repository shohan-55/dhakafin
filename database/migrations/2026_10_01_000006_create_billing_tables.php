<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('plan_code')->index();
            $table->string('status')->default('active')->index();
            $table->date('starts_on');
            $table->date('ends_on')->nullable();
            $table->json('entitlements')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('invoices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('number');
            $table->date('issue_date')->index();
            $table->date('due_date')->nullable()->index();
            $table->string('status')->default('draft')->index();
            $table->string('currency',3)->default('BDT');
            $table->unsignedBigInteger('subtotal_minor')->default(0);
            $table->unsignedBigInteger('tax_minor')->default(0);
            $table->unsignedBigInteger('total_minor')->default(0);
            $table->unsignedBigInteger('paid_minor')->default(0);
            $table->unsignedBigInteger('balance_minor')->default(0);
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->unique(['organization_id','number']);
        });

        Schema::create('invoice_lines', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('description');
            $table->unsignedBigInteger('quantity_milli')->default(1000);
            $table->unsignedBigInteger('unit_amount_minor');
            $table->unsignedBigInteger('line_total_minor');
            $table->unsignedBigInteger('tax_minor')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('idempotency_key')->nullable();
            $table->date('payment_date')->index();
            $table->unsignedBigInteger('amount_minor');
            $table->unsignedBigInteger('allocated_amount_minor')->default(0);
            $table->unsignedBigInteger('unapplied_amount_minor')->default(0);
            $table->string('method')->index();
            $table->string('reference')->nullable()->index();
            $table->string('status')->default('confirmed')->index();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->unique(['organization_id','idempotency_key']);
        });

        Schema::create('payment_allocations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('amount_minor');
            $table->timestamps();
            $table->unique(['payment_id','invoice_id']);
        });

        Schema::create('credit_notes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->string('number');
            $table->date('issue_date')->index();
            $table->unsignedBigInteger('amount_minor');
            $table->string('status')->default('issued')->index();
            $table->text('reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->unique(['organization_id','number']);
        });

        Schema::create('refunds', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
            $table->date('refund_date')->index();
            $table->unsignedBigInteger('amount_minor');
            $table->string('reference')->nullable()->index();
            $table->string('status')->default('recorded')->index();
            $table->text('reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refunds');
        Schema::dropIfExists('credit_notes');
        Schema::dropIfExists('payment_allocations');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoice_lines');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('subscriptions');
    }
};
