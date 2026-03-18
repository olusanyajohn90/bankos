<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestMailpit extends Command
{
    protected $signature = 'app:test-mailpit';
    protected $description = 'Send a test email to verify Mailpit is working';

    public function handle()
    {
        $this->info('Sending test email to Mailpit...');
        $this->info('Host: ' . config('mail.mailers.smtp.host'));
        $this->info('Port: ' . config('mail.mailers.smtp.port'));

        Mail::raw('This is a test email from bankOS. Mailpit is working!', function ($message) {
            $message->to('test@demomfb.com')
                    ->subject('Mailpit Test - ' . now()->format('H:i:s'));
        });

        $this->info('Email sent! Check Mailpit at http://localhost:8025');
    }
}
