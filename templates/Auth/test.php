<?php

?>

<?= $this->Form->control('from', [
    'value' => $from->email,
    'readonly' => 'readonly',
    'disabled' => 'disabled',
]); ?>

<?= $this->Form->create(); ?>
<?= $this->Form->control('to'); ?>
<?= $this->Form->submit(); ?>
<?= $this->Form->end();
