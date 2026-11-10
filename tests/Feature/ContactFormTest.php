<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Mail;
use App\Http\Middleware\VerifyCsrfToken;
use Mockery;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    public function test_contact_form_accepts_a_department_and_sends_to_its_address(): void
    {
        Mail::shouldReceive('send')
            ->once()
            ->with([], [], Mockery::on(function ($callback): bool {
                $message = Mockery::mock();
                $message->shouldReceive('from')
                    ->once()
                    ->with(config('mail.from.address'), config('mail.from.name'))
                    ->andReturnSelf();
                $message->shouldReceive('to')
                    ->once()
                    ->with('membership@pfga.ca')
                    ->andReturnSelf();
                $message->shouldReceive('subject')
                    ->once()
                    ->with('Contact Form Submission: Membership question')
                    ->andReturnSelf();
                $message->shouldReceive('replyTo')
                    ->once()
                    ->with('member@example.com', 'Test Member')
                    ->andReturnSelf();
                $message->shouldReceive('text')
                    ->once()
                    ->with("Name: Test Member\nFrom: member@example.com\nI have a membership question.")
                    ->andReturnSelf();

                $callback($message);

                return true;
            }));

        $response = $this->from('/contact')->post(route('contact.send'), [
            'name' => 'Test Member',
            'from' => 'member@example.com',
            'email' => 'membership',
            'subject' => 'Membership question',
            'message' => 'I have a membership question.',
        ]);

        $response->assertRedirect('/contact');
        $response->assertSessionHas('success', 'Your message has been sent successfully.');
    }

    public function test_contact_form_displays_validation_errors_for_an_unknown_department(): void
    {
        $response = $this->from('/contact')->post(route('contact.send'), [
            'name' => 'Test Member',
            'from' => 'member@example.com',
            'email' => 'not-a-department@example.com',
            'subject' => 'Membership question',
            'message' => 'I have a membership question.',
        ]);

        $response->assertRedirect('/contact');
        $response->assertSessionHas('error', 'The selected email is invalid.');
        $this->get('/contact')->assertSee('The selected email is invalid.');
    }
}
