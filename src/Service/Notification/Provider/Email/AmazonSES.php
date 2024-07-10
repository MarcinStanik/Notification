<?php declare(strict_types=1);

namespace App\Service\Notification\Provider\Email;

use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Aws\Ses\SesClient;
use Aws\Exception\AwsException;

/**
 * @author Marcin Stanik <marcin.stanik@gmail.com>
 * @since 07-2024
 * @version 1.0.0
 */
class AmazonSES extends AbstractEmailProvider
    implements IEmailProvider
{

    /** @var string */
    protected const string CONFIG_KEY = 'notificationEmailAmazonSES';

    /**
     * @param ParameterBagInterface $ParameterBag
     */
    public function __construct(ParameterBagInterface $ParameterBag)
    {
        parent::__construct($ParameterBag);
    }

    /**
     * @return bool
     */
    public function send(): bool
    {
        $SesClient = $this->getSesClient();
        $charSet = 'UTF-8';

        try {
            $Result = $SesClient->sendEmail([
                'Destination' => [
                    'ToAddresses' => [$this->getEmailTo()],
                ],
                'Message' => [
                    'Body' => [
                        'Html' => [
                            'Charset' => $charSet,
                            'Data' => $this->getHtmlBody(),
                        ],
                        'Text' => [
                            'Charset' => $charSet,
                            'Data' => $this->getTextBody(),
                        ],
                    ],
                    'Subject' => [
                        'Charset' => $charSet,
                        'Data' => $this->getSubject(),
                    ],
                ],
                'Source' => $this->getSenderEmail(),
            ]);

            $this->setSendReport($Result->toArray());

        } catch (AwsException $Ex) {

            $this->setErrors([
                \sprintf('AwsException, code %s, message: %s', $Ex->getAwsErrorCode(), $Ex->getMessage())
            ]);

            return false;
        } catch (\Exception $Ex) {

            $this->setErrors([
                \sprintf('AmazonSES - Exception, code %s, message: %s', $Ex->getCode(), $Ex->getMessage())
            ]);

            return false;
        }

        return true;
    }

    /**
     * @return SesClient
     */
    private function getSesClient(): SesClient
    {
        /** @var SesClient|null $SesClient */
        static $SesClient = null;

        if ($SesClient === null) {
            $region = ($this->ParameterBag->get(self::CONFIG_KEY)['region'] ?? null)
                ?: throw new ParameterNotFoundException(self::CONFIG_KEY . '.region');
            $userAccessKey = ($this->ParameterBag->get(self::CONFIG_KEY)['userAccessKey'] ?? null)
                ?: throw new ParameterNotFoundException(self::CONFIG_KEY . '.userAccessKey');
            $userSecretAccessKey = ($this->ParameterBag->get(self::CONFIG_KEY)['userSecretAccessKey'] ?? null)
                ?: throw new ParameterNotFoundException(self::CONFIG_KEY . '.userSecretAccessKey');

            $SesClient = new SesClient([
                'region' => $region,
                'credentials' => [
                    'key' => $userAccessKey,
                    'secret' => $userSecretAccessKey,
                ]
            ]);
        }

        return $SesClient;
    }

}
