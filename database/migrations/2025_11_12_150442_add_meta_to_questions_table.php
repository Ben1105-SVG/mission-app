<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->string('key')->nullable()->after('text')->index();
            $table->string('type')->nullable()->after('key'); // e.g. text, select, number, comfort
            $table->json('options')->nullable()->after('type'); // for select options
            $table->string('hint')->nullable()->after('options');
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn(['key', 'type', 'options', 'hint']);
        });
    }
};
