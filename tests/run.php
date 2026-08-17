<?php declare(strict_types=1);

require_once __DIR__ . '/../src/Model/CanonicalEvidence.php';

use Gemacode\GovpForShopware\Model\CanonicalEvidence;

$root = dirname(__DIR__);
$failures = [];
$check = static function (bool $condition, string $message) use (&$failures): void { if (!$condition) $failures[] = $message; };
$lines = [
    ['lineId' => 'b', 'productNumber' => 'SKU-B', 'quantity' => 2],
    ['lineId' => 'a', 'productNumber' => 'SKU-A', 'quantity' => 1],
];
$hash = CanonicalEvidence::hash(str_repeat('a', 32), '10001', str_repeat('b', 32), $lines);
$check($hash === CanonicalEvidence::hash(str_repeat('a', 32), '10001', str_repeat('b', 32), array_reverse($lines)), 'Evidence order must be stable.');
$changed = $lines; $changed[0]['quantity'] = 3;
$check($hash !== CanonicalEvidence::hash(str_repeat('a', 32), '10001', str_repeat('b', 32), $changed), 'Quantity changes must change evidence.');

foreach (glob($root . '/src/Resources/config/*.xml') ?: [] as $file) $check(simplexml_load_file($file) !== false, basename($file) . ' must be XML.');
$required = [
    'composer.json' => ['shopware-platform-plugin', 'shopware-plugin-class'],
    'src/Subscriber/DeliveryStateSubscriber.php' => ['STATE_SHIPPED', 'STATE_PARTIALLY_SHIPPED', 'ProcessDeliveryMessage'],
    'src/Message/ProcessDeliveryMessage.php' => ['AsyncMessageInterface'],
    'src/MessageHandler/ProcessDeliveryHandler.php' => ["'platform' => 'shopware'", 'shopware:channel:'],
    'src/Model/TokenProvider.php' => ['GOVP_CONNECTOR_TOKEN'],
    'src/Migration/Migration1786974000GovpDelivery.php' => ['gemacode_govp_delivery', 'ON DELETE CASCADE'],
];
foreach ($required as $name => $fragments) {
    $content = file_get_contents($root . '/' . $name);
    $check($content !== false, $name . ' must exist.');
    foreach ($fragments as $fragment) $check(str_contains((string)$content, $fragment), $name . ' missing ' . $fragment);
}
if ($failures) { fwrite(STDERR, implode(PHP_EOL, $failures) . PHP_EOL); exit(1); }
fwrite(STDOUT, "GOVP for Shopware checks passed: plugin, async events, state, secret preference and canonical evidence.\n");
