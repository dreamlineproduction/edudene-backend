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
        Schema::table('tutors', function (Blueprint $table) {
            $table->boolean('enable_one_to_one')->after('is_house')->default(false);
			$table->boolean('enable_trainer')->after('enable_one_to_one')->default(false);
			$table->boolean('enable_courses')->after('enable_trainer')->default(false);
			$table->decimal('one_to_one_hourly_rate', 8, 2)->after('enable_courses')->nullable();
			$table->decimal('trainer_hourly_rate', 8, 2)->after('one_to_one_hourly_rate')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tutors', function (Blueprint $table) {

            $table->dropColumn([
                'enable_one_to_one',
                'enable_trainer',
                'enable_courses',
                'one_to_one_hourly_rate',
                'trainer_hourly_rate',
            ]);

        });
    }
};
