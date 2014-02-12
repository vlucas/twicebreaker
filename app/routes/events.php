<?php
use Entity\Event;

$app->resource('events', function($request) {
    // Index
    $this->get(function($request) {
        // All items
        $events = $this['mapper']->all('Entity\Event')
            ->order(['created_at' => 'DESC']);

        $this->format('json', function() use($events) {
            return $events->toArray();
        });
        $this->format('html', function() use($events) {
            return $this->template('events/index', compact('events'));
        });
    });

    // Create new record
    $this->post(function($request) {
        // if(!$this['user']->isLoggedIn()) {
        //     return 401;
        // }

        $v = new Valitron\Validator($request->post());
        $v->rule('required', ['title']);
        if($v->validate()) {
            // Create new item
            $event = $this['mapper']->create('Entity\Event', [
                'user_id' => $this['user']->id,
                'title' => $request->post('title'),
                'description' => $request->post('description'),
                'duration' => '5 minutes'
            ]);

            $this->format('json', function() use($event) {
                return $this->response(201, $event->toArray());
            });
            $this->format('html', function() use($event) {
                return $this->response()->redirect('/events/' . $event->id);
            });
        } else {
            $this->format('json', function() use($v) {
                return $this->response(400, ['errors' => $v->errors()]);
            });
            $this->format('html', function() use($v) {
                return $this->template('events/new', ['errors' => $v->errors()]);
            });
        }
    });

    $this->param('int', function($request, $id) {
        $event = $this['mapper']->get('Entity\Event', $id);
        if(!$event) {
            return 404;
        }

        // VIEW
        $this->get(function($request) use($event) {
            $this->format('html', function($request) use($event) {
                return $this->template('events/view', compact(['event']));
            });
        });

        // UPDATE
        $this->post(function($request) use($event) {
            // Currently only allows starting of event timer
            $event->started_at = new \DateTime();
            $event->ended_at = new \DateTime($event->duration);
            $this['mapper']->save($event);
            return $this->response()->redirect('/events/' . $event->id);
        });
    });

    // New Event Form
    $this->path('new', function($request) {
        $this->get(function($request) {
            $this->format('html', function($request) {
                return $this->template('events/new');
            });
        });
    });
});