# AGENTS.md — Roles and Contacts

Source of truth for contributors (humans & AI agents). Keep it short, binding, and up to date.

## Purpose

Replaces the `Kontaktperson` DCE content elements in `fwrw_dce`/`fwtue_dce` (`t3/dce`). Fixes
their core problem: the old model forced **Person** and **Function/role** into a single flat
record (name + one role title + one set of contact data), even though reality looks like this:

- A **Person** can hold several **Roles**.
- Contact data (email/phone/fax/address) belongs **primarily to the role**, only rarely to the
  person personally (exception case).
- Two roles can share contact data (e.g. commander + deputy share one email address) — handled
  editorially (redundancy is fine), no separate "Contact" entity needed.
- A role has **at most one** current holder; "several people for one role" is modeled as two
  separate role records, not as an m:n relation.
- A role can be **vacant** (no holder) — the element must be able to display that.
- No history needed, current state only.

Not fire-department-specific (general club/organization structure) → hence hosted under `jwtue`,
not `feuerwehr-commons` (deliberate owner decision, 2026-08-21, against the otherwise-standing
recommendation to put new shared extensions under `feuerwehr-commons`).

**Naming** (2026-08-21): several rounds — `funktionstraeger` (too FD-specific), `officeholder`
(too bureaucratic-sounding), various contact-themed portmanteaus (too clunky) — before "Role and
Contact" as a compound, pronounceable name won out. Extension key **`jw_roles_and_contacts`**,
Content Block identifier deliberately shorter/singular: `role-and-contact-card`.

**Code is fully English** (identifiers, table/column names, namespace). Editorial UI labels are
bilingual: `.xlf` files carry English as the XLIFF source language (TYPO3 convention — the
unprefixed file must be English), with a `de.*.xlf` sibling providing the German `<target>`
translation (primary audience: German-speaking editors, but the extension is meant to be usable
without German too).

## Data model

```
Person                              Role
  name                                title
  image (FAL, 0–1)                    person (FK → Person, 0–1, "current holder")
  address   (optional, exception)     address
  email     (optional, exception)     email
  phone     (optional, exception)     phone
  mobile    (optional, exception)     mobile
  fax       (optional, exception)     fax
```

No join table needed — `Role.person` is a simple foreign-key field (`select`, `maxitems=1`,
`foreign_table`), since a role never has more than one simultaneous holder. A person can, however,
be referenced by any number of role records.

**Display priority per contact field:** the role's value if set — otherwise fall back to the
person's value. Image and name come exclusively from the person (a role without a holder shows
no image/name).

## Architecture / layout

```
Classes/DataProcessing/RoleProcessor.php        # resolves role.person, see below — required
Configuration/TCA/tx_jwrolesandcontacts_domain_model_person.php
Configuration/TCA/tx_jwrolesandcontacts_domain_model_role.php
ext_tables.sql                          # both tables, standard TYPO3 columns (tstamp/crdate/…)
ext_localconf.php                       # registers RoleProcessor as dataProcessing step 20
Resources/Private/Language/locallang_db.xlf     # TCA labels, English (source)
Resources/Private/Language/de.locallang_db.xlf  # TCA labels, German (translation)
ContentBlocks/ContentElements/role-and-contact-card/
  config.yaml                           # Relation field "roles", 1–3 roles — v13/v14 (1.x/2.x)
  EditorInterface.yaml                  # identical content — v12 (0.7.x) reads this filename instead
  templates/frontend.html               # v13/v14 (1.x/2.x)
  Source/Default/Frontend.html          # identical content — v12 (0.7.x) reads this path instead
  language/labels.xlf                   # Content Block labels, English (source)
  language/de.labels.xlf                # Content Block labels, German (translation)
```

Master data (Person, Role) are **classic TCA tables**, not Content Blocks — they're master data,
not content elements, edited through the normal list module in an editorial folder. The content
element itself (the card/block with 1–3 roles) is a **Content Block**
(`friendsoftypo3/content-blocks`).

