<h2>Events</h2>

<?php if(count($events) === 0): ?>
  <p>No events. Make one, maybe?</p>
<?php endif; ?>

<?php if(app()['user_is_admin']): ?>
  <p><a href="/events/new" class="btn btn-primary" role="button">New Event</a></p>
<?php endif; ?>

<?php foreach($events as $event): ?>

  <h2><a href="<?= app()->url('/events/' . $event->id); ?>"><?= $event->title ?></a></h2>
  <?= $event->description ?>

<?php endforeach; ?>

