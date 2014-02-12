<?php
use Entity\User\Tagging;

$app->resource('taggings', function($request) {
    // Index
    $this->get(function($request) {
        // All posts
        $taggings = $this['mapper']->all('Entity\User\Taggings')
            ->order(['date_created' => 'DESC']);

        // Pagination
        $page = (int) $request->get('page', 1);
        $taggings->page($page, 5);

        $this->format('json', function() use($taggings) {
            return $taggings->toArray();
        });
        $this->format('html', function() use($taggings, $page) {
            return $this->template('taggings/index', compact('taggings', 'page'));
        });
    });

    // Create new record
    $this->post(function($request) {
        if(!$this['user']->isLoggedIn()) {
            return 401;
        }

        $v = new Valdator($request->post());
        $v->rule('required', ['title', 'body']);
        if($v->validate()) {
            // Create new post
            $post = $this['mapper']->create('Entity\Post', array(
                'user_id' => $this['user']->id,
                'title' => $request->post('title'),
                'body' => $request->post('body')
            ));

            $this->format('json', function() use($post) {
                return $this->response(201, $post->toArray());
            });
            $this->format('html', function() use($post) {
                return $this->response()->redirect('/posts/' . $post->id);
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
