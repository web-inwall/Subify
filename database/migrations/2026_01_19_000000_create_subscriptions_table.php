<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('plan_key')->index();
            $table->string('status')->index();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();
            $table->jsonb('features_snapshot');
            $table->integer('price'); // Stored in cents
            $table->char('currency', 3);
            $table->timestamps();
        });

        DB::statement('CREATE INDEX subscriptions_features_snapshot_gin_index ON subscriptions USING gin (features_snapshot)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
