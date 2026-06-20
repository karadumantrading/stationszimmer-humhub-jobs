# Migrationskonzept: Stationszimmer (React/Supabase) → HumHub

Durchdenken der Inhalts-Migration für den Pivot. **Noch kein Code, nur Konzept**
mit den ehrlichen harten Punkten.

---

## 0. Die wichtigste Erkenntnis zuerst

**HumHub ist kein Forum, sondern ein soziales Netzwerk.** Es kennt von Haus aus
keine Threads/Posts wie das React-Forum, sondern *Spaces* mit Status-Beiträgen
und Kommentaren. Das ist die zentrale Architekturentscheidung, die ALLES andere
bestimmt — sie muss VOR der Migration fallen:

- **Variante A – Spaces/Streams (HumHub-Kern):** jede Forumskategorie = ein
  *Space*; jeder Thread = ein *Post* im Space; jede Antwort = ein *Kommentar*.
  Pro: rein im Core, stabil. Contra: Threads sind dann „flache" Posts (keine
  echte Forenstruktur, keine Thread-Titel als eigene Entität).
- **Variante B – Forum-Modul:** ein Community-Forum-Modul installieren und
  Threads/Posts dort einlesen. Pro: echte Forenstruktur. Contra: Modul-Reife/
  -Pflege prüfen; Importpfad abhängig vom Modul.
- **Empfehlung:** Wenn die Foren-Anmutung wichtig ist (sie ist Kern von
  Stationszimmer) → **B mit einem gepflegten Forum-Modul**, sonst A.
  → **Diese Entscheidung blockiert die Thread/Post-Migration.**

Die **Jobbörse** ist davon unberührt: sie hat im HumHub-Modul eine eigene
Tabelle (`job_listing`) und wird direkt befüllt (sauberster Teil der Migration).

---

## 1. Quell-Inventar (Supabase `ferirxjunfryoitneygm`)

| Tabelle | Inhalt | Zielkonzept HumHub |
|---|---|---|
| `auth.users` | E-Mail + (GoTrue-)Passwort-Hash | HumHub `user` — **Passwörter NICHT direkt migrierbar** (s. §3) |
| `profiles` | name, role, canton, bio, avatar_color, is_moderator, account_type, employer_institution, preferred_locale | HumHub User + Profilfelder + Gruppen |
| `categories` | 8 Forenkategorien | Spaces (A) bzw. Forum-Kategorien (B) |
| `threads` | Thread (title, author, lang, tags) | Post (A) / Thread (B) |
| `posts` | Beiträge/Antworten (body, author, lang) | Kommentare (A) / Posts (B) |
| `reports` | Moderationsmeldungen | HumHub-eigene Moderation (i. d. R. **nicht migrieren**) |
| `job_listings` | Stelleninserate (Stripe) | **`job_listing` (neues Modul) — 1:1-nah** |

DMs gab es im React-Stack **nicht**; HumHub bringt sie über das Mail-Modul mit →
nichts zu migrieren.

---

## 2. Feld-Mappings (konkret)

### 2.1 Nutzer (profiles → HumHub user + profile)
- `name` → Anzeigename (HumHub trennt Vor-/Nachname; ggf. splitten oder als
  Spitzname). `email` → user.email. `bio` → Profilfeld „about".
- `canton`, `role` → **eigene Profilfelder** anlegen (HumHub Profile Fields) oder
  als Gruppen. `is_moderator` → HumHub-Gruppe/Rolle „Moderation".
- `account_type=employer` + `employer_institution` → Gruppe „Arbeitgeber" +
  Profilfeld; relevant fürs Jobs-Modul (wer inserieren darf).
- `preferred_locale` → HumHub `user.language` (de/fr).
- `avatar_color` → kein direktes Ziel; HumHub generiert Default-Avatare → ignorieren.

### 2.2 Stelleninserate (job_listings → job_listing) — **sauberer Teil**
| Supabase | HumHub-Modul | Hinweis |
|---|---|---|
| institution_name | company_name | 1:1 |
| title, description, canton, location | dito | 1:1 |
| qualification (CareRole) | — | **kein direktes `setting`**; ableiten/zuordnen |
| employment_type (Festanstellung/Temporär/…) | employment_type | Werte mappen (`temporaer` etc.) |
| pensum_min/max | pensum_min/max | 1:1 |
| contact_email/url | dito | 1:1 |
| status (draft/paid/published/…) | status | mappen (paid→pending_review/published) |
| featured (+featured_until) | is_top / tier=`top` | Hervorhebung → Top-Tier |
| stripe_* | stripe_* | optional übernehmen |
| created_by (Supabase-UUID) | created_by (HumHub-int-ID) | **ID-Remapping nötig** (s. §3) |

⚠️ `setting` (spital/spitex/langzeit/…) existiert im React-Modell nicht 1:1 — die
React-Jobs nutzen `qualification`. Entweder beim Import zuordnen oder leer/Default.

