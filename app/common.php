<?php
// Setup defaults...
date_default_timezone_set('UTC');
error_reporting(-1); // Display ALL errors
ini_set('display_errors', '1');
ini_set("session.cookie_httponly", '1'); // Mitigate XSS javascript cookie attacks for browers that support it
ini_set("session.use_only_cookies", '1'); // Don't allow session_id in URLs

// Production setting switch
if(BULLET_ENV == 'production') {
    // Hide errors in production
    error_reporting(0);
    ini_set('display_errors', '0');
}

// Throw Exceptions for everything so we can see the errors
function exception_error_handler($errno, $errstr, $errfile, $errline ) {
    // Don't catch suppressed errors with '@' sign
    // @link http://stackoverflow.com/questions/7380782/error-supression-operator-and-set-error-handler
    $error_reporting = ini_get('error_reporting');
    if (!($error_reporting & $errno)) {
        return;
    }
    throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
}
set_error_handler("exception_error_handler");

// Start user session
if(session_status() !== PHP_SESSION_ACTIVE && !headers_sent()) {
    session_start();
}

// Share 'mapper' instance
$app['mapper'] = function($app) use($request) {
    $cfg = new \Spot\Config();
    $cfg->addConnection('mysql', str_replace('mysql2', 'mysql', $request->env('DATABASE_URL')));
    return new \Spot\Mapper($cfg);
};

// Share 'user' instance
$app['user'] = function($app) {
    $user = false;
    if(isset($_SESSION['user'])) {
        $user = $app['mapper']->first('Entity\User', [
            'id'           => isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : 0,
            'phone_number' => isset($_SESSION['user']['phone_number']) ? $_SESSION['user']['phone_number'] : 0,
            'tagcode'      => isset($_SESSION['user']['tagcode']) ? $_SESSION['user']['tagcode'] : 0,
        ]);
    }

    // Always ensure a user object is returned
    if(!$user) {
        $user = new Entity\User();
    }
    return $user;
};
// User admin flag
// @see app/routes/admin.php
$app['user_is_admin'] = isset($_SESSION['user_is_admin']) ? $_SESSION['user_is_admin'] : false;

// Share 'twilio' instance
$app['twilio'] = function($app) use($request) {
    return new Services_Twilio($request->env('TWILIO_SID'), $request->env('TWILIO_AUTH_TOKEN'));
};

// Register helpers
$app->helper('api', 'App\Helper\Api');
$app->helper('date', 'App\Helper\Date');
$app->helper('form', 'App\Helper\Form');

// Shortcut to access $app instance anywhere
$GLOBALS['app'] = $app;
function app() {
    return $GLOBALS['app'];
}

// Add 'page' method to Spot\Query for pagination
\Spot\Query::addMethod('page', function(\Spot\Query $query, $page = 1, $records = 20) {
    if(!$page) {
        $page = 1;
    }
    return $query->limit($records)->offset(((int) $page - 1) * $records);
});

// Display exceptions with error and 500 status
$app->on('Exception', function(\Bullet\Request $request, \Bullet\Response $response, \Exception $e) use($app) {
    if($request->format() === 'json' || $request->isCli()) {
        $data = array(
            'error' => str_replace('Exception', '', get_class($e)),
            'message' => $e->getMessage()
        );

        // Debugging info for development ENV
        if(BULLET_ENV !== 'production') {
            $data['file'] = $e->getFile();
            $data['line'] = $e->getLine();
            $data['trace'] = explode("\n", $e->getTraceAsString());
        }

        if($request->isCli()) {
            $response->content(print_r($data, true));
        } else {
            $response->content($data);
        }
    } else {
        $response->content($app->template('errors/exception', array('e' => $e))->content());
    }

    if(BULLET_ENV === 'production') {
        // An error happened in production. You should really let yourself know about it.
        // TODO: Email, log to file, or send to error-logging service like Sentry, Airbrake, etc.
    }
});

// Custom 404 Error Page
$app->on(404, function(\Bullet\Request $request, \Bullet\Response $response) use($app) {
  if($request->format() === 'json' || $request->isCli()) {
        $data = array(
            'error' => 404,
            'message' => 'Not Found'
        );
        $response->contentType('application/json');
        $response->content(json_encode($data));
    } else {
        $response->content($app->template('errors/404')->content());
    }
});

// Phone number format
function phoneNumberFormat($phone) {
    $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
    $parsed = $phoneUtil->parse($phone, "US");
    $phoneNumber = $phoneUtil->format($parsed, \libphonenumber\PhoneNumberFormat::E164);
    return $phoneNumber;
}

// Super-simple language translation by key => value array
function t($string) {
    static $lang = null;
    static $langs = array();
    if($lang === null) {
        $lang = app()->request()->get('lang', 'en');
        if(!preg_match("/^[a-z]{2}$/", $lang)) {
            throw new \Exception("Language must be a-z and only two characters");
        }
    }
    if(!isset($langs[$lang])) {
        $langFile = __DIR__ . '/lang/' . $lang . '.php';
        if(!file_exists($langFile)) {
            throw new \Exception("Language '$lang' not supported. Sorry :(");
        }
        $langs[$lang] = require($langFile);
    }

    if(isset($langs[$lang][$string])) {
        return $langs[$lang][$string];
    }
    return $string;
}

