<?php declare(strict_types=1);

namespace App\Service\Notification\Provider\Sms;

use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twilio\Rest\Client;

/**
 * @author Marcin Stanik <marcin.stanik@gmail.com>
 * @since 07-2024
 * @version 1.0.0
 */
class Twilio extends AbstractSmsProvider
    implements ISmsProvider
{

    /** @var string */
    protected const string CONFIG_KEY = 'notificationSmsTwilio';

    /**
     * @param ParameterBagInterface $ParameterBag
     */
    public function __construct(ParameterBagInterface $ParameterBag)
    {
        parent::__construct($ParameterBag);
    }

    /**
     * @return bool
     * @throws \Twilio\Exceptions\ConfigurationException
     */
    public function send(): bool
    {
        $Client = $this->getClient();

        try {
            $Result = $Client->messages->create(
                $this->getMobileTo(),
                [
                    'from' => $this->getSenderMobile(),
                    'body' => $this->getTextBody()
                ]
            );

            $this->setSendReport($Result->toArray());
        } catch (\Exception $Ex) {
            $this->setErrors([
                \sprintf('Twilio - Exception, code %s, message: %s', $Ex->getCode(), $Ex->getMessage())
            ]);

            return false;
        }

        return true;
    }

    /**
     * @return Client
     * @throws \Twilio\Exceptions\ConfigurationException
     */
    private function getClient(): Client
    {
        /** @var Client|null $Client */
        static $Client = null;

        if ($Client === null) {
            $accountSid = ($this->ParameterBag->get(self::CONFIG_KEY)['accountSid'] ?? null)
                ?: throw new ParameterNotFoundException(self::CONFIG_KEY . '.accountSid');
            $authToken = ($this->ParameterBag->get(self::CONFIG_KEY)['authToken'] ?? null)
                ?: throw new ParameterNotFoundException(self::CONFIG_KEY . '.authToken');

            $Client = new Client($accountSid, $authToken);
        }

        return $Client;
    }

}
