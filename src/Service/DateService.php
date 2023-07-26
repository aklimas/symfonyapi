<?php

namespace App\Service;

class DateService
{
    /**
     * @throws \Exception
     */
    public function calculateAge(string $dateOfBirth): int
    {
        $now = new \DateTime();
        $birthdate = new \DateTime($dateOfBirth);

        return $now->diff($birthdate)->y;
    }
}
