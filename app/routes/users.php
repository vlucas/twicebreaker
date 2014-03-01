<?php
use Entity\User;

$app->resource('users', function($request) {
    $this->get(function($request) {
        return $this->response()->redirect('/');
    });

    // Create new record
    $this->post(function($request) {
        $v = new \Valitron\Validator($request->post());
        $v->rule('required', ['name', 'phone_number']);
        if($v->validate()) {
            // Create new user
            $user = $this['mapper']->upsert('Entity\User', [
                'name'         => $request->post('name'),
                'phone_number' => $request->post('phone_number')
            ], [
                'phone_number' => $request->post('phone_number')
            ]);
            if($user) {
                // Use id, number, and tagcode to store in session
                $_SESSION['user'] = json_encode([
                    'id'           => $user->id,
                    'phone_number' => $user->phone_number,
                    'tagcode'      => $user->tagcode
                ]);
            }

            $this->format('json', function($request) use($user) {
                return $this->response(201, $post->toArray());
            });
            $this->format('html', function($request) use($user) {
                $return_url = $request->post('return_url');
                return $this->response()->redirect($return_url ?: '/');
            });
        } else {
            $this->format('json', function($request) use($v) {
                return $this->response(400, ['errors' => $v->errors()]);
            });
            $this->format('html', function($request) use($v) {
                return $this->template('users/new', ['errors' => $v->errors()]);
            });
        }
    });
});
