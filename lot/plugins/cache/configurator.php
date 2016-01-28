<p><?php echo $speak->plugin_cache_description_content . ' ' . Jot::info($speak->plugin_cache_info_content); ?></p>
<?php

$c = $config->states->{'plugin_' . md5(File::B(__DIR__))};
$content = "";
foreach($c->path as $path => $exp) {
    if($exp !== true) {
        $exp = ' ' . $exp;
    } else {
        $exp = "";
    }
    $content .= $path . $exp . "\n";
}

?>
<p><?php echo Form::textarea('content', trim($content), 'feed/rss', array('class' => array('textarea-block', 'textarea-expand'))); ?></p>
<p><?php echo Jot::button('action', $speak->update); ?></p>