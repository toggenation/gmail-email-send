<?php

declare(strict_types=1);

namespace GmailEmailSend\Model\Entity;

use Cake\ORM\Entity;

/**
 * GmailAuth Entity
 *
 * @property int $id
 * @property string|resource|null $credentials
 * @property string|null $email
 * @property string|resource|null $token
 */
class GmailAuth extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'credentials' => true,
        'email' => true,
        'token' => true,
    ];

    /**
     * Fields that are excluded from JSON versions of the entity.
     *
     * @var list<string>
     */
    protected array $_hidden = [
        'token',
        'credentials'
    ];
}
