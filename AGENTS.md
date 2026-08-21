# AGENTS.md — Roles and Contacts

Quelle der Wahrheit für Mitwirkende (Menschen & KI-Agenten). Kurz, verbindlich, aktuell halten.

## Zweck

Ersetzt die `Kontaktperson`-DCE-Inhaltselemente in `fwrw_dce`/`fwtue_dce` (`t3/dce`). Löst deren
Kernproblem: das alte Modell zwang **Person** und **Funktion** in einen einzigen flachen
Datensatz (Name + eine Funktionsbezeichnung + eine Adresse/Kontaktdaten), obwohl die Realität ist:

- Eine **Person** kann mehrere **Rollen/Funktionen** innehaben.
- Kontaktdaten (E-Mail/Telefon/Fax/Adresse) hängen **primär an der Rolle**, nur selten an der
  Person persönlich (Ausnahmefall).
- Zwei Rollen können sich Kontaktdaten teilen (z. B. Kommandant + Stellvertreter dieselbe
  E-Mail) — das ist redaktionell gelöst (Redundanz erlaubt), keine eigene "Kontakt"-Entität.
- Eine Rolle hat **höchstens einen** aktuellen Inhaber; "mehrere Personen zu einer Rolle" wird
  über zwei separate Rollen-Datensätze abgebildet, nicht über eine m:n-Relation.
- Eine Rolle kann **unbesetzt** sein (kein Inhaber) — das Element muss das anzeigen können.
- Keine Historie nötig, nur aktueller Stand.

Ist **nicht** Feuerwehr-spezifisch (Vereins-/Organisationsstruktur allgemein) → deshalb bei
`jwtue`, nicht `feuerwehr-commons` (bewusste Owner-Entscheidung 2026-08-21, gegen die sonst für
neue geteilte Extensions geltende Empfehlung Richtung `feuerwehr-commons`).

**Namensfindung** (2026-08-21): mehrere Anläufe — `funktionstraeger` (zu FW-nah), `officeholder`
(zu behördlich), diverse Kontakt-Kunstwörter (zu sperrig) — bis „Role and Contact" als
zusammengesetzter, aussprechbarer Name überzeugte. Extension-Key **`jw_roles_and_contacts`**,
Content-Block-Identifier bewusst kürzer/singularisch: `role-and-contact-card`.

**Code ist vollständig Englisch** (Bezeichner, Tabellen-/Spaltennamen, Namespace) — nur die
redaktionellen UI-Labels in den `.xlf`-Dateien bleiben Deutsch (Zielgruppe: deutschsprachige
Redakteure).

## Datenmodell

```
Person                              Role
  name                                title
  image (FAL, 0–1)                    person (FK → Person, 0–1, "aktueller Inhaber")
  address   (optional, Ausnahme)      address
  email     (optional, Ausnahme)      email
  phone     (optional, Ausnahme)      phone
  mobile    (optional, Ausnahme)      mobile
  fax       (optional, Ausnahme)      fax
```

Kein Join-Table nötig — `Role.person` ist ein einfaches Fremdschlüsselfeld (`select`,
`maxitems=1`, `foreign_table`), da eine Rolle nie mehrere gleichzeitige Inhaber hat. Eine Person
kann aber von beliebig vielen Rollen-Datensätzen referenziert werden.

**Anzeige-Priorität pro Kontaktfeld:** Wert der Rolle, falls gepflegt — sonst Fallback auf den
Wert der Person. Bild und Name kommen ausschließlich von der Person (keine Rolle ohne Inhaber
zeigt Bild/Name).

## Architektur / Aufbau

```
Configuration/TCA/tx_jwrolesandcontacts_domain_model_person.php
Configuration/TCA/tx_jwrolesandcontacts_domain_model_role.php
ext_tables.sql                          # beide Tabellen, Standard-TYPO3-Spalten (tstamp/crdate/…)
Resources/Private/Language/locallang_db.xlf
ContentBlocks/ContentElements/role-and-contact-card/
  config.yaml                           # Relation-Feld "roles", 1–3 Rollen
  templates/frontend.html
  language/labels.xlf
```

Stammdaten (Person, Role) sind **klassische TCA-Tabellen**, keine Content Blocks — sie sind
Stammdaten, keine Inhaltselemente, Pflege über das normale Listenmodul in einem Redaktionsordner.
Das Content-Element selbst (Karte/Block mit 1–3 Rollen) ist ein **Content Block**
(`friendsoftypo3/content-blocks`).

## TYPO3-Versionsunterstützung: 12, 13, 14 (Entscheidung 2026-08-21)

**Wichtiger Befund, gegen die eigene erste Annahme korrigiert:** `friendsoftypo3/content-blocks`
ist **im Lockstep pro TYPO3-Major versioniert**, nicht frei mischbar. Quelle: Packagist-Metadaten
direkt abgefragt (`https://repo.packagist.org/p2/friendsoftypo3/content-blocks.json`), nicht nur
Doku-Text (der an der Stelle irreführend war).

