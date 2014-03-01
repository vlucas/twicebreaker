<?php
$app = app();
$user = $app['user'];
?>
<h2><?= $event->title ?></h2>
<?= $event->description ?>
<p>&nbsp;</p>

<?php if(!$event->started_at): ?>
  <div class="well well-lg">
    This event has not started yet.
    <?php if($app['user_is_admin']): ?>
    <form role="form" action="/events/<?= $event->id ?>" method="post">
      <input type="hidden" name="status" value="start" />
      <button type="submit" class="btn btn-default">Start Timer (<?= $event->duration; ?>)</button>
    </form>
    <?php endif; ?>
  </div>

  <br />
  <?php if($user->current_event_id === $event->id): ?>
    <p><strong>You have joined this event!</strong></p>
    <!-- Probably display leaderboard here -->
  <?php else: ?>
    <form role="form" action="/events/<?= $event->id ?>/join" method="post">
    <input type="hidden" name="phone_number" value="<?= $user->phone_number; ?>" />
      <button type="submit" class="btn btn-default">Join Event</button>
    </form>
  <?php endif; ?>
<?php elseif($event->getSecondsLeft() === 0): ?>
  <div class="well well-lg bigstat">
    Event has ended!
    <?php if($app['user_is_admin']): ?>
    <form role="form" action="/events/<?= $event->id ?>" method="post">
      <input type="hidden" name="status" value="start" />
      <button type="submit" class="btn btn-default">Restart Timer (<?= $event->duration; ?>)</button>
    </form>
    <?php endif; ?>
  </div>
<?php else: ?>
<div class="well well-lg bigstat">
  <big>Time Remaining: <span id="countdownTimer"></span></big>
</div>
<script>
  // Yay Stackoverflow for a quick hacky solution!
  // @link http://stackoverflow.com/questions/1191865/code-for-a-simple-javascript-countdown-timer

  var elementId = 'countdownTimer';
  var mins = 5;  //Set the number of minutes you need
  var secs = mins * 60;
  var secs = <?= $event->getSecondsLeft() ?>;
  var currentSeconds = 0;
  var currentMinutes = 0;
  setTimeout('Countdown()',1000);

  function Countdown() {
    currentMinutes = Math.floor(secs / 60);
    currentSeconds = secs % 60;
    if(currentSeconds <= 9) currentSeconds = "0" + currentSeconds;
    secs--;
    document.getElementById(elementId).innerHTML = currentMinutes + ":" + currentSeconds; //Set the element id you need the time put into.
    if(secs !== -1) setTimeout('Countdown()',1000);
  }
</script>
<?php endif; ?>
