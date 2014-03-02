<?php
$app = app();
$user = $app['user'];
?>
<h2><?= $event->title ?></h2>
<?= $event->description ?>
<p>&nbsp;</p>

<?php if(!$event->hasStarted()): ?>
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
  <div class="well well-lg">
    <?php if($user->current_event_id === $event->id): ?>
      <p><strong>You have joined this event!</strong></p>
      <!-- Probably display leaderboard here -->
    <?php else: ?>
      <p>This event currently has <?= $participantCount; ?> participants.</p>
      <p>You are not currently participating in this event.</p>
      <a class="btn btn-default" href="/events/<?= $event->id ?>/join">Join Event</a>
    <?php endif; ?>
  </div>
<?php else: ?>
  <?php if($event->hasEnded()): ?>
    <div class="well well-lg bigstat">
      Event has ended!
      <?php if($app['user_is_admin']): ?>
      <form role="form" action="/events/<?= $event->id ?>" method="post">
        <input type="hidden" name="status" value="start" />
        <button type="submit" class="btn btn-default">Restart Timer (<?= $event->duration; ?>)</button>
      </form>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <?php if(!$event->hasEnded()): ?>
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
    <script>
      var refreshTimeout = 10000;
      function refresh() {
         window.location.reload(true);
      }
      setTimeout(refresh, refreshTimeout);
    </script>
  <?php endif; ?>

  <div class="panel panel-default">
    <div class="panel-heading">
      <h3 class="panel-title">Leaderboard (<?= $participantCount; ?> participants)</h3>
    </div>
    <div class="panel-body">
      <table class="table table-striped" id="leaderboard">
        <thead>
        <tr>
          <th>User</th>
          <th>Taggings</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach($leaderboard as $user): ?>
          <tr>
            <td><?= $user->name; ?></td>
            <td><?= $user->tagcount; ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>

