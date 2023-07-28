<?php

namespace App\EventSubscriber;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Controller\JWTKey;
use App\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

final class RegisterMailSubscriber implements EventSubscriberInterface
{
    final public const EMAIL_SENDER = 'dev@programigo.com';

    private MailerInterface $mailer;

    private string $currentHost;

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        $this->currentHost = $request->getHttpHost();

    }

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['sendMail', EventPriorities::POST_WRITE],
            'kernel.request' => 'onKernelRequest',
        ];
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function sendMail(ViewEvent $event): void
    {
        $user = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (!$user instanceof User) {
            return;
        }

        if (Request::METHOD_POST == $method) {
            $this->sendConfirmationEmail($user);
        }
    }

    /**
     * @throws TransportExceptionInterface
     */
    private function sendConfirmationEmail(User $user): void
    {
        $token = JWTKey::generateToken($user);

        $confirmationLink = sprintf('https://'.$this->currentHost.'/email/confirm?token=%s', $token);

        $this->confirmEmail($user, $confirmationLink);
    }

    /**
     * @throws TransportExceptionInterface
     */
    private function confirmEmail(User $user, $confirmationLink): void
    {
        $message = (new Email())
            ->from(self::EMAIL_SENDER)
            ->to($user->getEmail())
            ->subject('Please Confirm your Email')
            ->text(sprintf('Link to activate account: %s ', $confirmationLink));

        $this->mailer->send($message);
    }


}
