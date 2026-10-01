<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('engagements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('service_type')->index();
            $table->string('title');
            $table->string('status')->default('active')->index();
            $table->string('stage')->default('requested')->index();
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable()->index();
            $table->foreignId('lead_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('client_contact_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->unique(['organization_id','code']);
        });

        Schema::create('documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('engagement_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('original_name');
            $table->string('disk')->default('local');
            $table->text('path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->string('checksum_sha256', 64)->index();
            $table->string('status')->default('active')->index();
            $table->string('sensitivity')->default('confidential')->index();
            $table->string('review_status')->default('pending')->index();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('document_reviews', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reviewer_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('outcome')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_reviews');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('engagements');
    }
};
