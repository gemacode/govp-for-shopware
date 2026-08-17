<?php declare(strict_types=1);

namespace Gemacode\GovpForShopware\Message;

use Shopware\Core\Framework\MessageQueue\AsyncMessageInterface;

final readonly class ProcessDeliveryMessage implements AsyncMessageInterface
{
    public function __construct(public string $deliveryId) {}
}
