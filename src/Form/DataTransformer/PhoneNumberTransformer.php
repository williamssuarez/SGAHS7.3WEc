<?php

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class PhoneNumberTransformer implements DataTransformerInterface
{

    /**
     * Transforms a phone string (0424-0401290) to an array for the form.
     * @param string|null $value A full phone number string.
     * @return array
     */
    public function transform($value): array
    {
        if (null === $value) {
            return ['prefix' => '0412','number' => null,];
        }

        // Split the stored string '0424-0401290' into parts
        $parts = explode('-', $value, 2);

        if (count($parts) !== 2) {
            throw new TransformationFailedException(sprintf(
                'The phone number "%s" is not in the expected format (XXXX-XXXXXXX).',
                $value
            ));
        }

        return ['prefix' => $parts[0], 'number' => $parts[1]];
    }

    /**
     * Transforms the array from the form back to a phone string (0424-0401290).
     * @param array $value
     * @return string
     */
    public function reverseTransform($value): string
    {
        if (null === $value || !isset($value['prefix']) || !isset($value['number'])) {
            // Handle case where required fields are missing
            return '';
        }

        $prefix = $value['prefix'];
        $number = $value['number'];

        // Combine and return the string for the entity
        return sprintf('%s-%s', $prefix, $number);
    }
}
