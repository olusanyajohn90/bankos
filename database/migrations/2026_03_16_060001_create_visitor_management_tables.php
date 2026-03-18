<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Visitor registry (one record per person, reused across visits)
        Schema::create('visitors', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('full_name');
            $table->string('id_type')->nullable();              // national_id, passport, drivers_license, voters_card
            $table->string('id_number')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('company')->nullable();
            $table->string('photo_path')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_blacklisted')->default(false);
            $table->text('blacklist_reason')->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'full_name']);
        });

        // Individual visit log (one record per visit)
        Schema::create('visitor_visits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('visitor_id')->index();
            $table->foreignId('host_user_id')->constrained('users');     // staff being visited
            $table->string('purpose');                                    // meeting, delivery, interview, banking, maintenance, etc.
            $table->string('badge_number')->nullable();                  // physical badge issued
            $table->string('vehicle_plate')->nullable();
            $table->string('items_brought')->nullable();                 // items visitor brought in
            $table->string('items_left')->nullable();                    // items left behind
            $table->uuid('branch_id')->nullable();
            $table->string('status')->default('checked_in');             // expected, checked_in, checked_out, no_show, denied
            $table->text('notes')->nullable();
            $table->text('denial_reason')->nullable();
            $table->timestamp('expected_at')->nullable();
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamp('checked_out_at')->nullable();
            $table->foreignId('checked_in_by')->nullable()->constrained('users');
            $table->foreignId('checked_out_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->index(['tenant_id', 'status']);
            $table->index(['checked_in_at']);
        });

        // Meeting rooms / locations
        Schema::create('visitor_meeting_rooms', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('name');                               // Boardroom, Conference A, MD Office, etc.
            $table->string('location')->nullable();              // floor, wing, building
            $table->unsignedTinyInteger('capacity')->default(2);
            $table->boolean('is_available')->default(true);
            $table->timestamps();
        });

        // Meeting bookings (scheduled meetings with visitors)
        Schema::create('visitor_meetings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('visit_id')->nullable()->index();       // linked to a visit when checked in
            $table->uuid('room_id')->nullable();
            $table->foreignId('organiser_id')->constrained('users');
            $table->string('title');
            $table->text('agenda')->nullable();
            $table->text('minutes')->nullable();
            $table->string('status')->default('scheduled');      // scheduled, in_progress, completed, cancelled
            $table->timestamp('scheduled_at');
            $table->unsignedSmallInteger('duration_minutes')->default(60);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'scheduled_at']);
        });

        // Meeting attendees (visitors + internal staff on a meeting)
        Schema::create('visitor_meeting_attendees', function (Blueprint $table) {
            $table->id();
            $table->uuid('meeting_id');
            $table->uuid('visitor_id')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->string('type')->default('visitor');          // visitor, staff
            $table->string('attendance_status')->default('invited'); // invited, confirmed, attended, absent
            $table->timestamps();
        });

        // Activity log (what visitors did during visit)
        Schema::create('visitor_activities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('visit_id')->index();
            $table->foreignId('logged_by')->constrained('users');
            $table->string('activity_type');                     // meeting, delivery, document_signed, area_access, etc.
            $table->text('description');
            $table->string('area_accessed')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();
        });

        // Watchlist / pre-approved visitors
        Schema::create('visitor_watchlist', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('visitor_id');
            $table->string('status');                            // blacklisted, vip, pre_approved
            $table->text('reason')->nullable();
            $table->foreignId('added_by')->constrained('users');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitor_watchlist');
        Schema::dropIfExists('visitor_activities');
        Schema::dropIfExists('visitor_meeting_attendees');
        Schema::dropIfExists('visitor_meetings');
        Schema::dropIfExists('visitor_meeting_rooms');
        Schema::dropIfExists('visitor_visits');
        Schema::dropIfExists('visitors');
    }
};
