<?php declare(strict_types=1);

namespace Gemacode\GovpForShopware\Model;

use Symfony\Contracts\HttpClient\HttpClientInterface;

final class ExchangeClient
{
    public function __construct(private readonly HttpClientInterface $http) {}

    /** @param array<string,mixed> $payload @return array<string,mixed> */
    public function issue(string $baseUrl, string $token, string $key, array $payload): array
    {
        $url = rtrim($baseUrl, '/') . '/connectors/issue';
        if (parse_url($url, PHP_URL_SCHEME) !== 'https') throw new \InvalidArgumentException('GOVP Exchange requiere HTTPS.');
        $response = $this->http->request('POST', $url, [
            'headers' => ['Accept' => 'application/json', 'Authorization' => 'Bearer ' . $token, 'Idempotency-Key' => $key],
            'json' => $payload,
            'timeout' => 15,
        ]);
        $status = $response->getStatusCode();
        $body = $response->toArray(false);
        if ($status < 200 || $status >= 300 || !isset($body['govp']['code'], $body['govp']['verifyUrl'])) {
            throw new \RuntimeException('GOVP Exchange HTTP ' . $status);
        }
        return $body;
    }
}
