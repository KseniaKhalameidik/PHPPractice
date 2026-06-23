<?php

namespace App\MessageHandler;

use App\Message\SendEmailMessage;
use App\Repository\UserRepository;
use App\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Email;

#[AsMessageHandler]
final class SendEmailMessageHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private LoggerInterface $logger,
        private MailerInterface $mailer,
    ){
    }

    public function __invoke(SendEmailMessage $message): void
    {
        $this->logger->notice("Start of handling SendEmailMessage", [
            'user_id' => $message->userId,
            'text' => $message->text
        ]);

        /** @var User|null $user */
        $user = $this->userRepository->findOneBy([ 'id' => $message->userId ]);

        if ($user !== null) {
            $this->logger->notice('User data', [
                'id' => $user->getId(),
                'mail' => $user->getEmail()
            ]);
            
            try {
                $email = (new Email())
                    ->from('test@test.com')
                    ->to($user->getEmail())
                    ->text($message->text)
                ;

                $this->mailer->send($email);
            } catch (TransportException $exception) {
                $this->logger->error('Mailer sending error', ['exception' => $exception]);
            }
        }
    }
}