<?php

namespace App\MessageHandler;

use App\Entity\User;
use App\Message\SeriesWasCreated;
use App\Repository\UserRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SendNewSeriesEmailHandler
{
  public function __construct(
    private UserRepository $userRepository,
    private MailerInterface $mailer,
  ) {
  }

  public function __invoke(SeriesWasCreated $message)
  {
    $users = $this->userRepository->findAll();
    $userEmails = array_map(fn (User $user) => $user->getEmail(), $users);
    $series = $message->series;

    $email = (new TemplatedEmail())
      ->from('sistema@example.com')
      ->to(...$userEmails)
      //->cc('cc@example.com')
      //->bcc('bcc@example.com')
      //->replyTo('fabien@example.com')
      //->priority(Email::PRIORITY_HIGH)
      ->subject('Nova série criada')
      ->text("Série {$series->getName()} foi criada")
      ->htmlTemplate('emails/series-created.html.twig')
      ->context(compact('series'));
    // ->html("<h1>Série criada</h1><p>Série \"{$series->getName()}\" foi criada</p>");

    $this->mailer->send($email);
  }
}
