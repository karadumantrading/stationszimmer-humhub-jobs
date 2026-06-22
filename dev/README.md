# Dev-Umgebung (HumHub + MariaDB via Docker)

Lokale Umgebung, um das Modul `jobs` zu entwickeln und zu testen. Auf dieser
Maschine ist Docker (noch) **nicht installiert** – Schritt 0 deckt das ab.

## 0. Voraussetzungen (einmalig)

- **Docker Desktop für Windows** installieren: https://www.docker.com/products/docker-desktop/
  (WSL2-Backend empfohlen; benötigt einen Neustart und ggf. Admin-Rechte).
- Danach `docker --version` und `docker compose version` müssen funktionieren.

## 1. Starten

```powershell
cd C:\Projekte\stationszimmer-humhub-jobs\dev
Copy-Item .env.example .env      # Werte bei Bedarf anpassen
docker compose up -d
```

Dann **http://localhost:8080** öffnen und den HumHub-Installer durchlaufen
(DB-Host `db`, DB-Name/User/Passwort wie in `.env`). Falls die DB-Felder schon
vorbelegt sind, übernehmen.

## 2. Module aktivieren

Beide Module sind in den Container gemountet (`protected/modules/jobs` und
`…/forum`). Administration → Module → **«Forum»** und **«Jobbörse»** aktivieren
(Migrationen laufen dabei; das Forum seedet die 8 Bereiche).

## 3. Stripe-SDK + Secrets

```powershell
# Hinweis: Das mriedmann/humhub-Image bringt KEIN composer mit. Erst composer holen,
# dann das Stripe-SDK installieren (im Container ephemer -> nach `down`/Recreate erneut):
docker compose exec humhub sh -c "cd /var/www/localhost/htdocs && curl -sS https://getcomposer.org/installer | php && php composer.phar require stripe/stripe-php"

# Stripe-Params hinterlegen (Secrets via dev/.env -> Container-Env -> params)
# Inhalt von dev/local.php.sample in protected/config/local.php einfügen.
```

> Ohne SDK/Keys bleibt das Modul lauffähig: Checkout/Webhook prüfen zuerst die
> Konfiguration und antworten sonst mit Fehlermeldung bzw. HTTP 503 – **kein Fatal**.
> Für echte Zahlungstests brauchst du ein Stripe-Konto + Keys (siehe Haupt-README).

Webhook (z. B. via Stripe CLI in den Container forwarden):
`stripe listen --forward-to http://localhost:8080/index.php?r=jobs/webhook`

## 4. Entwickeln

- Modul-Dateien liegen lokal unter `../protected/modules/jobs` und sind **live**
  im Container. Nach Code-Änderungen ggf. den HumHub-Cache leeren:
  `docker compose exec humhub php protected/yii cache/flush-all`
- Cron manuell antreiben: `docker compose exec humhub php protected/yii cron/daily`
  oder direkt `… php protected/yii jobs/expire`.

## 5. Stoppen / Zurücksetzen

```powershell
docker compose down            # Container stoppen (Daten bleiben in Volumes)
docker compose down -v         # ALLES inkl. DB/Uploads löschen (Frischstart)
```

> **@verify:** Image-Tag, Webroot-Pfad und Env-Variablennamen hängen vom
> gewählten HumHub-Image ab (hier `mriedmann/humhub`). Bei Abweichungen die
> Mount-Pfade in `docker-compose.yml` und die Installer-Schritte anpassen.
> Offizielle HumHub-Doku: https://docs.humhub.org/
