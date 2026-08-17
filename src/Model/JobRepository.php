<?php declare(strict_types=1);

namespace Gemacode\GovpForShopware\Model;

use Doctrine\DBAL\Connection;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Uuid\Uuid;

final class JobRepository
{
    private const PROCESSING_LEASE_MINUTES = 15;

    public function __construct(private readonly Connection $connection) {}

    public function queue(string $deliveryId): bool
    {
        if (!Uuid::isValid($deliveryId)) throw new \InvalidArgumentException('deliveryId no válido.');
        return $this->connection->executeStatement(
            'INSERT IGNORE INTO gemacode_govp_delivery (delivery_id, status, created_at) VALUES (:id, :status, :created)',
            ['id' => Uuid::fromHexToBytes($deliveryId), 'status' => 'queued', 'created' => (new \DateTimeImmutable())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]
        ) === 1;
    }

    public function claim(string $deliveryId): bool
    {
        $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        return $this->connection->executeStatement(
            "UPDATE gemacode_govp_delivery SET status='processing', attempts=attempts+1, last_error=NULL, updated_at=:updated WHERE delivery_id=:id AND (status IN ('queued','attention') OR (status='processing' AND updated_at < :stale))",
            [
                'id' => Uuid::fromHexToBytes($deliveryId),
                'updated' => $now->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                'stale' => $now->modify('-' . self::PROCESSING_LEASE_MINUTES . ' minutes')->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            ]
        ) === 1;
    }

    public function success(string $deliveryId, string $code, string $url): void
    {
        $this->connection->update('gemacode_govp_delivery', ['status' => 'issued', 'govp_code' => $code, 'verify_url' => $url, 'last_error' => null], ['delivery_id' => Uuid::fromHexToBytes($deliveryId)]);
    }

    public function failure(string $deliveryId, string $message): void
    {
        $this->connection->update('gemacode_govp_delivery', ['status' => 'attention', 'last_error' => mb_substr($message, 0, 2048)], ['delivery_id' => Uuid::fromHexToBytes($deliveryId)]);
    }
}
