<?php
// Set admin with proper key
app()->path('admin', function($request) {
    $this->get(function($request) {
        $this->format('html', function($request) {
            return $this->template('admin/index');
        });
    });

    // Super basic level security... this is just a simple 5-minute game.
    // What could possibly go wrong?
    // @see http://imgur.com/lOxDyDT
    $this->post(function($request) {
        if($request->post('password') === getenv('ADMIN_PASSWORD')) {
            $_SESSION['user_is_admin'] = true;
            $this['user_is_admin'] = true;
        } else {
            return 401;
        }
        return $this->response()->redirect('/');
    });

    // Logout
    $this->path('logout', function($request) {
        $this->get(function($request) {
            $_SESSION['user_is_admin'] = false;
            return $this->response()->redirect('/');
        });
    });
});
