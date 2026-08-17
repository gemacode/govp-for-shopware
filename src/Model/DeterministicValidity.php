<?php declare(strict_types=1);

namespace Gemacode\GovpForShopware\Model;

final class DeterministicValidity
{
    public static function fromCreatedAt(?\DateTimeInterface $createdAt, int $days): string
    {
        if ($createdAt === null) {
            throw new \InvalidArgumentException('La entrega no contiene una fecha de creación válida.');
        }

        $days = max(1, min(3650, $days));
        return \DateTimeImmutable::createFromInterface($createdAt)
            ->setTimezone(new \DateTimeZone('UTC'))
            ->modify(sprintf('+%d days', $days))
            ->format(\DateTimeInterface::ATOM);
    }
}
