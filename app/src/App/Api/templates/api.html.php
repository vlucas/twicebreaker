<?php
// Load form helper
$app = app();
$fh = $app->helper('form');
$dh = $app->helper('date');
$request = $app->request();

// Setup variables
$entities = $view->entities();
$hasEntities = count($entities) > 0;
$links = $view->links();
$actions = $view->actions();
$fields = $view->fields();
$errors = $view->errors();
$titleField = $view->titleField();
$isOnlyActions = $view->isOnlyActions();
$isItemView = $view->itemView() || (!$hasEntities && !$isOnlyActions);
$hideActionLinks = ($isItemView && $isOnlyActions); // Hide if 'itemView' and ONLY actions
$hasContent = $hasEntities || $isItemView; // Has content for 'content' tab
$tabTitle = $isItemView ? 'Details' : 'Items';
?>

<div class="panel panel-default">
  <div class="panel-body">
    <div class="panel-body-heading bordered">
      <h2 class="pb-title"><?php echo $title; ?></h2>
    </div>
    <ul class="nav nav-tabs" id="myTab">
      <?php if($hasContent): ?>
      <li class="active"><a href="#details" data-toggle="tab"><?php echo $tabTitle; ?></a></li>
      <?php endif; ?>
      <?php
      $ai = 0;
      foreach($actions as $rel => $action):
        ++$ai;
      ?>
        <li<?php echo ($ai === 1 && !$hasContent) ? ' class="active"' : ''; ?>><a href="#<?php echo $rel; ?>" data-toggle="tab"><?php echo $action['title']; ?></a></li>
      <?php endforeach; ?>
    </ul>
    <div class="tab-content">
      <?php if($hasContent): ?>
        <div class="tab-pane active" id="details">
          <?php if($isItemView): ?>
            <table class="table">
            <?php foreach($view->properties() as $key => $value):
              if(in_array($key, array('id', '_links'))) continue;
              if(strpos($key, '@') === 0) continue;

              // Use custom callback to display field
              if(isset($view->callbacks['field'][$key])) {
                  $value = call_user_func($view->callbacks['field'][$key], $value);
              }

              if($value instanceof \DateTime) {
                  $value = $dh->format($value);
              }
            ?>
              <tr>
                <th><?php echo ucwords(str_replace('_', ' ', $key)); ?></th>
                <td><?php echo $value; ?></td>
              </tr>
            <?php endforeach; ?>
            </table>
          <?php endif; ?>

          <?php
          if(count($entities) > 0):
          ?>
            <table class="table table-hover">
              <thead>
                <tr>
                <?php foreach($fields as $field): ?>
                  <th><?php echo ucwords(str_replace('_', ' ', $field)); ?></th>
                <?php endforeach; ?>
                <?php if(isset($data['entities'][0]['links']) && count($data['entities'][0]['links']) > 0): ?>
                  <th>Actions</th>
                <?php endif; ?>
                </tr>
              </thead>
              <tbody>
            <?php foreach($entities as $item):
              $links = false;
              if(isset($item['links'])) {
                $links = $view->formatLinks($item['links']);
              }
            ?>
                <tr>
                <?php
                // Sort properties by displayed fields and only include those keys
                $item['properties'] = array_merge(array_flip($fields), $item['properties']);
                foreach($item['properties'] as $key => $value):
                  if(!in_array($key, $fields)) continue;

                  // Use custom callback to display field
                  if(isset($view->callbacks['field'][$key])) {
                      $value = call_user_func($view->callbacks['field'][$key], $value);
                  }
                ?>
                <?php if($key === $titleField && $links !== false): ?>
                  <td><a href="<?php echo $links['self']['href']; ?>"><?php echo $value; ?></a></td>
                <?php else: ?>
                  <td><?php echo $value; ?></td>
                <?php endif; ?>
                <?php endforeach; ?>
                <?php
                // Print links as a button dropdown selection
                if(isset($item['links'])):
                ?>
                  <td class="api-item-actions">
                    <div class="btn-group">
                      <a class="btn btn-default btn-sm" href="<?php echo $links['self']['href']; ?>">View</a>
                      <a class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" href="#"><span class="caret"></span></a>
                      <ul class="dropdown-menu">
                      <?php
                      unset($links['self']); // Remove 'self' (printed above)

                      // Edit and Delete links
                      $editLink = false;
                      $deleteLink = false;
                      if(isset($links['edit'])) {
                        $editLink = $links['edit'];
                        unset($links['edit']);
                      }
                      if(isset($links['delete'])) {
                        $deleteLink = $links['delete'];
                        unset($links['delete']);
                      }

                      foreach($links as $link):
                      ?>
                        <li><a href="<?php echo $link['href']; ?>" class="btn-small"><?php echo $link['title']; ?></a></li>
                      <?php endforeach; ?>
                      <li class="divider"></li>
                      <?php if($editLink): ?>
                        <li><a href="<?php echo $editLink['href']; ?>" class="btn-small"><?php echo $editLink['title']; ?></a></li>
                      <?php endif; ?>
                      <?php if($deleteLink): ?>
                        <li><a href="<?php echo $deleteLink['href']; ?>" class="btn-small"><?php echo $deleteLink['title']; ?></a></li>
                      <?php endif; ?>
                      </ul>
                    </div>
                  </td>
                <?php endif; ?>
                </tr>
            <?php endforeach; ?>
              </tbody>
            </table>
          <?php endif; ?>

      </div>
      <?php endif; ?>
        <?php
          $ai = 0;
          foreach($actions as $rel => $form):
            ++$ai;
        ?>
      <div class="tab-pane<?php echo ($ai === 1 && !$hasContent) ? ' active' : ''; ?>" id="<?php echo $rel; ?>">
        <?php echo $view->partial('api/form', compact('request', 'fh', 'form', 'errors'))->content(); ?>
      </div>
      <?php endforeach; ?>
    </div>
    <hr />

  </div>
</div>
