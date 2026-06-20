# Stationszimmer – HumHub-Module

HumHub-Custom-Module (Yii2 / PHP 8.x / MariaDB) für die Pflege-Community
Stationszimmer. Zwei Module in diesem Repo:

- **`forum`** — kategorie-basiertes Forum (8 Bereiche → Themen → Antworten),
  inkl. Moderation (anpinnen/sperren/löschen), Admin-Bereichsverwaltung, DE+FR.
  *Frisch starten* (keine Migration); Eigenbau statt schwacher Drittmodule
  (siehe `docs/ENTSCHEIDE_und_Forenmodell.md`).
- **`jobs`** — bezahlte Stelleninserate (Stripe Checkout, Tarife, Auto-Ablauf).

Sprache: DE-CH (ss statt ß) + FR-Gerüst.

---

## Modul «Jobbörse» (`jobs`)

Bezahlte Stelleninserate für Pflege-Arbeitgeber (Heime, Spitäler, Spitex).
Stripe Checkout (gehostet), automatischer Ablauf, DE-CH + FR.

> **Status / Hinweis:** Dieser Code wurde ausserhalb einer laufenden HumHub-Instanz
> verfasst (auf der Build-Maschine sind kein PHP/Composer/MySQL/HumHub vorhanden).
> Er ist **nicht ausgeführt/getestet**. HumHub-Core-APIs bitte gegen die **installierte
> Version** (Ziel ~1.16) und `docs.humhub.org` prüfen (Stellen mit `@verify` markiert).

## Installation in eine HumHub-Instanz

1. Ordner `protected/modules/jobs/` dieses Repos in die HumHub-Installation kopieren
   (Zielpfad identisch: `<humhub>/protected/modules/jobs/`).
2. Stripe-SDK installieren: im HumHub-Root `composer require stripe/stripe-php`.
3. Modul aktivieren: Administration → Module (Marketplace → installierte/lokale Module)
   → «Jobbörse» aktivieren. Dabei laufen die Migrationen (`migrations/`).
4. **Secrets** setzen (NICHT in DB, NICHT committen) – in `protected/config/local.php`:
   ```php
   return [
       'params' => [
           'stripe' => [
               'secretKey'     => getenv('STRIPE_SECRET_KEY')     ?: 'sk_test_…',
               'webhookSecret' => getenv('STRIPE_WEBHOOK_SECRET') ?: 'whsec_…',
           ],
       ],
   ];
   ```
5. Stripe-Webhook anlegen: Endpoint `https://DEINE-DOMAIN/jobs/webhook` (bzw.
   `/index.php?r=jobs/webhook`), Event `checkout.session.completed`.
6. Admin-Konfiguration öffnen (Administration → Jobbörse) und die **Stripe-Price-IDs**
   für Intro/Basis/Top, das **Intro-Stichdatum** sowie **Moderation an/aus** eintragen.
7. HumHub-**Cron** muss serverseitig eingerichtet sein (Daily-Run setzt abgelaufene
   Inserate auf `expired`). Manuell: `php protected/yii jobs/expire`.

## Tarife (konfigurierbar, nicht im Code)

| Tier | Preis | Dauer | Besonderheit |
|---|---|---|---|
| Intro | CHF 49 | 30 Tage | nur bis Stichdatum wählbar |
| Basis | (Admin) | 30 Tage | – |
| Top | (Admin) | 30 Tage | `is_top=1`, Hervorhebung |
| Lehrstelle | gratis | 30 Tage | kein Stripe (optional Admin-Freigabe) |

## Sicherheit (verbindlich)

- Stripe Secret/Webhook-Secret nur aus `params`/Env, nie in DB/Code/Logs.
- Webhook signaturgeprüft (`\Stripe\Webhook::constructEvent`), ungültig → HTTP 400.
- Tier/Preis/Dauer/`published_until` immer serverseitig aus der Config; Client wählt nur den Tier.
- Webhook idempotent (Status-Check), Eigentums-Checks bei edit/delete, Whitelist für
  `canton`/`setting`/`employment_type`/`tier`, CSRF überall an ausser Webhook.
