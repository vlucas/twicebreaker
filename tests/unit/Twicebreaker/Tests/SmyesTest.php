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
        $this->assertContains('You are not registered', $response->content());
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

    public function testShouldTagWithTwilioRequest()
    {
        // Join event
        $request = new Request('POST', 'events/1/join', ['tagcode' => 'ABC']);
        $response = self::$app->run($request);
        $this->assertSame(302, $response->status()); // Redirects to user

        // Tag user
        $request = new Request('POST', '/smyes', [
            'MessageSid' => '123abc',
            'AccountSid' => getenv('TWILIO_SID'),
            'From' => '+15555551212',         // Chester Tester
            'To' => getenv('TWILIO_NUMBER'),  // Your Twilio Number
            'Body' => 'MCT'                   // Testy McTesterpants's tagcode
        ], ['Accept' => 'application/xml']);
        $response = self::$app->run($request);
        var_dump($response->content());
        $this->assertSame(200, $response->status());
        $this->assertContains(self::$app['sms']['tag_success_messages'], $response->content());
    }
}

