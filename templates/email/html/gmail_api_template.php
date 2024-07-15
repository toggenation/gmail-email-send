<?php

/**
 * @var \App\View\AppView $this
 */
?>
<h1>Templated example</h1>
<?= $one; ?><br>
<?= $two; ?>

<p>Inline image</p>
<p style="border-radius: 6px; background-color: #d33c43; padding: 10px;">
    <?= $this->Html->tag('img', null, ['src' => 'cid:' . $contentId]);?>
</p>