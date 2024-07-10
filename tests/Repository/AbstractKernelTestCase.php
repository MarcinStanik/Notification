<?php declare(strict_types=1);

namespace App\Tests\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @author Marcin Stanik <marcin.stanik@gmail.com>
 * @since 07-2024
 * @version 1.0.0
 */
abstract class AbstractKernelTestCase extends KernelTestCase
{

    protected ?EntityManagerInterface $EntityManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->EntityManager = self::bootKernel()->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->EntityManager->close();
        $this->EntityManager = null; // Prevents memory leaks
    }

}