## Verified 2026-08-21: tested end-to-end on real TYPO3 v12 / v13 / v14 installs

Installed via composer (path repo), activated, schema applied, seeded a Person + two Roles (one
with an empty role-level email to exercise the person fallback, one vacant to exercise the vacancy
case), rendered the real frontend and inspected the HTML. **All three versions now render
correctly** — role-level fields, person-fallback, and the vacant-role case all work as designed.
Getting there required fixing several real, version-specific gaps:

- **Content Blocks is versioned in lockstep with the TYPO3 major**, confirmed via Packagist
  metadata (`https://repo.packagist.org/p2/friendsoftypo3/content-blocks.json`), not just doc
  prose (which was misleading):

  | TYPO3 | content-blocks tested | Maturity |
  |---|---|---|
  | 12.4.23 | `0.7.21` | **pre-1.0** — several real rough edges found below |
  | 13.4.33 | `1.6.3` | stable, zero extra fixes needed |
  | 14.3.5 | `2.4.8` | stable, zero extra fixes needed |

- **`0.7.x` (v12) expects a differently-named/placed definition file:** `EditorInterface.yaml`
  at the Content Block root, not `config.yaml`. Confirmed via
  `vendor/friendsoftypo3/content-blocks/Documentation/Definition/EditorInterface/Index.rst` in the
  installed package. **Fix:** both files now ship with identical content; each content-blocks
  major reads only the one it understands, the other is silently ignored.
- **`0.7.x` (v12) expects the Fluid template at `Source/Default/Frontend.html`**, not
  `templates/frontend.html`. **Fix:** both paths now ship with identical template content.
- **`0.7.x` (v12) `Relation` field type requires an explicit `allowed` key** — `foreign_table`
  alone is not enough; `RelationResolver::processRelation()` reads
  `$tcaFieldConfig['config']['allowed']` with no fallback, throwing "Undefined array key" if
  missing, even though `foreign_table` alone is documented as sufficient for later versions.
  **Fix:** both YAML files now set `allowed` and `foreign_table` to the same value; harmless on
  1.x/2.x too.
