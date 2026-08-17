# Changelog

## 0.1.1 — 2026-08-17

- Make validity deterministic across retries from the delivery creation time.
- Reclaim abandoned `processing` jobs after a bounded 15-minute lease.
- Repeat the secure native-installation check with Shopware CLI 0.17.3; the
  upstream `mcp/sdk` advisory remains blocking without `--no-audit`.

## 0.1.0 — 2026-08-17

- First Shopware 6.7 plugin candidate.
- Shipped/partially shipped events, asynchronous processing and atomic job state.
- Vault-first token, canonical evidence and stable Exchange idempotency.
- Native installation attempts documented; Shopware 6.7.12.2 and 6.7.13.0 are
  blocked by Composer advisory `PKSA-p9gd-j6gr-6f9t` without bypassing audit.
