<form role="form" action="/users" method="post">
  <div class="form-group">
    <label for="inputName">Name</label>
    <input type="text" class="form-control" id="inputName" name="name" placeholder="Full Name" value="<?= app()->request()->name; ?>">
  </div>
  <div class="form-group">
    <label for="inputPhone">Phone Number</label>
    <input type="tel" class="form-control" id="inputPhone" name="phone_number" placeholder="555-555-1212" value="<?= app()->request()->phone_number; ?>">
  </div>
  <input type="hidden" name="return_url" value="<?= isset($return_url) ? $return_url : app()->request()->return_url; ?>" />
  <button type="submit" class="btn btn-default">Join</button>
</form>

