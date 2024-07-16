<?php

use Cake\Utility\Inflector;

?>

<dl>
    <?php foreach ($params as $key => $value) : ?>
        <dt><?= Inflector::humanize($key); ?></dt>
        <dd><?= h($value); ?></dd>
    <?php endforeach; ?>
</dl>