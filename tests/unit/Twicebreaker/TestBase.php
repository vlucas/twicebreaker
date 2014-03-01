<?php
namespace Twicebreaker;
use Bullet\Request;

class TestBase extends \PHPUnit_Framework_TestCase
{
    protected static $app;

    public function setUp()
    {
        if(!self::$app) {
            // Require main index file to load everything
            require dirname(dirname(dirname(__DIR__))) . '/web/index.php';
            $GLOBALS['app'] = $app;
            self::$app = $app;

            // Reset database and seed it
            $response = self::$app->run(new Request('GET', 'db/reset'));
            $response = self::$app->run(new Request('GET', 'db/seed'));

            // Login test user
            $request = new Request('POST', 'login.json', ['username' => 'test', 'password' => 'test']);
            $response = self::$app->run($request);
        }
        return self::$app;
    }

    protected function assertArrayContains($needle, $haystack)
    {
        foreach ($needle as $key => $val) {
            $this->assertArrayHasKey($key, $haystack);

            if (is_array($val)) {
                $this->assertArrayContainsArray($val, $haystack[$key]);
            } else {
                $this->assertEquals($val, $haystack[$key]);
            }
        }
    }

    protected function assertArrayContainsTypes($path, $needle, $haystack)
    {
        if($path) {
            $pathParts = explode('.', $path);
            foreach($pathParts as $pathPart) {
                if($pathPart === '*') {
                    $this->assertInternalType('array', $haystack);
                    foreach($haystack as $item) {
                        $nextPath = current($pathParts);
                        $this->assertArrayContainsTypes($nextPath, $needle, $item);
                    }
                    return;
                }
                $this->assertArrayHasKey($pathPart, $haystack);
                $haystack = $haystack[$pathPart];
            }
        }

        foreach ($needle as $key => $val) {
            $this->assertArrayHasKey($key, $haystack);

            if (is_array($val)) {
                $this->assertArrayContainsArray($val, $haystack[$key]);
            } else {
                $result = strtolower(gettype($haystack[$key]));
                if(is_callable($val)) {
                    $val = call_user_func($val, $result);
                    if($val === false) {
                        $this->fail("Key '$key' is type $result, custom callback returned " . var_export($val, true));
                    }
                } else {
                    if($val !== gettype($haystack[$key])) {
                        $this->fail("Key '$key' is type $result, expected " . var_export($val, true));
                    } else {
                        $this->assertTrue(true);
                    }
                }
            }
        }
    }
}

