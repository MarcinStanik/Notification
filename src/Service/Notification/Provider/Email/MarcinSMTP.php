<?php declare(strict_types=1);

namespace App\Service\Notification\Provider\Email;

use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * @author Marcin Stanik <marcin.stanik@gmail.com>
 * @since 07-2024
 * @version 1.0.0
 */
class MarcinSMTP extends AbstractEmailProvider
    implements IEmailProvider
{

    /** @var string */
    protected const string CONFIG_KEY = 'notificationEmailMarcinSMTP';

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
        $sendResult = false;

        $PHPMailer = $this->getPHPMailer();

        try {
            $PHPMailer->setFrom($this->getSenderEmail());
            $PHPMailer->addAddress($this->getEmailTo());

            $PHPMailer->isHTML();
            $PHPMailer->Subject = $this->getSubject();
            $PHPMailer->Body = $this->getHtmlBody();
            $PHPMailer->AltBody = $this->getTextBody();

            $sendResult = $PHPMailer->send();

            if ($sendResult === false) {
                $this->addError('Problem sending email by Maricn SMTP');
            }
        } catch (\Exception $Ex) {
            $this->addError(
                \sprintf('MarcinSMTP - Exception, code %s, message: %s', $Ex->getCode(), $Ex->getMessage())
            );
        }

        return $sendResult;
    }

    /**
     * @return PHPMailer
     */
    private function getPHPMailer(): PHPMailer
    {
        /** @var PHPMailer|null $PHPMailer */
        static $PHPMailer = null;

        if ($PHPMailer === null) {
            $host = ($this->ParameterBag->get(self::CONFIG_KEY)['host'] ?? null)
                ?: throw new ParameterNotFoundException(self::CONFIG_KEY . '.host');
            $user = ($this->ParameterBag->get(self::CONFIG_KEY)['user'] ?? null)
                ?: throw new ParameterNotFoundException(self::CONFIG_KEY . '.user');
            $password = ($this->ParameterBag->get(self::CONFIG_KEY)['password'] ?? null)
                ?: throw new ParameterNotFoundException(self::CONFIG_KEY . '.password');
            $port = ($this->ParameterBag->get(self::CONFIG_KEY)['port'] ?? null)
                ?: throw new ParameterNotFoundException(self::CONFIG_KEY . '.port');

            $PHPMailer = new PHPMailer(true);
            $PHPMailer->isSMTP();
            $PHPMailer->Host = $host;
            $PHPMailer->SMTPAuth = true;
            $PHPMailer->Username = $user;
            $PHPMailer->Password = $password;
            $PHPMailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;  //TLS
            $PHPMailer->Port = $port;
        }

        return $PHPMailer;
    }

}
