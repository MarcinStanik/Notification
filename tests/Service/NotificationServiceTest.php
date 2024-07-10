<?php declare(strict_types=1);

namespace App\Tests\Service;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use App\Service\RabbitMqService;
use App\Repository\NotificationRepository;
use App\Repository\NotificationRecipientRepository;
use App\Service\NotificationService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author Marcin Stanik <marcin.stanik@gmail.com>
 * @since 07-2024
 * @version 1.0.0
 */
class NotificationServiceTes extends KernelTestCase
{
    private ContainerInterface $Container;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
    }

    public function testMock()
    {
        $ParameterBagMock = $this->createMock(ParameterBagInterface::class);
        $RabbitMqServiceMock = $this->createMock(RabbitMqService::class);
        $NotificationRepositoryMock = $this->createMock(NotificationRepository::class);
        $NotificationRecipientRepositoryMock = $this->createMock(NotificationRecipientRepository::class);

        $NotificationService = new NotificationService(
            $ParameterBagMock,
            $RabbitMqServiceMock,
            $NotificationRepositoryMock,
            $NotificationRecipientRepositoryMock
        );

        $this->assertInstanceOf(NotificationService::class, $NotificationService);

        $result = $NotificationService->send(1);

        $this->assertIsBool($result);
    }

}
