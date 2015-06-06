<?php echo $messages; ?>
<form class="form-kill form-cache" id="form-kill" action="<?php echo $config->url_current . $config->url_query; ?>" method="post">
  <?php if($files): ?>
  <ul>
    <?php foreach($files as $file): ?>
    <li><?php echo CACHE . DS . File::path(Text::parse($file, '->decoded_url')); ?></li>
    <?php endforeach; ?>
  </ul>
  <?php endif; ?>
  <p>
  <?php echo Jot::button('action', $speak->yes); ?>
  <?php echo Jot::btn('reject', $speak->no, $config->manager->slug . '/cache'); ?>
  </p>
  <?php echo Form::hidden('token', $token); ?>
</form>