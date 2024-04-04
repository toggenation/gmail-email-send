<?php
declare(strict_types=1);

namespace GmailEmailSend\Service\Traits;

use Cake\Utility\Text;

trait ErrorFormatterTrait
{
    /**
     * Provides error message from error array
     *
     * Given error array:
     * [
     *   'shipper' => [
     *     '_empty' => 'This field cannot be left empty',
     *   ],
     *   'destination' => [
     *     '_empty' => 'This field cannot be left empty',
     *   ],
     * ]
     *
     * Becomes:
     * "This field cannot be left empty (Shipper). This field cannot be left empty (Destination)."
     *
     * @param array  $validationErrors The Validation Errors from an entity
     * @return string
     */
    public function formatErrors(array $validationErrors = []): string
    {
        $message = [];

        foreach ($validationErrors as $field => $errors) {
            $error = Text::toList(array_values($errors));

            // $field = Inflector::humanize($field);

            $message[] = sprintf('%s (errorField: %s).', $error, $field);
        }

        return implode(' ', $message);
    }

    public function getEntitiesErrors($entities)
    {
        $errors = [];
        foreach ($entities as $entity) {
            $errors[] = $entity->getErrors();
        }

        return $errors;
    }
}
