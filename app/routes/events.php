<?php
use Entity\Event;
use Joelvardy\Flash as Flash;

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
        if(!$this['user_is_admin']) {
            return 401;
        }

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
                return $this->template('events/view', ['event' => $event, 'errors' => Flash::message('error')]);
            });
        });

        // UPDATE
        $this->post(function($request) use($event) {
            if(!$this['user_is_admin']) {
                return 401;
            }

            // Currently only allows starting of event timer
            $event->started_at = new \DateTime();
            $event->ended_at = new \DateTime($event->duration);
            $this['mapper']->save($event);
            return $this->response()->redirect('/events/' . $event->id);
        });

        // Join Event
        $this->path('join', function($request) use($event) {
            // Join form
            $this->get(function($request) use($event) {
                $this->format('html', function($request) use($event) {
                    return $this->template('events/join', compact(['event']));
                });
            });

            // Join Event
            $this->post(function($request) use($event) {
                $user = $this['mapper']->first('Entity\User', ['phone_number' => $request->post('phone_number')]);
                if(!$user) {
                    return $this->response()->redirect('/events/' . $event->id . '/join');
                }

                // Update 'current_event_id' for user
                $this['user']->current_event_id = $event->id;
                $this['mapper']->save($this['user']);

                $this->format('json', function() use($event) {
                    return $this->response(201, $event->toArray());
                });
                $this->format('html', function() use($event) {
                    return $this->response()->redirect('/events/' . $event->id);
                });
            });
        });

        // Tag user at event
        $this->path('taguser', function($request) use($event) {
            $this->post(function($request) use($event) {
                // Create new membership for user and event
                $event = $this['mapper']->create('Entity\Event\Tagging', [
                    'user_id'  => $this['user']->id,
                    'event_id' => $event->id,
                    'tagcode'  => $tagcode
                ]);

                $this->format('json', function() use($event) {
                    return $this->response(201, $event->toArray());
                });
                $this->format('html', function() use($event) {
                    return $this->response()->redirect('/events/' . $event->id);
                });
            });
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
