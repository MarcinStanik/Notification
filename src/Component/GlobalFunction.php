<?php declare(strict_types=1);

namespace App\Component;

/**
 * @author Marcin Stanik <marcin.stanik@gmail.com>
 * @since 07-2024
 * @version 1.0.0
 */
class GlobalFunction
{

    /**
     * @param \DateTime|\DateTimeImmutable $DateTime
     * @param string $formatFrom
     * @return array
     */
    public static function dateTimeToArray(\DateTime|\DateTimeImmutable $DateTime, string $formatFrom = 'Y-m-d H:i:s'): array
    {
       return [
            'year' => $DateTime->format('Y'),
            'month' => $DateTime->format('n'),
            'day' => $DateTime->format('j'),
            'hour' => $DateTime->format('G'),
            'minute' => $DateTime->format('i'),
            'second' => $DateTime->format('s'),
            'timestamp' => $DateTime->getTimestamp(),
            'formatted' => $DateTime->format($formatFrom),
        ];
    }

    /**
     * @param \DateTime|\DateTimeImmutable $DateTime
     * @param string $formatFrom
     * @return array
     */
    public static function dateToArray(\DateTime|\DateTimeImmutable $DateTime, string $formatFrom = 'Y-m-d'): array
    {
        return [
            'year' => $DateTime->format('Y'),
            'month' => $DateTime->format('n'),
            'day' => $DateTime->format('j'),
            'timestamp' => $DateTime->getTimestamp(),
            'formatted' => $DateTime->format($formatFrom),
        ];
    }

}
