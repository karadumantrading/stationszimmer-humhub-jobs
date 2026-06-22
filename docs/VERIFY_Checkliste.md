# @verify-Checkliste (HumHub-API gegen installierte Version)

Beide Module wurden ohne laufende HumHub-Instanz geschrieben. Diese Punkte
**vor/beim ersten Test** gegen die **installierte HumHub-Version** (Ziel ~1.16)
und `docs.humhub.org` prüfen. Priorität: **🔴 bricht sonst** · **🟡 wichtig** ·
**🟢 kosmetisch/optional**.

> Schnelltest insgesamt: Module in der Dev-Umgebung (`dev/`) aktivieren →
> Migrationen müssen durchlaufen → Menüpunkte erscheinen → je eine Seite je
> Controller öffnen. Fehler weisen meist genau auf einen Punkt unten.

## Stand 22.06.2026 — LIVE gegen HumHub 1.16 (Docker) getestet ✅
Beide Module aktiviert, Migrationen gelaufen, Seiten als Gast gerendert (200):
`forum/category/index` (8 Kategorien), `forum/category/view`, `jobs/job/index`.
Zusätzlich live verifiziert:
- **Cron**: `cron/daily` löst `onDailyCron` ohne Fehler aus; Console-Fallback
  `php yii jobs/expire` läuft (Action umbenannt `index`→`expire`, matcht Doku).
- **Webhook**: `POST /jobs/webhook` → **503** (Gast erreichbar, CSRF aus, kein Fatal
  ohne Stripe-SDK/Keys – `\Stripe\…` wird erst nach Key-Prüfung referenziert).
- **Composer fehlt im HumHub-Image** → Stripe-SDK per `composer.phar` (siehe dev/README).
- **`@<moduleId>`-Alias**: HumHub registriert ihn automatisch (Core nutzt
  `render('@activity/...')`) → Admin-Views (`@jobs/...`, `@forum/...`) lösen korrekt auf. ✅

Damit sind alle 🔴-Punkte geklärt. Offen bleiben nur eingeloggte End-to-End-Flows
(Thema/Antwort/Inserat erstellen, Stripe-Checkout) – brauchen Login bzw. Stripe-Keys.
Dabei gefundene + behobene Bugs:
- 🔧 **Cron-Klasse**: `humhub\commands\CronController` (nicht `modules\cron\…`) → Modul lud sonst nicht.
- 🔧 **Icon im Menü**: `humhub\modules\ui\widgets\Icon` existiert nicht → Core-Muster
  übernommen (MenuLink-Config-Array, `'icon' => 'briefcase'` als String).
- 🔧 **Migrations-Namenskollision**: HumHub trackt Modul-Migrationen global per `version`
  (PK) → beide `m000000_000000_init` kollidierten → eindeutig umbenannt.
- 🔧 **Webhook**: plain `yii\web\Controller` (kein `RULE_GUEST_ACCESS_ONLY`).
- 🔧 **Docblock-`*/`-Falle** in `JobListing.php` (`stripe_*/published_*` schloss den
  Kommentar → ParseError, 500 nur auf der jobs-Seite) → entschärft.
- ℹ️ **Gastzugriff**: via `php yii settings/set user auth.allowGuestAccess 1` + die
  `['guestAccess'=>[...]]`-Regeln in den öffentlichen Controllern. Wichtig: **Pretty-URLs**
  nutzen (`/forum/...`), nicht `/index.php?r=...` (Letzteres leitet Gäste auf /dashboard).

## Stand 21.06.2026 (gegen humhub/develop verifiziert)
- ✅ **Menü-API bestätigt**: `humhub\widgets\TopMenu::EVENT_INIT`,
  `humhub\modules\ui\menu\MenuLink`, `humhub\modules\ui\widgets\Icon` + Setter
  (setId/Label/Url/Icon/SortOrder/IsActive) + `addEntry` — `Events.php` (jobs+forum) passt 1:1.
- ✅ **ControllerAccess bestätigt**: `RULE_LOGGED_IN_ONLY = 'login'` existiert; es
  gibt **keine** `RULE_GUEST_*`-Konstante (Gastzugriff via GuestAccessValidator).
- 🔧 **Webhook gefixt**: nutzt jetzt plain `yii\web\Controller` (kein Access-Layer)
  statt der nicht existierenden `RULE_GUEST_ACCESS_ONLY`.
- ⏳ **Offen für Docker-Live-Test**: Cron-Klasse, Admin-Controller-Basis,
  `@modul`-View-Alias, Migrationslauf (siehe 🔴 unten).

---

## 🔴 Bricht das Modul, wenn falsch

- [ ] **Top-Menü-Event** `humhub\widgets\TopMenu::EVENT_INIT`
  → `jobs/config.php`, `forum/config.php`. Falls die Klasse/Konstante anders
  heisst, erscheinen die Menüpunkte nicht (oder Fehler beim Boot).
