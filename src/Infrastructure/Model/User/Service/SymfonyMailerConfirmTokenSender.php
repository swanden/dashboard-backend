<?php

declare(strict_types=1);

namespace App\Infrastructure\Model\User\Service;

use App\Model\User\Entity\User\Email;
use App\Model\User\Service\ConfirmTokenSender;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;

final class SymfonyMailerConfirmTokenSender implements ConfirmTokenSender
{
    public function __construct(
        private MailerInterface $mailer
    ) {}

    public function send(Email $email, string $token): void
    {
        $email = (new TemplatedEmail())
            ->to(new Address($email->getValue()))
            ->subject('Signup confirmation')
            ->htmlTemplate('mail/user/signup.html.twig')
            ->context([
                'token' => $token
            ]);

        try {
            $this->mailer->send($email);
        } catch(TransportExceptionInterface $e) {
            throw new \RuntimeException('Mail sending failed.');
        }
    }
}
