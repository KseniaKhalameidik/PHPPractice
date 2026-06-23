<?php

namespace App\MessageHandler;

use App\Message\UniversityCacheWarmUpMessage;
use App\Service\UniversityService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class UniversityCacheWarmUpMessageHandler
{
    public function __construct(
        private UniversityService $universityService,
        private LoggerInterface $logger
    ) {}

    public function __invoke(UniversityCacheWarmUpMessage $message): void
    {
        $this->logger->notice("Start university cache warm up for $message->name");
        $this->universityService->getUniversitiesByName($message->name);
        $this->logger->notice("Cache warm up for university $message->name done with success");
    }
}