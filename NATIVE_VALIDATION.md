# Registro de validación nativa

## 17 de agosto de 2026 — bloqueado de forma segura

Entorno:

- Shopware CLI `0.17.2`, binario Darwin arm64 verificado con el checksum oficial;
- Docker Desktop `29.5.3`;
- imagen oficial `ghcr.io/shopware/docker-dev:php8.4-node24-caddy`, digest
  `sha256:0c23acd7bc41d51a50b4746b1a2b11ca1e9fc2b37e4ff0333366811c2bb23e75`;
- PHP 8.4;
- intentos con Shopware `6.7.12.2` y la última release `6.7.13.0`.

Comandos reproducidos:

```bash
shopware-cli project create govp-shopware-native --version 6.7.12.2 --docker --with-amqp --php-version 8.4 --no-interaction
shopware-cli project create govp-shopware-native --version 6.7.13.0 --docker --with-amqp --php-version 8.4 --no-interaction
```

Composer bloqueó correctamente ambas instalaciones por el aviso
`PKSA-p9gd-j6gr-6f9t`: Shopware 6.7.12.2 resuelve `mcp/sdk 0.5.0` y 6.7.13.0
resuelve `mcp/sdk 0.6.0`, versiones afectadas según la política de seguridad del
instalador. El CLI ofreció `--no-audit`; no se utilizó.

Resultado: la prueba no alcanzó instalación, migración ni activación del plugin.
La puerta `nativeValidation` permanece pendiente hasta que una release segura de
Shopware pueda instalarse con la auditoría habilitada. Las comprobaciones de
Composer, sintaxis PHP, XML, eventos, migración y evidencia canónica del plugin sí
están superadas, pero no sustituyen esa puerta.
