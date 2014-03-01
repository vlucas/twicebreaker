<?php
namespace Twicebreaker\Tests;
use Bullet\Request;

class AppTest extends \Twicebreaker\TestBase
{
    public function testIndexShouldRedirectToEvents()
    {
        $request = new Request('GET', 'index.json');
        $response = self::$app->run($request);
        $this->assertSame(302, $response->status());
    }
}

