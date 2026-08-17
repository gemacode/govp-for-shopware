# Aceptación nativa — Shopware 6

1. Instalar, activar y migrar en una tienda limpia Shopware 6.7 soportada.
2. Configurar dos canales con emisores separados y token en vault/entorno.
3. Expedir una entrega completa y otra parcial; obtener un GOVP por entrega.
4. Repetir la transición y el mensaje; comprobar idempotencia y reclamación.
5. Ejecutar dos workers concurrentes sin doble emisión.
6. Probar 429, 503, caída de red, recuperación y reconciliación de `attention`.
7. Probar devolución y política acordada de revocación/sustitución.
8. Verificar que los logs, exportaciones y soporte no exponen token ni datos
   personales innecesarios.
9. Actualizar el plugin, desactivarlo y desinstalarlo conservando referencias;
   el borrado de tabla debe ser una acción separada y explícita.
10. Completar un piloto por un administrador no técnico.

Registrar versión, comandos y resultados. Las pruebas locales de estructura no
sustituyen esta matriz.
