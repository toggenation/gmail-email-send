<?php

/**
 * @var \App\View\AppView $this
 */
?>
<div class="gmailAuth index content">
    <?= $this->Html->link(__('New Gmail Auth'), ['action' => 'getToken'], ['class' => 'button float-right']) ?>
    <h3><?= __('Gmail Auth') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('id') ?></th>
                    <th><?= $this->Paginator->sort('email') ?></th>
                    <th><?= $this->Paginator->sort('description') ?></th>
                    <th><?= $this->Paginator->sort('modified') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($authRecords as $gmailAuth) : ?>
                    <tr>
                        <td><?= $this->Number->format($gmailAuth->id) ?></td>
                        <td><?= h($gmailAuth->email) ?></td>
                        <td><?= h($gmailAuth->description) ?></td>
                        <td><?= h($gmailAuth->modified) ?></td>
                        <td class="actions">
                            <?= $this->Html->link(__('View'), ['action' => 'view', $gmailAuth->id]) ?>
                            <?= $this->Html->link(__('Edit'), [
                                'controller' => 'Auth', 'action' => 'changeCredentials', $gmailAuth->id,
                            ]) ?>
                            <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $gmailAuth->id], ['confirm' => __('Are you sure you want to delete # {0}?', $gmailAuth->id)]) ?>
                            <?= $this->Html->link(__('Test'), ['action' => 'test', $gmailAuth->id]) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="paginator">
        <ul class="pagination">
            <?= $this->Paginator->first('<< ' . __('first')) ?>
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
            <?= $this->Paginator->last(__('last') . ' >>') ?>
        </ul>
        <p><?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?></p>
    </div>
</div>