<?php declare(strict_types=1);

namespace App\Controller;

use App\Service\NotificationService as NotificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Notification;
use App\Dto\NotificationDto;
use App\Type\NotificationChanelType;

/**
 * @author Marcin Stanik <marcin.stanik@gmail.com>
 * @since 07-2024
 * @version 1.0.0
 */
class NotificationController extends AbstractController
{

    /**
     * @param NotificationService $NotificationService
     */
    public function __construct(
        private NotificationService $NotificationService
    )
    {
    }

    #[Route(
        '/api/notification',
        name: 'api_notification',
        methods: ['POST']
    )]
    public function send(Request $Request): JsonResponse
    {
        $content = $Request->getContent();
        $data = \json_decode($content, true);

        return $this->sendNotification($data);
    }

    #[Route(
        '/api/notification/email',
        name: 'api_notification_email',
        methods: ['POST']
    )]
    public function email(Request $Request): JsonResponse
    {
        $content = $Request->getContent();
        $data = \json_decode($content, true);

        if (isset($data['recipients'])) {
            $recipients = [];
            foreach ($data['recipients'] as $recipient) {
                $recipients[] = \is_array($recipient)
                    ? $recipient
                    : [
                        'email' => $recipient,
                    ];
            }
            $data['recipients'] = $recipients;
        }

        $data['chanels'] = [NotificationChanelType::EMAIL->name];

        return $this->sendNotification($data);
    }

    #[Route(
        '/api/notification/sms',
        name: 'notification_sms',
        methods: ['POST']
    )]
    public function sms(Request $Request): JsonResponse
    {
        $content = $Request->getContent();
        $data = \json_decode($content, true);

        if (isset($data['recipients'])) {
            $recipients = [];
            foreach ($data['recipients'] as $recipient) {
                $recipients[] = \is_array($recipient)
                    ? $recipient
                    : [
                        'mobile' => $recipient,
                    ];
            }
            $data['recipients'] = $recipients;
        }

        $data['chanels'] = [NotificationChanelType::SMS->name];

        return $this->sendNotification($data);
    }

    /**
     * @param array $data
     * @return JsonResponse
     */
    private function sendNotification(array $data): JsonResponse
    {
        $NotificationDto = NotificationDto::factory($data);
        $errors = $NotificationDto->validate();

        if (\count($errors) > 0) {
            return new JsonResponse([
                'errors' => $errors,
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $Notifications = $this->NotificationService->storage($NotificationDto);

        return new JsonResponse([
            'success' => true,
            'message' => 'Created',
            'data' => [
                'notifications' => \array_map(
                    fn(Notification $Notification): array => $Notification->toArray(['id']),
                    $Notifications
                ),
            ],
        ]);
    }

}
