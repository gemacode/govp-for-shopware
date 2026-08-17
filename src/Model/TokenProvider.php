<?php declare(strict_types=1);

namespace Gemacode\GovpForShopware\Model;

use Shopware\Core\System\SystemConfig\SystemConfigService;

final class TokenProvider
{
    public function __construct(private readonly SystemConfigService $config) {}

    public function get(?string $salesChannelId): string
    {
        $environment = getenv('GOVP_CONNECTOR_TOKEN');
        $token = is_string($environment) && $environment !== ''
            ? $environment
            : (string)$this->config->get('GovpForShopware.config.token', $salesChannelId);
        if (!preg_match('/^gx_[A-Za-z0-9_-]{32,128}$/', $token)) {
            throw new \RuntimeException('Falta un token GOVP válido en el vault/entorno o en la configuración de la extensión.');
        }
        return $token;
    }
}
