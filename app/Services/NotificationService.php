<?php

namespace App\Services;

use App\Models\NotificationLog;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    public function __construct(
        protected SmsService $smsService
    ) {}

    public function notify(User $user, string $subject, string $body): void
    {
        $this->sendEmail($user, $subject, $body);
        $this->sendSms($user, $subject, $body);
    }

    protected function sendEmail(User $user, string $subject, string $body): void
    {
        try {
            Mail::raw($body, function ($message) use ($user, $subject) {
                $message->to($user->email, $user->name)->subject($subject);
            });

            $this->log($user, 'email', $subject, $body, 'sent');
        } catch (\Throwable $e) {
            Log::error('Email notification failed', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            $this->log($user, 'email', $subject, $body, 'failed');
        }
    }

    protected function sendSms(User $user, string $subject, string $body): void
    {
        try {
            $this->smsService->send($user->phone, "{$subject}: {$body}");
            $this->log($user, 'sms', $subject, $body, 'sent');
        } catch (\Throwable $e) {
            Log::error('SMS notification failed', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            $this->log($user, 'sms', $subject, $body, 'failed');
        }
    }

    protected function log(User $user, string $channel, string $subject, string $body, string $status): void
    {
        NotificationLog::create([
            'user_id' => $user->id,
            'channel' => $channel,
            'subject' => $subject,
            'body' => $body,
            'status' => $status,
            'sent_at' => now(),
        ]);
    }
}
