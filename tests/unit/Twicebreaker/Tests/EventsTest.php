<?php
namespace Twicebreaker\Tests;
use Bullet\Request;

class EventsTest extends \Twicebreaker\TestBase
{
    public function testEventsShouldReturnHttpStatus200()
    {
        $request = new Request('GET', 'events.json');
        $response = self::$app->run($request);
        $this->assertSame(200, $response->status());
    }

    public function testShouldTagUserAtEvent()
    {
        $request = new Request('POST', 'events/1/taguser', ['tagcode' => 'ABC']);
        $response = self::$app->run($request);
        $this->assertSame(302, $response->status()); // Redirects to user
    }

    public function testDoubleTaggingDoesNotError()
    {
        $request = new Request('POST', 'events/1/taguser', ['tagcode' => 'ABC']);
        $response = self::$app->run($request);
        $this->assertSame(302, $response->status());
    }

    public function testBadEventTaggingCodeReturnsError()
    {
        $request = new Request('POST', 'events/1/taguser', ['tagcode' => 'F16']);
        $response = self::$app->run($request);
        $this->assertSame(400, $response->status());
    }

    public function testNonExistentEventReturns404()
    {
        $request = new Request('GET', 'events/182');
        $response = self::$app->run($request);
        $this->assertSame(404, $response->status());
    }

    public function testNonExistentRouteReturns404()
    {
        $request = new Request('GET', 'this_does_not_exists.json');
        $response = self::$app->run($request);
        $this->assertSame(404, $response->status());
    }
}

