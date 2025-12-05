<?php

namespace App\Services;

use App\Models\Inquiry;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class InquiryEmailService
{
    /**
     * Send the appropriate mission trip email to the applicant.
     *
     * @throws Throwable
     */
    public function send(Inquiry $inquiry): void
    {
        $email = $inquiry->email;
        $name = $inquiry->name;
        $htmlContent = $this->getEmailContent($inquiry, $name);
        $textContent = $this->getEmailTextContent($inquiry, $name);

        try {
            Mail::send([], [], function ($message) use ($email, $name, $inquiry, $htmlContent, $textContent) {
                $message->to($email, $name)
                    ->subject($this->getEmailSubject($inquiry->status))
                    ->html($htmlContent)
                    ->text($textContent);
            });

            Log::info('Inquiry email sent via admin panel', [
                'inquiry_id' => $inquiry->id,
                'email' => $email,
                'status' => $inquiry->status,
            ]);
        } catch (Throwable $e) {
            Log::error('Failed to send inquiry email via admin panel', [
                'inquiry_id' => $inquiry->id,
                'email' => $inquiry->email,
                'exception' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    private function getEmailSubject(string $status): string
    {
        return match ($status) {
            'green' => 'Congratulations! Your Mission Trip Application Has Been Approved',
            'yellow' => 'Thank You for Your Mission Trip Application',
            'red' => 'Thank You for Your Interest in Our Mission Trip',
            default => 'Mission Trip Application Received',
        };
    }

    private function getEmailContent(Inquiry $inquiry, string $name): string
    {
        $status = $inquiry->status;

        $message = match ($status) {
            'green' => 'Congratulations! Your application has been approved. You can now sign up for the trip and pay your deposit.',
            'yellow' => 'Thank you for your application! A member of our team will reach out to you soon to discuss next steps and answer any questions you may have.',
            'red' => 'Thank you for your interest in our mission trip. While we\'re not able to move forward with this particular opportunity at this time, we\'d love to stay connected and explore other ways you can be involved in our mission work. Our team will be in touch soon.',
            default => 'Thank you for your application. We will review it and get back to you soon.',
        };

        $signupLink = ($status === 'green' && config('missions.signup_url'))
            ? '<p style="margin: 20px 0;"><a href="' . config('missions.signup_url') . '" style="background-color: #22c55e; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block;">Reserve Your Trip</a></p>'
            : '';

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <h2 style='color: #22c55e;'>Hello {$name},</h2>
            <p>{$message}</p>
            {$signupLink}
            <p style='margin-top: 30px; font-size: 12px; color: #666;'>
                If you have any questions, please don't hesitate to contact us at stm@adventures.org
            </p>
        </body>
        </html>
        ";
    }

    private function getEmailTextContent(Inquiry $inquiry, string $name): string
    {
        $status = $inquiry->status;

        $message = match ($status) {
            'green' => 'Congratulations! Your application has been approved. You can now sign up for the trip and pay your deposit.',
            'yellow' => 'Thank you for your application! A member of our team will reach out to you soon to discuss next steps and answer any questions you may have.',
            'red' => 'Thank you for your interest in our mission trip. While we\'re not able to move forward with this particular opportunity at this time, we\'d love to stay connected and explore other ways you can be involved in our mission work. Our team will be in touch soon.',
            default => 'Thank you for your application. We will review it and get back to you soon.',
        };

        $signupLink = ($status === 'green' && config('missions.signup_url'))
            ? "\n\nReserve your trip: " . config('missions.signup_url')
            : '';

        return "Hello {$name},\n\n{$message}{$signupLink}\n\nIf you have any questions, please contact us at stm@adventures.org";
    }
}


