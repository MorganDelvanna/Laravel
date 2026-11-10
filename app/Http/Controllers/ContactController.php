<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ContactController extends Controller
{
    private const RECIPIENTS = [
        'membership' => 'membership@pfga.ca',
        'treasurer' => 'treasurer@pfga.ca',
        'dawebguy' => 'dawebguy2@pfga.ca',
        'action' => 'action@pfga.ca',
        'archery' => 'archery@pfga.ca',
        'handgun' => 'handgun@pfga.ca',
        'rifle' => 'rifle@pfga.ca',
        'smallbore' => 'smallbore@pfga.ca',
        'juniors' => 'smallbore@pfga.ca',
    ];

    public function send(Request $request)
    {
        Log::info('Contact form submission started');

        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'from' => 'required|email|max:255',
                'email' => ['required', 'string', Rule::in(array_keys(self::RECIPIENTS))],
                'subject' => 'required|string|max:50',
                'message' => 'required|string|min:10'
            ]);
        } catch (ValidationException $e) {
            return redirect()->back()->with('error', $e->validator->errors()->first());
        }

        Log::info('Contact form validated successfully', [
            'department' => $validatedData['email'],
        ]);

        $subject = "Contact Form Submission: {$validatedData['subject']}";
        $body = "Name: {$validatedData['name']}\n";
        $body .= "From: {$validatedData['from']}\n";
        $body .= $validatedData['message'];

        $recipient = self::RECIPIENTS[$validatedData['email']];

        try {
            Mail::send([], [], function ($message) use ($recipient, $subject, $validatedData, $body) {
                $message->from(config('mail.from.address'), config('mail.from.name'))
                    ->to($recipient)
                    ->subject($subject)
                    ->replyTo($validatedData['from'], $validatedData['name'])
                    ->text($body);
            });
            Log::info('Contact email sent successfully', [
                'department' => $validatedData['email'],
                'subject' => $validatedData['subject']
            ]);
        } catch (\Throwable $e) {
            Log::error('Contact form submission failed', [
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
            ]);
            return redirect()->back()->with('error', 'There was an error sending your message. Please try again.');
        }

        return redirect()->back()->with('success', 'Your message has been sent successfully.');
    }
}
