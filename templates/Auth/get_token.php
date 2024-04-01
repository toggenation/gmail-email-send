<?php

/**
 * @var \App\View\AppView $this
 */
?>

<?php echo $this->Form->create(null, ['type' => 'file']); ?>
<?php echo $this->Form->control('email', ['default' => 'jmcd1973@gmail.com']); ?>
<?php echo $this->Form->file('credentials', [
    'label' => 'Upload client_secret*.json'
]); ?>
<?php echo $this->Form->submit('Send'); ?>
<?php echo $this->Form->end(); ?>
