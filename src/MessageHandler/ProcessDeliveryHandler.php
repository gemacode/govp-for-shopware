<?php declare(strict_types=1);

namespace Gemacode\GovpForShopware\MessageHandler;

use Gemacode\GovpForShopware\Message\ProcessDeliveryMessage;
use Gemacode\GovpForShopware\Model\CanonicalEvidence;
use Gemacode\GovpForShopware\Model\DeterministicValidity;
use Gemacode\GovpForShopware\Model\ExchangeClient;
use Gemacode\GovpForShopware\Model\JobRepository;
use Gemacode\GovpForShopware\Model\TokenProvider;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class ProcessDeliveryHandler
{
    public function __construct(
        private readonly EntityRepository $deliveryRepository,
        private readonly SystemConfigService $config,
        private readonly TokenProvider $tokens,
        private readonly ExchangeClient $exchange,
        private readonly JobRepository $jobs
    ) {}

    public function __invoke(ProcessDeliveryMessage $message): void
    {
        if (!$this->jobs->claim($message->deliveryId)) return;
        try {
            $criteria = (new Criteria([$message->deliveryId]))->addAssociation('order')->addAssociation('positions.orderLineItem');
            $delivery = $this->deliveryRepository->search($criteria, Context::createDefaultContext())->first();
            if ($delivery === null || $delivery->getOrder() === null) throw new \RuntimeException('Entrega Shopware no encontrada.');
            $order = $delivery->getOrder();
            $salesChannelId = $order->getSalesChannelId();
            if (!$this->config->getBool('GovpForShopware.config.active', $salesChannelId)) throw new \RuntimeException('GOVP está desactivado para este canal de venta.');
            $issuer = trim((string)$this->config->get('GovpForShopware.config.issuerName', $salesChannelId));
            $baseUrl = (string)$this->config->get('GovpForShopware.config.exchangeUrl', $salesChannelId);
            $days = max(1, min(3650, (int)$this->config->get('GovpForShopware.config.validityDays', $salesChannelId)));
            if ($issuer === '') throw new \RuntimeException('Falta el nombre del emisor GOVP.');
            $lines = [];
            foreach ($delivery->getPositions() ?? [] as $position) {
                $line = $position->getOrderLineItem();
                $payload = $line?->getPayload() ?? [];
                $lines[] = ['lineId' => $position->getId(), 'productNumber' => isset($payload['productNumber']) ? (string)$payload['productNumber'] : null, 'quantity' => $position->getQuantity()];
            }
            $hash = CanonicalEvidence::hash($delivery->getId(), $order->getOrderNumber(), $salesChannelId, $lines);
            $validUntil = DeterministicValidity::fromCreatedAt($delivery->getCreatedAt(), $days);
            $result = $this->exchange->issue($baseUrl, $this->tokens->get($salesChannelId), 'shopware:channel:' . strtolower($salesChannelId) . ':delivery:' . strtolower($delivery->getId()), [
                'issuer' => ['name' => $issuer],
                'subject' => ['type' => 'shipment', 'id' => $delivery->getId(), 'name' => 'Shopware delivery ' . $order->getOrderNumber(), 'description' => count($lines) . ' shipped positions'],
                'requirement' => 'Demuestra una expedición de Shopware mediante una huella de sus posiciones mínimas.',
                'evidence' => [['label' => 'Huella canónica de la entrega Shopware', 'sha256' => $hash]],
                'validUntil' => $validUntil,
                'source' => ['platform' => 'shopware', 'externalId' => 'delivery-' . $delivery->getId()],
            ]);
            $this->jobs->success($delivery->getId(), (string)$result['govp']['code'], (string)$result['govp']['verifyUrl']);
        } catch (\Throwable $error) {
            $this->jobs->failure($message->deliveryId, $error->getMessage());
            throw $error;
        }
    }
}
