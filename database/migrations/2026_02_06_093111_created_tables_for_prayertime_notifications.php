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
        Schema::create('prayertime_noti_tokens', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->nullable();
            $table->string('fcm_token', 300);
            $table->string('timezone')->nullable();
            $table->string('user_lat')->nullable();
            $table->string('user_long')->nullable();
            $table->string('fajr')->nullable();
            $table->string('sunrise')->nullable();
            $table->string('dhuhr')->nullable();
            $table->string('sunset')->nullable();
            $table->string('maghrib')->nullable();
            $table->timestamp('prayer_updated_at')->nullable();
            $table->string('day_difference', 2)->nullable();
            $table->timestamps();
        });


        Schema::create('prayertime_noti_hijri_dates', function (Blueprint $table) {
            $table->id();
            $table->string('hijri_date');
            $table->string('hijri_day');
            $table->string('hijri_monthname');
            $table->integer('hijri_year');
            $table->integer('day_difference')->nullable();
            $table->timestamps();
        });

        Schema::create('prayertime_noti_messages', function (Blueprint $table) {
            $table->id();
            $table->string('frequency');
            $table->string('language')->nullable();
            $table->string('notification_type');
            $table->string('notification_title');
            $table->string('notification_message');
            $table->integer('minute')->nullable();
            $table->integer('hour')->nullable();
            $table->integer('day')->nullable();
            $table->string('week_day')->nullable();
            $table->string('month')->nullable();
            $table->integer('year')->nullable();
            $table->string('prayer_type')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prayertime_noti_tokens');
        Schema::dropIfExists('prayertime_noti_hijri_dates');
        Schema::dropIfExists('prayertime_noti_messages');
    }
};
