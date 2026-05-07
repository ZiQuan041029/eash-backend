<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visa_uploads', function (Blueprint $table) {
            $table->string('guest_email')->nullable()->after('guest_id');
            $table->timestamp('approved_at')->nullable()->after('status');
            $table->string('approval_token_hash')->nullable()->after('approved_at');
            $table->timestamp('approval_token_used_at')->nullable()->after('approval_token_hash');
        });

        Schema::table('visa_uploads', function (Blueprint $table) {
            $table->string('status')->default('pending')->change();
        });
    }

    public function down(): void
    {
        Schema::table('visa_uploads', function (Blueprint $table) {
            $table->dropColumn([
                'guest_email',
                'approved_at',
                'approval_token_hash',
                'approval_token_used_at',
            ]);
        });

        Schema::table('visa_uploads', function (Blueprint $table) {
            $table->string('status')->default('uploaded')->change();
        });
    }
};

