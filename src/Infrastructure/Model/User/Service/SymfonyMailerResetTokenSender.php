<?php

declare(strict_types=1);

namespace App\Infrastructure\Model\User\Service;

use App\Model\User\Entity\User\Email;
use App\Model\User\Entity\User\ResetToken;
use App\Model\User\Service\ResetTokenSender;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

final class SymfonyMailerResetTokenSender implements ResetTokenSender
{
    public function __construct(
        private MailerInterface $mailer
    ) {}

    public function send(Email $email, ResetToken $token): void
    {
        $email = (new TemplatedEmail())
            ->to(new Address($email->getValue()))
            ->subject('Password resetting')
            ->htmlTemplate('mail/user/reset.html.twig')
            ->context([
                'token' => $token->getToken()
            ]);

        try {
            $this->mailer->send($email);
        } catch(TransportExceptionInterface $e) {
            throw new \RuntimeException('Mail sending failed.');
        }
    }
}
