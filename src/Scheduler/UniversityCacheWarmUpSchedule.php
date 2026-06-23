<?php

namespace App\Scheduler;

use App\Message\UniversityCacheWarmUpMessage;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;
use Symfony\Contracts\Cache\CacheInterface;

#[AsSchedule('cache_warmup')]
final class UniversityCacheWarmUpSchedule implements ScheduleProviderInterface
{
    public function __construct(
        private CacheInterface $cache,
    ) {
    }

    public function getSchedule(): Schedule
    {
        return (new Schedule())
            ->add(
                // @TODO - Modify the frequency to suite your needs
                RecurringMessage::every('5 seconds', new UniversityCacheWarmUpMessage('Far')),
            )
            ->add(
                RecurringMessage::every('10 seconds', new UniversityCacheWarmUpMessage('Moscow'))
            )
            ->add(
                RecurringMessage::every('15 seconds', new UniversityCacheWarmUpMessage('Ural'))
            )
            ->stateful($this->cache)
        ;
    }
}