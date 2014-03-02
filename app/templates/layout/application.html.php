<?php
$app = app();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Twicebreaker</title>
  <link href="<?php echo $app->url('/assets/styles/bootstrap.min.css'); ?>" rel="stylesheet" type="text/css" />
  <link href="<?php echo $app->url('/assets/styles/application.css'); ?>" rel="stylesheet" type="text/css" />
</head>
<body>
  <div class="container">

    <!-- Header -->
    <div class="navbar navbar-default navbar-fixed-top" role="navigation">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="/">Twicebreaker</a>
        </div>
        <div class="collapse navbar-collapse">
          <ul class="nav navbar-nav">
            <li class="active"><a href="/">Home</a></li>
            <li><a href="/events">Events</a></li>
          </ul>
          <?php if($app['user']->isLoggedIn()): ?>
          <ul class="nav navbar-nav navbar-right">
          <li><a href="#"><?= $app['user']->name; ?></a></li>
          </ul>
          <?php endif; ?>
        </div><!--/.nav-collapse -->
      </div>
    </div>

    <!-- Content -->
    <div class="row">
      <div class="span3 alpha omega">
        <div id="sidebar">
          <ul id="nav_sidebar">
          <li><a href="<?php echo $app->url('/'); ?>">Dashboard</a></li>
          <li><a href="<?php echo $app->url('/posts'); ?>">Blog</a></li>
        <ul>
        </div>
      </div>

      <div id="content" class="bBox">
        <?php
        $flashMessages = Joelvardy\Flash::message('flash');
        if(!empty($flashMessages)):
        ?>
        <div class="alert alert-success">
          <?php foreach($flashMessages as $fieldMessages): ?>
            <li><?php echo implode("</li>\n</li>", $fieldMessages); ?></li>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if(!empty($errors)): ?>
        <div class="alert alert-danger">
          <?php foreach($errors as $field => $fieldErrors): ?>
            <li><?php echo implode("</li>\n</li>", $fieldErrors); ?></li>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php echo $yield; ?>
      </div>
    </div>

    <!-- Footer -->
    <div class="row">
      <div class="span12">
        <div id="footer">
          <br />
          <p>Built by <a href="http://vancelucas.com/">Vance Lucas</a> | Powered by <a href="http://twilio.com/">Twilio</a></p>
        </div>
      </div>
    </div>
  </div>

  <!-- JavaScripts -->
  <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js"></script>
</body>
</html>
