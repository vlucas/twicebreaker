<?php
// Options from root URL (should expose all available user choices)
app()->path(array('/', 'index'), function($request) {
    $this->get(function($request) {
        return $this->response()->redirect('/events');
    });
});

