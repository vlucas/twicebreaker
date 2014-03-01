<h2>Join <?= $event->title; ?></h2>

<?php echo $view->partial('users/new', ['return_url' => '/events/' . $event->id]); ?>

