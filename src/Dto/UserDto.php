<?php declare(strict_types=1);

namespace App\Dto;

/**
 * @author Marcin Stanik <marcin.stanik@gmail.com>
 * @since 07-2024
 * @version 1.0.0
 */
class UserDto
    implements IDto
{

    /**
     * @param int|null $id
     * @param string|null $name
     * @param string|null $email
     * @param string|null $mobile
     */
    public function __construct(
        private ?int $id = null,
        private ?string $name = null,
        private ?string $email = null,
        private ?string $mobile = null,
    )
    {
    }

    /**
     * @param array $data
     * @return static
     */
    public static function factory(array $data): static
    {
        return new self(
            $data['id'] ?? null,
            $data['name'] ?? null,
            $data['email'] ?? null,
            $data['mobile'] ?? null
        );;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getMobile(): ?string
    {
        return $this->mobile;
    }

}
