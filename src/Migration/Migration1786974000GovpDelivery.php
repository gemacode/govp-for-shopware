<?php declare(strict_types=1);

namespace Gemacode\GovpForShopware\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

final class Migration1786974000GovpDelivery extends MigrationStep
{
    public function getCreationTimestamp(): int { return 1786974000; }

    public function update(Connection $connection): void
    {
        $connection->executeStatement(<<<'SQL'
CREATE TABLE IF NOT EXISTS `gemacode_govp_delivery` (
  `delivery_id` BINARY(16) NOT NULL,
  `status` VARCHAR(24) NOT NULL DEFAULT 'queued',
  `govp_code` VARCHAR(128) NULL,
  `verify_url` TEXT NULL,
  `last_error` TEXT NULL,
  `attempts` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` DATETIME(3) NOT NULL,
  `updated_at` DATETIME(3) NULL,
  PRIMARY KEY (`delivery_id`),
  CONSTRAINT `fk.govp_delivery.delivery_id` FOREIGN KEY (`delivery_id`)
    REFERENCES `order_delivery` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function updateDestructive(Connection $connection): void {}
}
