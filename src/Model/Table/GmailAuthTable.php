<?php

declare(strict_types=1);

namespace GmailEmailSend\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * GmailAuth Model
 *
 * @method \GmailEmailSend\Model\Entity\GmailAuth newEmptyEntity()
 * @method \GmailEmailSend\Model\Entity\GmailAuth newEntity(array $data, array $options = [])
 * @method array<\GmailEmailSend\Model\Entity\GmailAuth> newEntities(array $data, array $options = [])
 * @method \GmailEmailSend\Model\Entity\GmailAuth get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \GmailEmailSend\Model\Entity\GmailAuth findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \GmailEmailSend\Model\Entity\GmailAuth patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\GmailEmailSend\Model\Entity\GmailAuth> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \GmailEmailSend\Model\Entity\GmailAuth|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \GmailEmailSend\Model\Entity\GmailAuth saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\GmailEmailSend\Model\Entity\GmailAuth>|\Cake\Datasource\ResultSetInterface<\GmailEmailSend\Model\Entity\GmailAuth>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\GmailEmailSend\Model\Entity\GmailAuth>|\Cake\Datasource\ResultSetInterface<\GmailEmailSend\Model\Entity\GmailAuth> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\GmailEmailSend\Model\Entity\GmailAuth>|\Cake\Datasource\ResultSetInterface<\GmailEmailSend\Model\Entity\GmailAuth>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\GmailEmailSend\Model\Entity\GmailAuth>|\Cake\Datasource\ResultSetInterface<\GmailEmailSend\Model\Entity\GmailAuth> deleteManyOrFail(iterable $entities, array $options = [])
 */
class GmailAuthTable extends Table
{
    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('gmail_auth');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->notEmptyString('credentials');

        $validator
            ->email('email');

        return $validator;
    }


    public function validationClientSecret(Validator $validator): Validator
    {
        $clientIdValidator = new Validator();

        $clientIdValidator->notEmptyString("client_id")
            ->notEmptyString("project_id")
            ->notEmptyString("auth_uri")
            ->notEmptyString("token_uri")
            ->notEmptyString("auth_provider_x509_cert_url")
            ->notEmptyString("client_secret")
            ->notEmptyArray("redirect_uris");

        $validator->addNested('web', $clientIdValidator);

        return $validator;
    }


    public function buildRules(RulesChecker $rules): RulesChecker
    {

        $rules->add($rules->isUnique(['email'], 'Email must be unique'));

        return $rules;
    }
}