### 2.3 Foren (categories/threads/posts) — abhängig von §0-Entscheid
- created_at/created_by **erhalten** (HumHub-Content hat beides).
- `lang` (de/fr) → Content-Sprache (falls Forum-Modul) oder Tag.

---

## 3. Die harten Punkte (ehrlich)

1. **Passwörter sind nicht migrierbar.** Supabase/GoTrue speichert eigene
   Hashes; HumHub nutzt sein eigenes Schema. → Nutzer werden **ohne Passwort
   importiert** und erhalten eine **Einladungs-/Passwort-Reset-Mail** beim
   Cutover. Friktion einplanen (Kommunikation!).
2. **ID-Remapping.** Supabase nutzt UUIDs, HumHub auto-increment-int. Während des
   Imports eine **Mapping-Tabelle** (UUID → HumHub-user.id) führen und überall
   (Inserate, Threads, Posts) anwenden.
3. **Forenmodell-Bruch** (§0): 1:1-Treue ist nicht garantiert; je nach Variante
   gehen Thread-Titel/Struktur teils verloren (A) oder hängen am Modul (B).
4. **Reihenfolge/Referenzielle Integrität:** erst Users → dann Spaces/Kategorien
   → dann Threads → dann Posts/Kommentare → dann Jobs.

---

## 4. Empfohlener technischer Weg

**Export (JSON) + HumHub-Konsolen-Import über die Model-Schicht** — NICHT
DB-zu-DB direkt (HumHubs polymorphe `content`/`content_container`-Tabellen von
Hand zu füllen ist fragil).

1. **Export aus Supabase** je Tabelle als JSON (Supabase-Client/REST oder SQL→JSON).
2. **Import-Konsolenbefehl** in einem kleinen HumHub-Modul (`migrate`) oder im
   `jobs`-Modul:
   - `users.json` → `User`/`Profile` anlegen (ohne Passwort, Status „muss Passwort
     setzen"), Mapping-Tabelle schreiben.
   - `categories.json` → Spaces (oder Forenkategorien) anlegen.
   - `threads.json` + `posts.json` → Content über die HumHub-Modelle erzeugen
     (created_by via Mapping, created_at setzen).
   - `job_listings.json` → `JobListing`-Records (created_by via Mapping).
   - Reine Model-Nutzung = HumHub-Lifecycle/Notifications korrekt; idempotent
     (z. B. externe-ID-Feld pro importiertem Datensatz, Re-Run-sicher).
3. **Reset-/Einladungs-Mails** auslösen.

Pseudostruktur:
```
php protected/yii migrate-content/users   exports/users.json
php protected/yii migrate-content/spaces  exports/categories.json
php protected/yii migrate-content/threads exports/threads.json exports/posts.json
php protected/yii migrate-content/jobs     exports/job_listings.json
```

---

## 5. Phasen & Cutover

1. **Entscheid §0** (Forenmodell) — Blocker.
2. HumHub-Dev-Umgebung (siehe `dev/`) + Module (jobs, ggf. Forum, migrate).
3. Export-Skripte (Supabase → JSON).
4. Import-Befehle + Dry-Run auf Dev, Datenkontrolle.
5. Profilfelder/Gruppen/Rollen in HumHub vorbereiten (canton, role, Arbeitgeber, Moderation).
6. **Probemigration** (voller Datensatz auf Staging), QA.
7. **Cutover:** Wartungsfenster → finaler Export/Import → DNS/Domain auf HumHub →
   Reset-Mails → React/Supabase stilllegen (erst nach Verifikation, Backup behalten).

---

## 6. Risiken

- **Passwort-Reset-Friktion** → Nutzerkommunikation vor Cutover.
- **Foren-Fidelity** (Modellbruch) → früh prototypisch testen.
- **SEO/URLs** ändern sich (andere URL-Struktur) → Redirects planen, falls
  öffentliche Threads indexiert sind.
- **Aufwand**: Der React-Stack hatte viel Funktionalität (Realtime, Suche,
  Lohnrechner, Stripe-Jobs, i18n). In HumHub muss jedes Teil neu abgebildet/
  -gebaut werden — der Pivot ist **kein Port, sondern weitgehend ein Neuaufbau**.
  Das ist die strategisch teuerste Konsequenz und sollte bewusst getragen werden.

---

## 7. Offene Entscheidungen (für dich)

- **Forenmodell:** Spaces (A) oder Forum-Modul (B)?
- **Bestehende Nutzer**: wirklich migrieren (mit Reset-Mail) oder Neustart der Community?
- **Bestehende Threads/Posts**: migrieren oder als „Archiv" stehen lassen und in HumHub frisch starten?
- **Lohnrechner / Realtime / Suche**: in HumHub neu nachbauen — Priorität?
