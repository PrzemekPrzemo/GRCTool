<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificate_inventory', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique();
            $table->string('common_name');
            $table->json('san')->nullable();           // Subject Alternative Names
            $table->string('issuer')->nullable();
            $table->string('cert_type', 32)->default('TLS'); // TLS, Code_Signing, Internal_CA, Client_Auth, S_MIME
            $table->string('environment', 32)->default('production'); // production, staging, dev, internal
            $table->string('fingerprint_sha256', 64)->nullable();
            $table->string('serial_number')->nullable();
            $table->date('issued_at')->nullable();
            $table->date('expires_at');
            $table->boolean('auto_renew')->default(false);
            $table->unsignedSmallInteger('renewal_days_before')->default(30);
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('managed_by')->nullable();  // e.g. "Let's Encrypt", "DigiCert", "Internal PKI"
            $table->foreignId('asset_id')->nullable()->constrained('assets')->nullOnDelete();
            $table->string('status', 32)->default('active'); // active, expired, revoked, replaced
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('crypto_keys', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique();
            $table->string('name');
            $table->string('key_type', 32);            // AES, RSA, EC, HMAC, EdDSA
            $table->string('algorithm')->nullable();   // e.g. AES-256-GCM, RSA-2048, ECDSA-P256
            $table->unsignedSmallInteger('key_size')->nullable();
            $table->string('storage_location', 64);    // HSM, KMS, Vault, filesystem, TPM
            $table->string('key_id')->nullable();      // external reference
            $table->unsignedSmallInteger('rotation_days')->default(365);
            $table->date('last_rotated_at')->nullable();
            $table->date('next_rotation_due')->nullable();
            $table->string('purpose')->nullable();     // encryption, signing, authentication, wrapping
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crypto_keys');
        Schema::dropIfExists('certificate_inventory');
    }
};
