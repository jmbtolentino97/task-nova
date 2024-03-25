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
        Schema::table("users", function (Blueprint $table) {
            $table->string("first_name")->after("email");
            $table->string("last_name")->after("first_name");
            $table->dropColumn(["name"]);
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("users", function (Blueprint $table) {
            $table->dropColumn([
                "first_name",
                "last_name"
            ]);
            $table->string("name")->after("email");
            $table->dropSoftDeletes();
        });
    }
};
