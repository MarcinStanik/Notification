<?php declare(strict_types=1);

namespace App\Dto;

/**
 * @author Marcin Stanik <marcin.stanik@gmail.com>
 * @since 07-2024
 * @version 1.0.0
 */
interface IDto
{

    /**
     * @param array $data
     * @return self
     */
    public static function factory(array $data): static;

}
