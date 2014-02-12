<?php
use Entity\User;

$app->resource('users', function($request) {
    $this->get(function($request) {
        return $this->response()->redirect('/');
    });

    // Create new record
    $this->post(function($request) {
        $v = new Valdator($request->post());
        $v->rule('required', ['name', 'phone_number']);
        if($v->validate()) {
            // Create new user
            $user = $this['mapper']->create('Entity\User', [
                'name'         => $request->post('name'),
                'phone_number' => $request->post('phone_number')
            ]);

            $this->format('json', function() use($user) {
                return $this->response(201, $post->toArray());
            });
            $this->format('html', function() use($user) {
                return $this->response()->redirect('/taggings/' . $user->id);
            });
        } else {
            $this->format('json', function() use($v) {
                return $this->response(400, ['errors' => $v->errors()]);
            });
            $this->format('html', function() use($v) {
                return $this->template('posts/new', ['errors' => $v->errors()]);
            });
        }
    });
});