- **Core question resolved, definitively, not just guessed:** Content Blocks resolves its own
  `Relation` field (`roles`) into one row per related record, but does **not** recursively resolve
  a *further* TCA relation on that related record (`role.person`) — confirmed identically on all
  three tested versions. Root cause (traced through
  `ContentBlockDataDecorator`/`RelationResolver` source): recursive resolution only kicks in when
  the *related* table is itself a Content-Blocks-defined table; ours (`..._domain_model_role`) is
  plain classic TCA by design, so Content Blocks builds a "fake" wrapper exposing only the raw row
  — `person` stays the raw uid. **Fix:** `Classes/DataProcessing/RoleProcessor.php`, registered via
  `ext_localconf.php` at `tt_content.jwtue_roleandcontactcard.dataProcessing.20` (after Content
  Blocks' own step `10`), fetches the referenced Person rows and overwrites `role.person` with the
  resolved row (incl. FAL-resolved `image`). Works unchanged across all three versions — the
  `ContentBlockData` object it mutates (`extends \stdClass`, magic `__get()` backed by an internal
  `_processed` array) behaves identically on 0.7.x/1.6.x/2.4.x.
  Uses `TYPO3\CMS\Core\Database\Connection::PARAM_INT_ARRAY` (not
  `Doctrine\DBAL\ArrayParameterType`) specifically because it is TYPO3 core's own version-stable
  abstraction over the DBAL 3→4 change between v12 and v13+ — confirmed necessary since v12 ships
  DBAL 3.10, where the DBAL-native enum isn't the intended API for this.
- **No CLI schema-migration command exists in v12 or v13** (`database:updateschema` does not
  exist in either — checked via `typo3 list`); schema had to be applied by hand (or via the
  Install Tool's web UI "Analyze Database Structure" in a real deployment). **v14 changed this:**
  `extension:setup` there is documented as "Set up extensions **and perform database
  migrations**" and did in fact create both tables and the `tt_content` column automatically —
  no manual SQL needed on v14. Column type for the relation field also differs: `varchar(255)` on
  v12/v13 vs. `longtext` on v14 (both hold the same comma-separated uid-list format; content-blocks
  still uses plain TCA `type=group` storage on all three, not a JSON/MM format change).
- Not related to the extension, but hit during testing and worth recording: on **Windows**,
  `friendsoftypo3/content-blocks` 0.7.x's asset-publishing step
  (`ContentBlockLoader::publishAssets()`) unconditionally calls PHP `symlink()` with a *relative*
  target for every loaded Content Block, even one with an empty `Assets/` folder. PHP-on-Windows
  resolves that relative target against the process's current working directory instead of the
  link's own location, so it reliably fails with "No such file or directory" — a Windows/PHP
  quirk, not a real deployment issue (production is Linux). No config flag exists in 0.7.x to skip
  it. Worked around locally by pre-creating the expected `public/_assets/<md5(name)>` target
  directory before running `extension:setup` (the loader skips the symlink call if the target
  already exists) — not something the extension itself needs to handle.

## Remaining open items

- No backend icon yet (`Resources/Public/Icons/tx_jwrolesandcontacts_domain_model_*.svg`) — TCA
  already references the paths, files are still missing.
- Card CSS/layout not yet ported (old `.kontaktperson` markup from `fwrw-dce`/`fwtue-dce` as a
  reference, new classes `.roles-and-contacts`/`.roles-and-contacts-card` in the template) —
  both fire departments' sitepackage CSS still needs updating.
- Migrating the existing `Kontaktperson` DCE content (Rottweil + Tübingen DCE UID 8) to the new
  element is not yet planned/done.
- Test data (1 Person, 2 Roles, 1 content element) was left in place on page 1 ("Home") of all
  three local `C:\xampp\htdocs\typo3-v{12,13,14}` instances used for this verification — remove if
  those instances are needed clean for other purposes.

## TYPO3 version support: 12, 13, 14

`composer.json`/`ext_emconf.php` deliberately span **one** version range across all three majors
(`"friendsoftypo3/content-blocks": "^0.7.21 || ^1.6 || ^2.2"` and equivalently in
`ext_emconf.php`) instead of separate `release-v12`/`release-v13`/`release-v14` branches as in
[`einsatzstatistik`](../einsatzstatistik/AGENTS.md) — justified because this extension has **no
PHP business logic depending on version-specific core APIs** (the one PHP class, `RoleProcessor`,
was verified above to behave identically across all three `content-blocks` majors; its one
version-sensitive detail, the DBAL array-parameter type, is already handled through TYPO3 core's
own stable `Connection::PARAM_INT_ARRAY` abstraction rather than a DBAL-version-specific one).
Composer resolves the matching `content-blocks` version based on whichever `typo3/cms-core`
version is pinned in the target project — confirmed working, not just theoretical: 0.7.21 / 1.6.3
/ 2.4.8 were each picked correctly during the three real installs above.

**When to switch to the branch model:** if a future change needs genuinely different behavior per
major (not just a dual-file trick like `EditorInterface.yaml`/`config.yaml`), switch to
`release-v12`/`release-v13`/`release-v14` like `einsatzstatistik`/`jw_feuser_manager`.

## Conventions

- Namespace `JwTue\RolesAndContacts\`, extension key `jw_roles_and_contacts`.
- `declare(strict_types=1)`, PHP ^8.1, `final` by default, constructor DI — once there are PHP
  classes (currently: none, pure TCA + Content Block).
- All identifiers (tables, columns, namespace, files) English; UI labels bilingual (English
  source `.xlf`, German `de.*.xlf` translation) — new labels always get both.

## Repo / deploy

Published at [`github.com/jwtue/jw_roles_and_contacts`](https://github.com/jwtue/jw_roles_and_contacts)
(`main` branch), not yet released as a tagged Composer package. See the repo's git history for the
data-model/architecture/owner-account/naming decision process.
