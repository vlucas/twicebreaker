<?php
$app = app();
$form = $app->helper('form');
$request = $app->request();
?>

<h2>New Event</h2>
<form role="form" action="/events" method="post">
  <div class="form-group">
    <label for="inputName">Title</label>
    <?= $form->text('title', $request->title, ['class' => 'form-control', 'id' => 'inputTitle', 'placeholder' => '']); ?>
  </div>
  <div class="form-group">
    <label for="inputDesc">Description</label>
    <?= $form->textarea('description', $request->description, ['class' => 'form-control', 'id' => 'inputDesc', 'placeholder' => '']); ?>
  </div>
  <button type="submit" class="btn btn-default">Add Event</button>
</form>

