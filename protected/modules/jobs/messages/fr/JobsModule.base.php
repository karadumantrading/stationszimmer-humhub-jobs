<?php
/**
 * Français – fichier de langue du module «Jobbörse».
 *
 * GERÜST / PLATZHALTER: Werte bewusst leer – werden später vom Lektor-Agenten
 * mit Schweizer Französisch befüllt. Solange FR nicht aktiv ausgerollt ist,
 * läuft das Modul auf Deutsch (DE bleibt voll funktionsfähig).
 * Keys identisch zu messages/de/JobsModule.base.php.
 */
return [
    // Tarif-Labels
    'Intro' => '',
    'Basis' => '',
    'Top' => '',
    'Lehrstelle' => '',

    // Navigation / UI
    'Jobbörse' => '',
    'Meine Inserate' => '',
    'Inserat aufgeben' => '',
    'Filtern' => '',
    'Zurücksetzen' => '',
    'Alle Kantone' => '',
    'Alle Bereiche' => '',
    'Alle Anstellungen' => '',
    'Pensum %' => '',
    'Aktuell sind keine Inserate ausgeschrieben.' => '',
    'Bitte wählen …' => '',
    'Weiter zur Tarifwahl' => '',
    'Abbrechen' => '',
    'Bearbeiten' => '',
    'Speichern' => '',
    'Konfiguration' => '',
    'Alle' => '',
    'Freigeben' => '',
    'Ablehnen' => '',
    'Ablaufen lassen' => '',
    'Inserate verwalten' => '',

    // Feld-Labels
    'Arbeitgeber' => '',
    'Titel' => '',
    'Bereich' => '',
    'Kanton' => '',
    'Pensum von (%)' => '',
    'Pensum bis (%)' => '',
    'Anstellungsart' => '',
    'Beschreibung' => '',
    'Kontakt-E-Mail' => '',
    'Link zur Ausschreibung' => '',
    'Ort' => '',
    'Tarif' => '',
    'Status' => '',
    'Online bis' => '',

    // Meldungen
    'Dieses Inserat ist nicht (mehr) öffentlich.' => '',
    'Inserat nicht gefunden.' => '',
    'Keine Berechtigung für dieses Inserat.' => '',
    'Inserat gespeichert.' => '',
    'Dieser Tarif ist nicht (mehr) verfügbar.' => '',
    'Inserat eingereicht – es wird nach kurzer Prüfung veröffentlicht.' => '',
    'Inserat ist online.' => '',
    'Bezahlung ist noch nicht konfiguriert.' => '',
    'Die Zahlung konnte nicht gestartet werden.' => '',
    'Zahlung abgebrochen – dein Inserat ist als Entwurf gespeichert.' => '',
    'Inserat freigegeben und veröffentlicht.' => '',
    'Inserat abgelehnt.' => '',
    'Inserat auf «abgelaufen» gesetzt.' => '',
    'Du hast noch keine Inserate erstellt.' => '',

    // Detail / Erfassung
    'Zur Jobbörse' => '',
    'Bewerbung & Kontakt' => '',
    'Per E-Mail bewerben' => '',
    'Zur Ausschreibung' => '',
    'Tarif wählen & veröffentlichen' => '',
    'Neues Inserat' => '',
    'Inserat bearbeiten' => '',
    'Im nächsten Schritt wählst du den Tarif. Veröffentlicht wird das Inserat nach der Bezahlung (Lehrstellen sind gratis).' => '',

    // Checkout
    'Tarif wählen' => '',
    'Inserat: {title}' => '',
    'Aktuell sind keine Tarife verfügbar. Bitte später erneut versuchen.' => '',
    'Gratis' => '',
    'gemäss Stripe' => '',
    '{days} Tage online' => '',
    'Gratis veröffentlichen' => '',
    'Auswählen & bezahlen' => '',
    'Die Bezahlung läuft sicher über Stripe (gehostete Seite). Kartendaten erreichen unseren Server nicht.' => '',
    'Zahlung erhalten' => '',
    'Vielen Dank – deine Zahlung ist eingegangen.' => '',
    'Dein Inserat wird nach einer kurzen Prüfung freigeschaltet.' => '',
    'Dein Inserat wird soeben veröffentlicht und ist gleich online.' => '',
    'Zu meinen Inseraten' => '',
    'Bezahlen & veröffentlichen' => '',

    // Admin-Konfiguration
    'Jobbörse – Konfiguration' => '',
    'Der Stripe Secret Key und das Webhook-Secret gehören NICHT hierher, sondern in die Server-Konfiguration (params/.env). Hier nur die Price-IDs und Einstellungen.' => '',
    'Tarife (Stripe-Price-IDs)' => '',
    'Einstellungen' => '',
    'Stripe-Price-ID «Intro» (CHF 49)' => '',
    'Stripe-Price-ID «Basis»' => '',
    'Stripe-Price-ID «Top»' => '',
    'Intro-Stichdatum (letzter Tag)' => '',
    'Inserate vor Veröffentlichung prüfen' => '',
    'Laufzeit pro Inserat (Tage)' => '',
    'Bitte eine gültige Stripe-Price-ID (price_…) eingeben.' => '',

    // Bereiche (setting.*)
    'setting.spital' => '',
    'setting.spitex' => '',
    'setting.langzeit' => '',
    'setting.psychiatrie' => '',
    'setting.rehabilitation' => '',
    'setting.ausbildung' => '',

    // Anstellungsarten (type.*)
    'type.festanstellung' => '',
    'type.temporaer' => '',
    'type.lehrstelle' => '',

    // Status (status.*)
    'status.draft' => '',
    'status.pending_payment' => '',
    'status.pending_review' => '',
    'status.published' => '',
    'status.expired' => '',
    'status.archived' => '',
    'status.rejected' => '',
];
