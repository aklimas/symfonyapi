<?php

namespace App\EventSubscriber;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\User;
use Firebase\JWT\JWT;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

final class RegisterMailSubscriber implements EventSubscriberInterface
{
    final const EMAIL_SENDER = 'dev@programigo.com';
    final const HOST = '127.0.0.1:8000';

    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['sendMail', EventPriorities::POST_WRITE],
        ];
    }

    public function sendMail(ViewEvent $event)
    {
        $user = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (!$user instanceof User) {
            return;
        }

        if ($method == Request::METHOD_POST) {
            $this->sendConfirmationEmail($user);
        }
    }

    public function sendConfirmationEmail(User $user): void
    {
        $token = $this->generateToken($user);

        $confirmationLink = sprintf('https://'.self::HOST.'/email/confirm?token=%s', $token);

        $this->confirmEmail($user,$confirmationLink);
    }

    public function confirmEmail(User $user, $confirmationLink): void
    {
        $message = (new Email())
            ->from(self::EMAIL_SENDER)
            ->to($user->getEmail())
            ->subject('Please Confirm your Email')
            ->text(sprintf('Link to activate account: %s ', $confirmationLink));

        $this->mailer->send($message);
    }

    /**
     * @param User $user
     * @return string
     */
    public function generateToken(User $user): string
    {
        $payload = [
            'userId' => $user->getId(),
            'exp' => time() + 3600,
        ];

        return JWT::encode($payload, '12345', 'HS256');
    }

}
