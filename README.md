# Stationszimmer – HumHub-Module

![HumHub](https://img.shields.io/badge/HumHub-%E2%89%A5%201.16-1b9e77)
![PHP](https://img.shields.io/badge/PHP-8.x-777bb4)
![Module](https://img.shields.io/badge/Module-forum%20%C2%B7%20jobs-2b6a7a)
![Status](https://img.shields.io/badge/Status-WIP%20%C2%B7%20ungetestet-orange)
![i18n](https://img.shields.io/badge/i18n-DE%20%C2%B7%20FR--Ger%C3%BCst-d4a54b)

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
   für Intro/Basis/Top, das **Intro-Limit** (Standard 50) sowie **Moderation** eintragen.
7. HumHub-**Cron** muss serverseitig eingerichtet sein (Daily-Run setzt abgelaufene
   Inserate auf `expired`). Manuell: `php protected/yii jobs/expire`.

## Tarife (konfigurierbar, nicht im Code)

| Tier | Preis | Dauer | Besonderheit |
|---|---|---|---|
| Intro | CHF 49 | 30 Tage | nur für die **ersten 50 bezahlten Inserate** (`intro_listing_limit`); danach ausgeblendet |
| Basis | CHF 99 | 30 Tage | Einstiegspreis nach Ablauf der Intro-Aktion |
| Top | CHF 199 | 30 Tage | `is_top=1`, Top-Platzierung/Hervorhebung |
| Lehrstelle | gratis | 30 Tage | kein Stripe (Veröffentlichung nach Admin-Freigabe, da Moderation AN) |

> Preise = die im Stripe-Dashboard hinterlegten **Price-Objekte** (Beträge im Admin nicht
> dupliziert). Das Intro-Kontingent wird live aus der Tabelle abgeleitet (Anzahl Inserate
> mit `tier ≠ lehrstelle` und abgeschlossener Zahlung) – kein Datum, keine Extra-Spalte.
> Premium/Boost (~CHF 299) ist v2.

## Sicherheit (verbindlich)

- Stripe Secret/Webhook-Secret nur aus `params`/Env, nie in DB/Code/Logs.
- Webhook signaturgeprüft (`\Stripe\Webhook::constructEvent`), ungültig → HTTP 400.
- Tier/Preis/Dauer/`published_until` immer serverseitig aus der Config; Client wählt nur den Tier.
- Webhook idempotent (Status-Check), Eigentums-Checks bei edit/delete, Whitelist für
  `canton`/`setting`/`employment_type`/`tier`, CSRF überall an ausser Webhook.
