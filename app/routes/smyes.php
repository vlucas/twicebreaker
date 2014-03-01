<?php
use Entity\Event;

$app->resource('smyes', function($request) {
    // Receive Twilio Request from SMS that is received
    // @see https://www.twilio.com/docs/api/twiml/sms/twilio_request
    $this->post(function($request) {
        // Only XML responses are supported (via TwiML)
        $this->format('xml', function($request) {
            Bullet\View\Template::config(['auto_layout' => false]);

            /**
             *
             * 1. Receive Twilio request: https://www.twilio.com/docs/api/twiml/sms/twilio_request
             * 2. Lookup user by phone number
             *    a) If User found, proceed to step 3
             *    b) If no user, return 400 (or return TwiML with URL directing user to signup?)
             * 3. Use "Body" to lookup tagged user by "tagcode"
             *    a) If tagged user found, proceed to step 4
             *    b) If no tagged user, return TwiML response with "No such user" or similar error
             * 4. Create tagging record from user to tagged user
             * 5. Return TwiML response with positive confirmation "You Rock!", "Gotcha!", "Tag! You're it!", etc.
             *
             */

            $v = new Valitron\Validator($request->post());
            $v->rule('required', ['MessageSid', 'AccountSid', 'From', 'To', 'Body']);
            if($v->validate()) {
                $user = $this['mapper']->first('Entity\User', ['phone_number' => $request->post('From')]);
                $tagcode = trim($request->post('Body'));
                $tagged_user = $this['mapper']->first('Entity\User', ['tagcode' => $tagcode]);

                // User not registered in system
                if(!$user) {
                    return $this->template('smyes/message', ['message' => 'Ahoy! Looks like you are not registered to play yet. Please visit '
                        . getenv('APP_URL') . ' to register.'])
                        ->format('xml')
                        ->status(400);
                }

                // Tagged user not found
                if(!$tagged_user) {
                    return $this->template('smyes/message', ['message' => 'ERROR: Unknown tagcode. You can\'t just make these things up, you know.'])
                        ->format('xml')
                        ->status(400);
                }

                // User has not joined an event
                if(!$user->current_event_id) {
                    return $this->template('smyes/message', ['message' => 'ERROR: You have not joined an event yet! Check out the events here: '
                        . getenv('APP_URL') . '/events'])
                        ->format('xml')
                        ->status(400);
                }

                // Load event by texting user's 'current_event_id'
                $event = $this['mapper']->get('Entity\Event', $user->current_event_id);

                // Ensure event has already started
                if(!$event->hasStarted()) {
                    return $this->template('smyes/message', ['message' => 'ERROR: Hold your horses! Event '. $event->title . ' has not started yet.'])
                        ->format('xml')
                        ->status(400);
                }

                // Ensure event has not already ended
                if($event->hasEnded()) {
                    return $this->template('smyes/message', ['message' => 'ERROR: Rejected! Sorry chap, event '. $event->title . ' is over.'])
                        ->format('xml')
                        ->status(400);
                }

                // Tagged user has not joined same event as user
                if(!$tagged_user->current_event_id || $user->current_event_id != $tagged_user->current_event_id) {
                    return $this->template('smyes/message', ['message' => 'ERROR: '. $tagged_user->name . ' is lame. They haven\'t joined this event yet.'])
                        ->format('xml')
                        ->status(400);
                }

                // Create new tagging
                $tagging = $this['mapper']->build('Entity\Event\Tagging', [
                    'user_id'        => $user->id,
                    'tagged_user_id' => $tagged_user->id,
                    'event_id'       => $user->current_event_id,
                    'tagcode'        => $tagcode
                ]);
                $result = $this['mapper']->insert($tagging);

                // Lame attempt at multiple taggings of the same user
                if(!$result) {
                    if(count($tagging->errors('user_event_tag') > 0)) {
                        return $this->template('smyes/message', ['message' => 'Get a life. You already tagged ' . $tagged_user->name . '.'])
                            ->format('xml')
                            ->status(400);
                    }
                }

                $msg = $this['sms']['tag_success_messages'][array_rand($this['sms']['tag_success_messages'])];
                return $this->template('smyes/message', ['message' => $msg])
                    ->format('xml')
                    ->status(201);
            } else {
              return $this->template('smyes/message', ['errors' => $v->errors(), 'message' => 'Bad Request'])
                  ->format('xml')
                  ->status(400);
            }
        });
    });
});
