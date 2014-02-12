<?php
// Options from root URL (should expose all available user choices)
app()->path(array('/', 'index'), function($request) {
    $this->get(function($request) {
        $data = array(
            'rel' => array('index'),
            'links' => array(
                array(
                    'rel' => array('taggings'),
                    'title' => t('Taggings'),
                    'href' => $this->url('/taggings')
                )
            )
        );

        $this->format('json', function() use($data) {
            return $data;
        });
        $this->format('html', function() use($data) {
            return $this->template('index', compact('data'));
        });
    });
});

