<?php

namespace App\MessageHandler;

use App\Message\SendEmailMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[AsMessageHandler]
class SendEmailMessageHandler
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function __invoke(SendEmailMessage $message)
    {
        $email = (new Email())
            ->from('admin@example.com')
            ->to($message->getEmail())
            ->subject($message->getSubject())
            ->text($message->getContent());

        $this->mailer->send($email);
    }
}
