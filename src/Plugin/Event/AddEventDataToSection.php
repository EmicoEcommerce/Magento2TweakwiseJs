<?php

declare(strict_types=1);

namespace Tweakwise\TweakwiseJs\Plugin\Event;

use Magento\Customer\CustomerData\SectionSourceInterface as Subject;
use Tweakwise\TweakwiseJs\Api\Event\SessionServiceInterface;

class AddEventDataToSection
{
    /**
     * @param SessionServiceInterface $sessionService
     */
    public function __construct(
        private readonly SessionServiceInterface $sessionService,
    ) {
    }

    /**
     * @param Subject $subject
     * @param array $result
     * @return array
     */
    public function afterGetSectionData(Subject $subject, array $result): array
    {
        $events = $this->sessionService->get();
        $this->sessionService->clear();

        return array_merge($result, ['tweakwise_events' => $events]);
    }
}
