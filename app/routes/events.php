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
            $data = [
                'event'            => $event,
                'leaderboard'      => $event->getLeaderboardStats(),
                'participantCount' => $event->participants->count(),
                'errors'           => Flash::message('error')
            ];

            $this->format('html', function($request) use($data) {
                return $this->template('events/view', $data);
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
                $user = $this['user'];
                if($user->isLoggedIn()) {
                    // Update 'current_event_id' for user
                    $user->current_event_id = $event->id;
                    $this['mapper']->save($user);

                    // Send text with user tagcode
                    if(BULLET_ENV !== 'testing') {
                        $message = $this['twilio']->account->messages->sendMessage(
                            $request->env('TWILIO_NUMBER'), // From a valid Twilio number
                            $user->phone_number, // Text this number
                            "You joined the event '" . $event->title . "'. \nYour tagcode is: " . $user->tagcode
                        );
                    }

                    Flash::message('flash', 'Joined event ' . $event->title);
                    return $this->response()->redirect('/events/' . $event->id);
                }

                $this->format('html', function($request) use($event) {
                    return $this->template('events/join', compact(['event']));
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
