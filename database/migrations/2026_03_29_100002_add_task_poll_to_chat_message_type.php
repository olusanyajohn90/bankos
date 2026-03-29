<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Drop the old CHECK constraint and add new one with task/poll/voice
        DB::statement('ALTER TABLE chat_messages DROP CONSTRAINT IF EXISTS chat_messages_type_check');
        DB::statement("ALTER TABLE chat_messages ADD CONSTRAINT chat_messages_type_check CHECK (type::text = ANY (ARRAY['text','file','image','system','task','poll','voice']::text[]))");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE chat_messages DROP CONSTRAINT IF EXISTS chat_messages_type_check');
        DB::statement("ALTER TABLE chat_messages ADD CONSTRAINT chat_messages_type_check CHECK (type::text = ANY (ARRAY['text','file','image','system']::text[]))");
    }
};