- [ ] **Menü-/Icon-Klassen** `humhub\modules\ui\menu\MenuLink`,
  `humhub\modules\ui\widgets\Icon`
  → `jobs/Events.php`, `forum/Events.php`. API der `setLabel/setUrl/setIcon/
  setSortOrder/setIsActive`-Aufrufe verifizieren.
- [ ] **Cron-Event** `humhub\modules\cron\CronController::EVENT_ON_DAILY_RUN`
  → `jobs/config.php` (Auto-Ablauf der Inserate).
- [ ] **Migration-Basisklasse** `humhub\components\Migration` + **`user`-Tabelle**
  als FK-Ziel → `jobs/migrations/*`, `forum/migrations/*`. Wenn die User-Tabelle
  anders heisst, schlägt `addForeignKey(... 'user' ...)` fehl.
- [ ] **Controller-Basis + Access** `humhub\components\Controller`,
  `humhub\components\access\ControllerAccess` inkl. Konstanten
  `RULE_LOGGED_IN_ONLY`, `RULE_GUEST_ACCESS_ONLY`
  → alle Controller in `jobs/` und `forum/`. Bei abweichenden Konstanten greift
  der Zugriffsschutz nicht/blockt alles.
- [ ] **Webhook-Guest-Zugriff** (kein Login, kein CSRF) `jobs/WebhookController.php`
  → muss für Stripe ohne Login erreichbar sein, sonst kommt keine Zahlung an.
- [ ] **Admin-Controller** `humhub\modules\admin\components\Controller`
  → `jobs/controllers/admin/*`, `forum/controllers/admin/*`. Erzwingt
  ManageModules-Recht; bei anderer Basis evtl. ungeschützt oder Fehler.
- [ ] **Modul-Alias `@jobs` / `@forum`** für Renders wie
  `render('@jobs/views/admin/config')` / `@forum/views/admin/...`
  → admin-Controller beider Module. HumHub registriert i. d. R. `@<moduleId>`;
  falls nicht, Render-Pfad anpassen (relativer View-Name bzw. viewPath).

## 🟡 Wichtig (Funktion/Verhalten)

- [ ] **View-Flash-Helfer** `$this->view->success() / ->info() / ->saved()`
  → diverse Controller. Falls nicht vorhanden, durch `Yii::$app->session->
  setFlash(...)` ersetzen.
- [ ] **`forcePostRequest()`** (HumHub-Controller-Methode) → mehrere Actions
  (reply, delete, pin, lock, approve …).
- [ ] **User-API** `User::isSystemAdmin()`, `->displayName`, `->id`
  → `forum/Module.php` (Moderation = Admin), `forum/views/thread/_post.php`
  (Autorname). Bei abweichender API Anzeigename/Moderationsprüfung anpassen.
- [ ] **Guest-Sichtbarkeit der Listen** hängt zusätzlich an der HumHub-Einstellung
  «Eingeschränkter Zugriff für Gäste» (Administration → Einstellungen). Ohne sie
  sehen Nicht-Eingeloggte Forum/Jobs evtl. nicht.
- [ ] **`data-method="post"` / `data-confirm`** Links (pin/lock/delete/approve)
  → HumHub/Yii-JS muss diese in POST + Bestätigung umsetzen (Standard, aber
  prüfen, dass das JS geladen ist).
- [ ] **AssetBundle-Publishing** `jobs/assets/Assets.php` (CSS aus `resources/`)
  → prüfen, dass das CSS publiziert/geladen wird; sonst Pfad/`sourcePath` anpassen.

## 🟢 Kosmetisch / optional

- [ ] **RichText-Editor statt Textarea** für Beschreibungen
  → `jobs/views/job/_form.php`, Forum-Beiträge. Optional
  `humhub\modules\content\widgets\richtext\RichTextField` einsetzen (dann auch
  serverseitiges Sanitizing über HumHub).
- [ ] **FontAwesome-Iconnamen** (`briefcase`, `comments`, `hospital-o`, …)
  → Menü + Kategorie-Seed. Bei FA-Versionswechsel ggf. umbenennen
  (z. B. `hospital-o` → `hospital`).
- [ ] **„Fieberkurve"-Theme-Look** ist Theme-Sache; die Aktivitätskurve pro
  Inserat ist explizit v2.

---

## Datei-Index (wo welcher Punkt sitzt)

| Datei | Punkte |
|---|---|
| `jobs/config.php`, `forum/config.php` | TopMenu-/Cron-Event |
| `jobs/Events.php`, `forum/Events.php` | Menu/Icon-Klassen |
| `jobs/migrations/*`, `forum/migrations/*` | Migration-Basis, `user`-FK |
| `jobs/controllers/*`, `forum/controllers/*` | Controller/Access, forcePostRequest, view-Flash |
| `jobs/controllers/admin/*`, `forum/controllers/admin/*` | Admin-Controller, `@modul`-Alias |
| `jobs/WebhookController.php` | Guest/kein-CSRF |
| `forum/Module.php`, `forum/views/thread/_post.php` | User-API |
| `jobs/assets/Assets.php` | Asset-Publishing |
| `jobs/views/job/_form.php` | RichText optional |
