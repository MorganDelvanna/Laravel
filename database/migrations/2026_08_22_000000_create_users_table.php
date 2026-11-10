<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('UserName')->unique();
                $table->string('Password');
                $table->string('Role')->default('user');
                $table->timestamps();
            });
        }
        if (!Schema::hasTable('news')) {
            Schema::create('news', function (Blueprint $table) {
                $table->id();
                $table->date('publish_date');
                $table->string('header');
                $table->text('description');
                $table->boolean('archived')->default(false);
                $table->timestamps();
            });
        }
        if (!Schema::hasTable('calendar_events')) {
            Schema:: create('calendar_events', function(Blueprint $table) {
                $table->id();
                $table->dateTime('start');
                $table->dateTime('end');
                $table->string('title');
                $table->text('description')->nullable();
                $table->boolean('allDay')->default(false);
                $table->timestamps();
            });
        }
        if (!Schema::hasTable('applicants')) {
            Schema::create('applicants', function (Blueprint $table) {
                $table->id();
                $table->string('uuid');
                $table->string('type')->nullable();
                $table->text('data');
                $table->dateTime('created_at');
                $table->boolean('emailed')->default(false);
                $table->dateTime('emailed_at')->nullable();
                $table->dateTime('canceled_at')->nullable();
                $table->string('transaction_id')->nullable();
                $table->string('email')->nullable();
                $table->boolean('processed')->default(false);
            });
        }
        if (!Schema::hasTable('blacklist')) {
            Schema::create('blacklist', function (Blueprint $table) {
                $table->id();
                $table->string('email', 191)->unique();
            });
        }
        if (!Schema::hasTable('paidup')) {
            Schema::create('paidup', function (Blueprint $table) {
            $table->integer('Card');
            $table->integer('YearPaid');
            $table->timestamps();

            // Add unique constraint to prevent duplicate Card/YearPaid combinations
            $table->unique(['Card', 'YearPaid']);
        });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('news');
        Schema::dropIfExists('calendar_events');
        Schema::dropIfExists('applicants');
        Schema::dropIfExists('blacklist');
        Schema::dropIfExists('paidup');
    }
};
