<?php
namespace Twicebreaker\Tests;
use Bullet\Request;

class SmyesTest extends \Twicebreaker\TestBase
{
    public function testShouldReturnErrorForUnknownUser()
    {
        $request = new Request('POST', '/smyes', [
            'MessageSid' => '123abc',
            'AccountSid' => getenv('TWILIO_SID'),
            'From' => '+15555550000',         // Unknown user
            'To' => getenv('TWILIO_NUMBER'),
            'Body' => 'TEST FAIL'
        ], ['Accept' => 'application/xml']);
        $response = self::$app->run($request);
        $this->assertSame(400, $response->status());
        $this->assertContains('you are not registered', $response->content());
    }

    public function testShouldReturnErrorForInvalidTagcode()
    {
        $request = new Request('POST', '/smyes', [
            'MessageSid' => '123abc',
            'AccountSid' => getenv('TWILIO_SID'),
            'From' => '+15555551212',         // Chester Tester
            'To' => getenv('TWILIO_NUMBER'),  // Your Twilio Number
            'Body' => 'NOT'                   // Invalid tagcode
        ], ['Accept' => 'application/xml']);
        $response = self::$app->run($request);
        $this->assertSame(400, $response->status());
        $this->assertContains('Unknown tagcode', $response->content());
    }

    public function testShouldErrorWithNotJoinedEventYet()
    {
        // Login as user
        $request = new Request('POST', 'users', [
            'name' => 'Vance Lucas',
            'phone_number' => '555-555-1212'
        ]);
        $response = self::$app->run($request);
        $this->assertSame(302, $response->status()); // Redirects to root

        // Tag user
        $request = new Request('POST', '/smyes', [
            'MessageSid' => '123abc',
            'AccountSid' => getenv('TWILIO_SID'),
            'From' => '+15555551212',         // Chester Tester
            'To' => getenv('TWILIO_NUMBER'),  // Your Twilio Number
            'Body' => 'MCT'                   // Testy McTesterpants's tagcode
        ], ['Accept' => 'application/xml']);
        $response = self::$app->run($request);
        $this->assertSame(400, $response->status());
        $this->assertContains('have not joined an event', $response->content());
    }

    public function testShouldErrorWithEventNotStarted()
    {
        // Login as user
        $request = new Request('POST', 'users', [
            'name' => 'Vance Lucas',
            'phone_number' => '555-555-1212'
        ]);
        $response = self::$app->run($request);
        $this->assertSame(302, $response->status()); // Redirects to root

        // Join event
        $request = new Request('GET', 'events/1/join');
        $response = self::$app->run($request);
        $this->assertSame(302, $response->status()); // Redirects to /events/1

        // Tag user
        $request = new Request('POST', '/smyes', [
            'MessageSid' => '123abc',
            'AccountSid' => getenv('TWILIO_SID'),
            'From' => '+15555551212',         // Chester Tester
            'To' => getenv('TWILIO_NUMBER'),  // Your Twilio Number
            'Body' => 'MCT'                   // Testy McTesterpants's tagcode
        ], ['Accept' => 'application/xml']);
        $response = self::$app->run($request);
        $this->assertSame(400, $response->status());
        $this->assertContains('has not started yet', $response->content());
    }

    public function testShouldTagWithTwilioRequest()
    {
        // Login as user
        $request = new Request('POST', 'users', [
            'name' => 'Vance Lucas',
            'phone_number' => '555-555-1212'
        ]);
        $response = self::$app->run($request);
        $this->assertSame(302, $response->status()); // Redirects to root

        // Join event
        $request = new Request('GET', 'events/1/join');
        $response = self::$app->run($request);
        $this->assertSame(302, $response->status()); // Redirects to /events/1

        // Login as admin
        $request = new Request('POST', 'admin', ['password' => getenv('ADMIN_PASSWORD')]);
        $response = self::$app->run($request);
        $this->assertSame(302, $response->status()); // Redirects to root

        // Start Event
        $request = new Request('POST', 'events/1', [
            'status' => 'start',
        ]);
        $response = self::$app->run($request);
        $this->assertSame(302, $response->status()); // Redirects to /events/1

        // Tag user
        $request = new Request('POST', '/smyes', [
            'MessageSid' => '123abc',
            'AccountSid' => getenv('TWILIO_SID'),
            'From' => '+15555551212',         // Chester Tester
            'To' => getenv('TWILIO_NUMBER'),  // Your Twilio Number
            'Body' => 'MCT'                   // Testy McTesterpants's tagcode
        ], ['Accept' => 'application/xml']);
        $response = self::$app->run($request);
        $this->assertSame(201, $response->status());

        // Tag user again, expect error
        $request = new Request('POST', '/smyes', [
            'MessageSid' => '123abc',
            'AccountSid' => getenv('TWILIO_SID'),
            'From' => '+15555551212',         // Chester Tester
            'To' => getenv('TWILIO_NUMBER'),  // Your Twilio Number
            'Body' => 'MCT'                   // Testy McTesterpants's tagcode
        ], ['Accept' => 'application/xml']);
        $response = self::$app->run($request);
        $this->assertSame(400, $response->status());
        $this->assertContains('You already tagged', $response->content());
    }
}