| TYPO3 | content-blocks | Reifegrad |
|---|---|---|
| 12.4 | `0.5.2`–`0.7.21` | **hat nie 1.0 erreicht** — de facto Vor-Release-Status |
| 13.4 | `1.0.0`–`1.6.3` | stabil |
| 14.x | `2.0.0`–`2.4.8` | stabil, aktuell |

**Konsequenz/Risiko:** Auf TYPO3 v12 (aktuell FWRW **und** FWTUE Produktivstand) hängt diese
Extension an einer Content-Blocks-Version, die nie stabil (1.0) wurde. Das ist ein reales Risiko,
kein rein kosmetisches Detail — vor dem produktiven Einsatz auf v12 explizit gegenprüfen
(Stabilität, offene Issues im `0.7.x`-Zweig).

**Umsetzung:** `composer.json`/`ext_emconf.php` spannen bewusst **einen** Versionsbereich über
alle drei Majors (`"friendsoftypo3/content-blocks": "^0.7.21 || ^1.6 || ^2.2"` bzw. entsprechend
in `ext_emconf.php`) statt getrennter `release-v12`/`release-v13`/`release-v14`-Branches wie bei
[`einsatzstatistik`](../einsatzstatistik/AGENTS.md) — gerechtfertigt, weil diese Extension
(Stand jetzt) **keine PHP-Geschäftslogik** enthält, die versionsspezifische Core-APIs anfasst
(reine TCA/YAML/Fluid). Composer löst die passende `content-blocks`-Version anhand der im
Zielprojekt gepinnten `typo3/cms-core`-Version automatisch auf.

**Wann umsteigen auf Branch-Modell:** Sobald PHP-Klassen (z. B. der unten erwähnte
`DataProcessor`) tatsächlich versionsspezifisches Verhalten brauchen, dann wie
`einsatzstatistik`/`jw_feuser_manager` auf `release-v12`/`release-v13`/`release-v14` wechseln.

## Offen / noch zu verifizieren (Scaffold-Status, ungetestet)

- **Kernfrage:** Löst das Content-Blocks-`Relation`-Feld `roles` verschachtelt auch das
  klassische TCA-Feld `person` der Zieltabelle auf (inkl. `person.image` als fertige
  FAL-`FileReference`), oder liefert es nur die rohe `person`-uid? Im Template
  (`templates/frontend.html`) als TODO markiert. Falls nicht automatisch aufgelöst: kleiner
  `DataProcessor` in `Classes/DataProcessing/` nachrüsten, der `roles` inkl. Person + der
  Prioritäts-Fallback-Logik pro Feld vorab zu einem flachen Array zusammenbaut (sauberer als die
  aktuelle Kette aus verschachtelten `f:if` im Template). Sobald dieser existiert: Versions-Frage
  (siehe oben) neu bewerten.
- **Auf v12 zwingend gegen echten 0.7.x-Content-Blocks-Stand testen**, bevor produktiv genutzt —
  siehe Risiko-Hinweis oben.
- Noch kein Backend-Icon (`Resources/Public/Icons/tx_jwrolesandcontacts_domain_model_*.svg`) —
  TCA referenziert die Pfade bereits, Dateien fehlen noch.
- Noch nicht auf einer echten TYPO3-Instanz installiert/getestet (Schema-Migration,
  Content-Block-Registrierung, Rendering) — weder v12 noch v13/v14.
- CSS/Layout der Karte noch nicht übertragen (altes `.kontaktperson`-Markup aus
  `fwrw-dce`/`fwtue-dce` als Vorlage, aber neue Klassen `.funktionstraeger`/`.funktionstraeger-karte`
  im Template — noch nicht mal an den neuen Namen angepasst, TODO — Sitepackage-CSS beider Wehren
  muss angepasst werden).
- Migration der bestehenden `Kontaktperson`-DCE-Inhalte (Rottweil + Tübingen DCE-UID 8) auf das
  neue Element ist noch nicht geplant/durchgeführt.

## Konventionen

- Namespace `JwTue\RolesAndContacts\`, Extension-Key `jw_roles_and_contacts`.
- `declare(strict_types=1)`, PHP ^8.1, `final` per Default, Constructor-DI — sobald es PHP-Klassen
  gibt (aktuell: keine, reine TCA + Content Block).
- Alle Bezeichner (Tabellen, Spalten, Namespace, Dateien) Englisch; UI-Labels in `.xlf` Deutsch.

## Repo / Deploy

Noch nicht auf GitHub angelegt, noch kein Composer-Paket veröffentlicht — lokales Scaffold im
Workspace. Zielort: `github.com/jwtue/jw_roles_and_contacts`, Composer-Paket
`jwtue/jw_roles_and_contacts`. Vor Veröffentlichung: mit Owner absprechen (siehe Git-Historie
dieses Repos für den Entscheidungsprozess Datenmodell/Architektur/Owner-Account/Namensfindung).
