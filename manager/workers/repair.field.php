<?php echo $messages; ?>
<form class="form-<?php echo $the_key ? 'repair' : 'ignite'; ?> form-field" id="form-<?php echo $the_key ? 'repair' : 'ignite'; ?>" action="<?php echo $config->url_current; ?>" method="post">
  <?php echo Form::hidden('token', $token); ?>
  <label class="grid-group">
    <span class="grid span-1 form-label"><?php echo $speak->title; ?></span>
    <span class="grid span-5">
    <?php echo Form::text('title', Guardian::wayback('title', $file->title), null, array(
        'class' => 'input-block'
    )); ?>
    </span>
  </label>
  <label class="grid-group">
    <span class="grid span-1 form-label"><?php echo $speak->key; ?></span>
    <span class="grid span-5">
    <?php echo Form::text('key', Guardian::wayback('key', $the_key), null, array(
        'class' => 'input-block'
    )); ?>
    </span>
  </label>
  <label class="grid-group">
    <span class="grid span-1 form-label"><?php echo $speak->type; ?></span>
    <span class="grid span-5">
    <?php

    $cache = Guardian::wayback('type', $file->type);

    echo Form::select('type', array(
        't' => $speak->text,
        's' => $speak->summary,
        'b' => $speak->boolean,
        'o' => $speak->option,
        'f' => $speak->file
    ), $cache[0]);

    ?>
    </span>
  </label>
  <div class="grid-group">
    <span class="grid span-1 form-label"><?php echo $speak->scope; ?></span>
    <span class="grid span-5">
    <?php

    $file = (object) Converter::str($file);
    $cache = Guardian::wayback('scope', isset($file->scope) ? $file->scope : "");
    $scopes = array('article' => $speak->article, 'page' => $speak->page, 'comment' => $speak->comment);
    // Backward compatibility ...
    // "" is equal to `article,page`
    // '*' is equal to all scopes
    if($cache === "") $cache = array('article', 'page');
    if($cache === '*') $cache = array_keys($scopes);
    $cache = is_array($cache) ? $cache : explode(',', $cache);

    foreach($scopes as $k => $v) {
        echo '<div>' . Form::checkbox('scope[]', $k, in_array($k, $cache), $v) . '</div>';
    }

    ?>
    </span>
  </div>
  <label class="grid-group">
    <span class="grid span-1 form-label"><?php echo $speak->value; ?></span>
    <span class="grid span-5">
    <?php echo Form::textarea('value', Guardian::wayback('value', $file->value), null, array(
        'class' => 'input-block'
    )); ?>
    </span>
  </label>
  <label class="grid-group">
    <span class="grid span-1 form-label"><?php echo $speak->description; ?></span>
    <span class="grid span-5">
    <?php echo Form::text('description', Guardian::wayback('description', $file->description), Config::speak('manager.placeholder_description_', strtolower($speak->field)), array(
        'class' => 'input-block'
    )); ?>
    </span>
  </label>
  <div class="grid-group">
    <span class="grid span-1"></span>
    <span class="grid span-5">
      <?php if($the_key): ?>
      <?php echo Jot::button('action', $speak->update); ?>
      <?php echo Jot::btn('destruct', $speak->delete, $config->manager->slug . '/field/kill/key:' . $the_key); ?>
      <?php else: ?>
      <?php echo Jot::button('construct', $speak->create); ?>
      <?php endif; ?>
    </span>
  </div>
</form>
<hr>
<?php echo Config::speak('file:field'); ?>