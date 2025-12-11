<?php
// src/Twig/AppExtension.php

namespace App\Twig;

use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function getFilters(): array
    {
        return [
            // Registers a new filter named 'calculate_age' that calls the 'calculateAge' method
            new TwigFilter('calculate_age', $this->calculateAge(...)),
            new TwigFilter('time_ago', $this->getTimeAgo(...)),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('is_active', $this->isActive(...)),
            new TwigFunction('is_tree_active', $this->isTreeActive(...)),
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

    /**
     * Checks if the current route matches any of the given routes.
     * Use this to apply the 'active' class to an <a> or <li> element.
     *
     * @param string|array $routes The route name(s) to check against.
     * @param string $class The class name to return (e.g., 'active').
     * @return string The class name or an empty string.
     */
    public function isActive($routes, string $class = 'active'): string
    {
        $currentRoute = $this->requestStack->getCurrentRequest()?->get('_route');

        if (!is_array($routes)) {
            $routes = [$routes];
        }

        if (in_array($currentRoute, $routes)) {
            return $class;
        }

        return '';
    }

    /**
     * Checks if any route in the list is active, used for AdminLTE tree-view state.
     *
     * @param array $childRoutes A list of route names inside the tree.
     * @return string 'menu-open' or an empty string.
     */
    public function isTreeActive(array $childRoutes): string
    {
        // Reuse the isActive logic, but set 'menu-open' and 'active' on the parent
        $currentRoute = $this->requestStack->getCurrentRequest()?->get('_route');

        if (in_array($currentRoute, $childRoutes)) {
            // Must return both for the parent <li>: 'active' for highlight, 'menu-open' for expanded state
            return 'menu-open';
        }

        return '';
    }
}
