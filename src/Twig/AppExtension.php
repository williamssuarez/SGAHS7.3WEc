<?php
// src/Twig/AppExtension.php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            // Registers a new filter named 'calculate_age' that calls the 'calculateAge' method
            new TwigFilter('calculate_age', $this->calculateAge(...)),
            new TwigFilter('time_ago', $this->getTimeAgo(...)),
        ];
    }

    /**
     * Calculates the age based on a given date of birth.
     *
     * @param \DateTimeInterface|null $dateOfBirth The date of birth (DateTimeInterface object).
     * @return string The calculated age or an error message.
     */
    public function calculateAge(?\DateTimeInterface $dateOfBirth): string
    {
        if (!$dateOfBirth) {
            return 'N/D'; // Not Defined or Not Available
        }

        try {
            // Get the current date
            $now = new \DateTimeImmutable();

            // Calculate the difference between now and the birthdate
            $interval = $now->diff($dateOfBirth);

            // Return the difference in years
            return $interval->y;

        } catch (\Exception $e) {
            return 'Error';
        }
    }

    /**
     * Calculates the human-readable time elapsed since the given date. (e.g., "Hace 2 días")
     *
     * @param \DateTimeInterface|null $dateTime The date to compare against the present.
     * @return string
     */
    public function getTimeAgo(?\DateTimeInterface $dateTime): string
    {
        if (!$dateTime) {
            return 'N/D';
        }

        $now = new \DateTimeImmutable();
        $interval = $now->diff($dateTime);

        $unit = '';
        $value = 0;

        // Check years
        if ($interval->y >= 1) {
            $value = $interval->y;
            $unit = ($value === 1) ? 'año' : 'años';
        }
        // Check months
        elseif ($interval->m >= 1) {
            $value = $interval->m;
            $unit = ($value === 1) ? 'mes' : 'meses';
        }
        // Check days
        elseif ($interval->d >= 1) {
            $value = $interval->d;
            $unit = ($value === 1) ? 'día' : 'días';
        }
        // Check hours
        elseif ($interval->h >= 1) {
            $value = $interval->h;
            $unit = ($value === 1) ? 'hora' : 'horas';
        }
        // Check minutes
        elseif ($interval->i >= 1) {
            $value = $interval->i;
            $unit = ($value === 1) ? 'minuto' : 'minutos';
        }
        // Check seconds (or just return 'just now')
        else {
            return 'Hace un momento';
        }

        // Return the final formatted string
        return sprintf('Hace %d %s', $value, $unit);
    }
}
