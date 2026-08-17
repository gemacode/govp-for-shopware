# GOVP for Shopware

Plugin abierto para Shopware 6.7 que emite GOVP cuando una entrega alcanza el
estado `shipped` o `shipped_partially`.

> Estado: candidato técnico `0.1.0`, todavía unidireccional y pendiente de
> instalación nativa limpia.

## Alcance

- plugin PHP nativo `shopware-platform-plugin`;
- configuración por canal de venta desde la administración;
- escucha los eventos de entrada en los estados oficiales de entrega;
- mensaje asíncrono mediante Symfony Messenger/cola de Shopware;
- reclamación atómica y estado persistente por `order_delivery`;
- clave idempotente por canal y entrega;
- evidencia mínima: número de pedido, producto, cantidad y entrega;
- preferencia por `GOVP_CONNECTOR_TOKEN` en el vault/entorno;
- token introducido en la pantalla únicamente para demos pequeñas;
- fallos conservados como `attention` y reintentables sin duplicar.

La documentación oficial de Shopware identifica los plugins PHP como la vía de
extensión con acceso al proceso y la base de datos. La versión pública más
reciente comprobada durante este desarrollo es Shopware 6.7.13.0:
[releases oficiales](https://github.com/shopware/shopware/releases).

## Instalación candidata

Copiar o instalar el paquete en `custom/plugins/GovpForShopware` y ejecutar:

```bash
bin/console plugin:refresh
bin/console plugin:install --activate GovpForShopware
bin/console cache:clear
bin/console messenger:consume async --time-limit=300
```

Configurar URL, emisor, vigencia y activación desde Extensions. En producción,
guardar el token con el [vault de secretos de
Shopware](https://github.com/shopware/docs/blob/main/resources/references/core-reference/commands-reference.md#secrets)
y exponerlo como `GOVP_CONNECTOR_TOKEN`.

## Desarrollo

```bash
composer validate --strict
find . -name '*.php' -not -path './vendor/*' -exec php -l {} \;
php tests/run.php
```

La primera candidata se centra en expedición. La comprobación receptora exige
definir previamente el documento B2B de entrada y no se simula como si Shopware
fuera un ERP de almacén.

## Puerta nativa

Consultar `NATIVE_ACCEPTANCE.md` y `NATIVE_VALIDATION.md`. La instalación, actualización, desinstalación,
dos canales, parcial, devolución, cola e indisponibilidad deben probarse antes
de promover el plugin.

Shopware es una marca de shopware AG. Este proyecto no está afiliado ni
certificado por shopware AG.

## Licencia

Apache-2.0.
