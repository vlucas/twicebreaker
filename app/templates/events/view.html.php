<h2><?= $event->title ?></h2>
<?= $event->description ?>

<?php if(!$event->started_at): ?>
<form role="form" action="/events/<?= $event->id ?>" method="post">
  <input type="hidden" name="status" value="start" />
  <button type="submit" class="btn btn-default">Start Timer (<?= $event->duration; ?>)</button>
</form>
<?php elseif($event->getSecondsLeft() === 0): ?>
<div class="well">
  Event has ended!
</div>
<form role="form" action="/events/<?= $event->id ?>" method="post">
  <input type="hidden" name="status" value="start" />
  <button type="submit" class="btn btn-default">Restart Timer (<?= $event->duration; ?>)</button>
</form>
<?php else: ?>
<div class="well well-lg">
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
