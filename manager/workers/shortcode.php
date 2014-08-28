<form class="form-shortcode" action="<?php echo $config->url_current; ?>" method="post">
  <?php echo $messages; ?>
  <input name="token" type="hidden" value="<?php echo $token; ?>">
  <table class="table-bordered table-full">
    <thead>
      <tr>
        <th><?php echo $speak->key; ?></th>
        <th><?php echo $speak->value; ?></th>
      </tr>
    </thead>
    <tbody>
      <?php if($files): ?>
      <?php foreach($files as $key => $value): ?>
      <tr>
        <td><input name="keys[]" type="text" class="input-block" value="<?php echo Text::parse($key)->to_encoded_html; ?>" placeholder="{{<?php echo strtolower($speak->key); ?>}}"></td>
        <td><input name="values[]" type="text" class="input-block" value="<?php echo Text::parse($value)->to_encoded_html; ?>"></td>
      </tr>
      <?php endforeach; ?>
      <?php endif; ?>
      <tr>
        <td><input name="keys[]" type="text" class="input-block" placeholder="{{<?php echo strtolower($speak->key); ?>}}"></td>
        <td><input name="values[]" type="text" class="input-block"></td>
      </tr>
      <tr class="row-more-less" data-min="3" data-max="9999">
        <td colspan="2"><a class="btn btn-sm btn-default btn-increase" href="#add"><i class="fa fa-plus-circle"></i> <?php echo $speak->more; ?></a> <a class="btn btn-sm btn-default btn-decrease" href="#remove"><i class="fa fa-minus-circle"></i> <?php echo $speak->less; ?></a></td>
      </tr>
    </tbody>
  </table>
  <p><button class="btn btn-action" type="submit"><i class="fa fa-check-circle"></i> <?php echo $speak->update; ?></button></p>
</form>
<hr>
<?php echo Config::speak('file:shortcode'); ?>