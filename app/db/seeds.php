<?php
if(!isset($app)) {
  throw new \RuntimeException("DB Seeds must be run within application context.");
}

$mapper = $app['mapper'];

// CREATE EVENTS
echo "Creating Events...\n";
$events = array(array(
    'title'       => 'Test Event One',
    'description' => 'This is just a test event, please ignore this. It\'s really only for testing purposes anyways.'
), array(
    'title'       => 'Test Event One',
    'description' => 'This is just a test event, please ignore this. It\'s really only for testing purposes anyways.'
));
foreach($events as $i => $data) {
    $event = new \Entity\Event($data);
    $created = $mapper->insert($event);
    if(!$created) {
      echo "Unable to insert event: " . $event->name;
      print_r($event->errors());
    }
}
echo "+ Events Created!\n";


echo "Creating Users...\n";
$users = array(array(
    'name'         => 'Chester Tester',
    'phone_number' => '555-555-1212',
    'tagcode'      => 'TES'
), array(
    'name'             => 'Testy McTesterpants',
    'phone_number'     => '555-555-1313',
    'tagcode'          => 'MCT',
    'current_event_id' => 1
));
foreach($users as $i => $data) {
    $user = new \Entity\User($data);
    $created = $mapper->insert($user);
    if(!$created) {
      echo "Unable to insert user: " . $user->name;
      print_r($user->errors());
    }
}
echo "+ Users Created!\n";

