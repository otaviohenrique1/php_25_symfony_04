<?php

namespace App\MessageHandler;

use App\Entity\User;
use App\Message\SeriesWasCreated;
use App\Repository\UserRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class LogNewSeriesEmailHandler
{
  public function __construct(
    private LoggerInterface $logger,
  ) {
  }

  public function __invoke(SeriesWasCreated $message)
  {
    $this->logger->info('A new series was created', ['series' => [
      'seriesName' => $message->series
    ]]);
  }
}
