<?php declare(strict_types=1);

namespace Gemacode\GovpForShopware\Subscriber;

use Gemacode\GovpForShopware\Message\ProcessDeliveryMessage;
use Gemacode\GovpForShopware\Model\JobRepository;
use Shopware\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryStates;
use Shopware\Core\System\StateMachine\Event\StateMachineStateChangeEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class DeliveryStateSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly JobRepository $jobs, private readonly MessageBusInterface $bus) {}

    public static function getSubscribedEvents(): array
    {
        return [
            'state_enter.' . OrderDeliveryStates::STATE_MACHINE . '.' . OrderDeliveryStates::STATE_SHIPPED => 'onShipped',
            'state_enter.' . OrderDeliveryStates::STATE_MACHINE . '.' . OrderDeliveryStates::STATE_PARTIALLY_SHIPPED => 'onShipped',
        ];
    }

    public function onShipped(StateMachineStateChangeEvent $event): void
    {
        $deliveryId = $event->getTransition()->getEntityId();
        if ($this->jobs->queue($deliveryId)) $this->bus->dispatch(new ProcessDeliveryMessage($deliveryId));
    }
}
