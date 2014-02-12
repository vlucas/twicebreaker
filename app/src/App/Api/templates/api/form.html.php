<?php
$dh = app()->helper('date');
$method = isset($form['method']) ? strtoupper($form['method']) : 'GET';
$formMethod = ($method == 'GET' || $method == 'POST') ? $method : 'POST';
?>
    <form class="form-horizontal" role="form" action="<?php echo $form['href']; ?>" method="<?php echo $formMethod; ?>" enctype="<?php echo isset($form['type']) ? $form['type'] : 'application/x-www-form-urlencoded'; ?>">
      <?php if($errors): ?>
        <div class="alert alert-danger">
          <strong>Whoops!</strong> There were some errors:
        </div>
      <?php endif; ?>

      <?php foreach($form['fields'] as $param):
        $paramKey = $param['name'];
        $paramTitle = ucwords(str_replace('_', ' ', $param['name']));
      ?>
        <div class="form-group<?php echo (isset($param['required']) && $param['required'] === true) ? ' required' : ''; echo isset($errors[$paramKey]) ? ' has-error' : ''?>">
          <label class="control-label col-md-2 col-lg-2" for="api-action-input-<?php echo $paramKey; ?>"><?php echo $paramTitle; ?></label>
          <div class="col-md-10 col-lg-10">
            <?php
            // Fill-in field data for editing record
            $fieldValue = isset($param['value']) ? $param['value'] : $request->get($paramKey, null);
            if($dh->isDate($fieldValue)) {
                $fieldValue = $dh->format($fieldValue);
            }

            // Field HTML attributes
            $attrs = array(
                'id' => 'api-action-input-' . $paramKey,
                'class' => 'form-control'
            );

            // Set errors as 'help' if any
            if(isset($errors[$paramKey])) {
                $param['help'] = (isset($param['help']) ? $param['help'] . '; ' : '') . implode('; ', $errors[$paramKey]);
            }

            // Adjust field depending on field type
            switch($param['type']) {
              case 'text':
              case 'editor':
                $attrs = array('rows' => 10) + $attrs;
                echo $fh->textarea($paramKey, $fieldValue, $attrs);
              break;

              case 'bool':
              case 'boolean':
                // Requires both hidden field and checkboxso value will be passed if not checked
                echo $fh->input('hidden', $paramKey, 0, array('id' => false) + $attrs) . "\n";
                echo $fh->checkbox($paramKey, (int) $fieldValue);
              break;

              case 'int':
              case 'integer':
                echo $fh->text($paramKey, $fieldValue, array('size' => 10) + $attrs);
              break;

              case 'string':
                echo $fh->text($paramKey, $fieldValue, array('size' => (isset($param['length']) ? $param['length'] : 40)) + $attrs);
              break;

              case 'select':
                if(isset($param['multiple']) && $param['multiple']) {
                    $attrs['multiple'] = 'multiple';
                }
                $options = isset($param['options']) ? $param['options'] : array();
                if(!empty($options)) {
                    $isAssoc = array_values($options) !== $options;
                    if(!$isAssoc) {
                        $options = array_combine($options, $options);
                    }
                }
                echo $fh->select($paramKey, $options, $fieldValue, $attrs);
              break;

              case 'password':
                echo $fh->input('password', $paramKey, $fieldValue, array('size' => 25) + $attrs);
              break;

              case 'date':
              case 'datetime':
                $class = $param['type'] == 'date' ? 'datepicker' : 'datetimepicker';
                $attrs = array('class' => $attrs['class'] . ' ' . $class) + $attrs;
                echo $fh->input('text', $paramKey, $fieldValue, $attrs);
              break;

              default:
                echo $fh->input($param['type'], $paramKey, $fieldValue, $attrs);
            }

            // Hidden '_method' for browser forms
            if($method !== $formMethod) {
                echo $fh->input('hidden', '_method', $method, ['id' => false]) . "\n";
            }
            ?>

            <?php if(isset($param['help'])): ?><span class="help-block"><?php echo $param['help']; ?></span><?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
        <div class="control-group">
          <div class="col-md-10 col-md-offset-2 col-lg-10 col-lg-offset-2">
            <button class="btn btn-success" type="submit">Submit</button>
          </div>
        </div>
    </form>
