# Entscheide & Forenmodell (Stand 20.06.2026)

## Getroffene Entscheide
1. **Frisch starten** – KEINE Migration bestehender Nutzer/Threads/Posts aus der
   React/Supabase-App. Die Community wird in HumHub neu aufgebaut. → Das
   Migrationskonzept (`MIGRATION_Supabase_zu_HumHub.md`) entfällt damit
   weitgehend (bleibt als Referenz; relevant nur, falls später doch einzelne
   Daten – z. B. Jobinserate – übernommen werden sollen).
2. **GitHub-Remote**: gewünscht (privat, unter `karadumantrading`).
3. **Forenmodell**: ursprünglich „Forum-Modul (B)" gewünscht – siehe Befund unten.

## Befund: HumHub-Forum-Module sind schwach
Recherche im HumHub-Marketplace (Juni 2026):

| Modul | Autor | Preis | Version | Kategorien | Status |
|---|---|---|---|---|---|
| Discussion Boards (`discussions`) | Green Meteor | €48 | 1.0.0-beta.8 | **nein** (To-do) | unverifiziert |
| Chatter Forums (`chatter`) | Green Meteor | €69 | 2.0.0-beta.4 | unklar | unverifiziert |

Beide: **unverifizierte Community-Module, Beta, kostenpflichtig**, vom selben
Autor; HumHub warnt explizit, dass solche Module „nach Updates instabil werden
oder ausfallen" können. Für das **Kernfeature** einer Pflege-Community (8
Kategorien → Threads → Antworten) ist das ein Klumpenrisiko.

## Revidierte Empfehlung
Da ein „gepflegtes" Forum-Modul faktisch **nicht existiert**, sind die zwei
soliden Wege:

- **A · Spaces als Kategorien (HumHub-nativ).** Jede der 8 Kategorien = ein
  *Space* (beitretbar); Threads = Posts, Antworten = Kommentare.
  - Pro: nativ, **gratis**, stabil, sofort einsatzbereit, mobil/Notifications/
    Suche inklusive.
  - Contra: keine klassische „Forum-Thread-Liste"; eher Stream-Optik.
- **B′ · Eigenes Forum-Modul bauen** (wie das `jobs`-Modul): echte
  Kategorie→Thread→Antwort-Struktur, volle Kontrolle, **gratis**.
  - Pro: exakt die gewünschte Foren-Anmutung; keine Drittmodul-Abhängigkeit.
  - Contra: echte Eigenentwicklung (Aufwand), wie bei `jobs` hier nicht testbar
    ohne laufende HumHub-Instanz.

**Drittmodule (`discussions`/`chatter`) werden NICHT als Kern empfohlen**
(unverifiziert, Beta, kostenpflichtig, Kategorien schwach).

→ **Offene Entscheidung:** A (schnell, nativ, gratis) vs. B′ (eigenes Modul,
volle Foren-Treue). Sobald gewählt, binde ich das entsprechend an.

## Quellen
- HumHub Marketplace – Discussion Boards: https://marketplace.humhub.com/module/discussions
- HumHub Marketplace – Chatter Forums: https://marketplace.humhub.com/module/chatter
- HumHub Marketplace: https://marketplace.humhub.com/
