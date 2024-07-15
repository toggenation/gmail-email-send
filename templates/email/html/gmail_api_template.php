<?php 

/**
 * @var App\View\AppView $this
 */
?>
<h1>Templated example</h1>
<?= $one; ?><br>
<?= $two; ?>

<p style="border-radius: 6px; background-color: blue; padding: 10px;">
    <?= $this->Html->tag('img', null, ['src' => 'cid:' . $contentId]);?>
</p>