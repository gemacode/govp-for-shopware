<?php declare(strict_types=1);

namespace Gemacode\GovpForShopware\Model;

final class CanonicalEvidence
{
    /** @param array<int,array{lineId:string,productNumber:?string,quantity:int}> $lines */
    public static function hash(string $deliveryId, string $orderNumber, string $salesChannelId, array $lines): string
    {
        usort($lines, static fn (array $a, array $b): int => $a['lineId'] <=> $b['lineId']);
        return hash('sha256', json_encode([
            'deliveryId' => strtolower($deliveryId),
            'orderNumber' => $orderNumber,
            'salesChannelId' => strtolower($salesChannelId),
            'lines' => $lines,
        ], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    }
}
