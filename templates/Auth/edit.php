<?php

/**
 * @var \App\View\AppView $this
 */
?>
<h4>Change Credentials</h4>
<?php echo $this->Form->create($entity, ['type' => 'file']); ?>
<?php echo $this->Form->control('email'); ?>
<?php echo $this->Form->file('credentials', [
    'label' => 'Upload client_secret*.json'
]); ?>
<?php echo $this->Form->submit('Send'); ?>
<?php echo $this->Form->end(); ?>