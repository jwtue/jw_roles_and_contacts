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

**Code is fully English** (identifiers, table/column names, namespace) — only the editorial UI
labels in the `.xlf` files stay German (target audience: German-speaking editors).

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
Configuration/TCA/tx_jwrolesandcontacts_domain_model_person.php
Configuration/TCA/tx_jwrolesandcontacts_domain_model_role.php
ext_tables.sql                          # both tables, standard TYPO3 columns (tstamp/crdate/…)
Resources/Private/Language/locallang_db.xlf
ContentBlocks/ContentElements/role-and-contact-card/
  config.yaml                           # Relation field "roles", 1–3 roles
  templates/frontend.html
  language/labels.xlf
```

Master data (Person, Role) are **classic TCA tables**, not Content Blocks — they're master data,
not content elements, edited through the normal list module in an editorial folder. The content
element itself (the card/block with 1–3 roles) is a **Content Block**
(`friendsoftypo3/content-blocks`).

## TYPO3 version support: 12, 13, 14 (decision 2026-08-21)

**Important finding, correcting an earlier assumption:** `friendsoftypo3/content-blocks` is
**versioned in lockstep with the TYPO3 major**, not freely mixable. Source: fetched Packagist
metadata directly (`https://repo.packagist.org/p2/friendsoftypo3/content-blocks.json`), not just
doc prose (which was misleading on this point).

| TYPO3 | content-blocks | Maturity |
|---|---|---|
| 12.4 | `0.5.2`–`0.7.21` | **never reached 1.0** — effectively pre-release status |
| 13.4 | `1.0.0`–`1.6.3` | stable |
| 14.x | `2.0.0`–`2.4.8` | stable, current |

**Consequence/risk:** on TYPO3 v12 (current production state at both FWRW **and** FWTUE) this
extension depends on a content-blocks version that never went stable. That's a real risk, not a
cosmetic detail — verify explicitly before production use on v12 (stability, open issues in the
`0.7.x` line).

**Implementation:** `composer.json`/`ext_emconf.php` deliberately span **one** version range
across all three majors (`"friendsoftypo3/content-blocks": "^0.7.21 || ^1.6 || ^2.2"` and
equivalently in `ext_emconf.php`) instead of separate `release-v12`/`release-v13`/`release-v14`
branches as in [`einsatzstatistik`](../einsatzstatistik/AGENTS.md) — justified because this
extension (as of now) has **no PHP business logic** touching version-specific core APIs (pure
TCA/YAML/Fluid). Composer resolves the matching `content-blocks` version based on whichever
`typo3/cms-core` version is pinned in the target project.

**When to switch to the branch model:** once PHP classes (e.g. the `DataProcessor` mentioned
below) actually need version-specific behavior, switch to `release-v12`/`release-v13`/
`release-v14` like `einsatzstatistik`/`jw_feuser_manager`.

## Open / still to verify (scaffold stage, untested)

- **Core question:** does the Content Blocks `Relation` field `roles` also resolve the nested
  classic-TCA field `person` on the related record (including `person.image` as a ready-made FAL
  `FileReference`), or does it only return the raw `person` uid? Flagged as a TODO in
  `templates/frontend.html`. If not resolved automatically: add a small `DataProcessor` under
  `Classes/DataProcessing/` that pre-builds `roles` including the person and the per-field
  priority-fallback logic into a flat array (cleaner than the current chain of nested `f:if` in
  the template). Once that exists: re-evaluate the version question above.
- **Must be tested against a real 0.7.x content-blocks install on v12** before production use —
  see risk note above.
- No backend icon yet (`Resources/Public/Icons/tx_jwrolesandcontacts_domain_model_*.svg`) — TCA
  already references the paths, files are still missing.
- Not yet installed/tested on a real TYPO3 instance (schema migration, Content Block
  registration, rendering) — neither v12 nor v13/v14.
- Card CSS/layout not yet ported (old `.kontaktperson` markup from `fwrw-dce`/`fwtue-dce` as a
  reference, new classes `.roles-and-contacts`/`.roles-and-contacts-card` in the template) —
  both fire departments' sitepackage CSS still needs updating.
- Migrating the existing `Kontaktperson` DCE content (Rottweil + Tübingen DCE UID 8) to the new
  element is not yet planned/done.

## Conventions

- Namespace `JwTue\RolesAndContacts\`, extension key `jw_roles_and_contacts`.
- `declare(strict_types=1)`, PHP ^8.1, `final` by default, constructor DI — once there are PHP
  classes (currently: none, pure TCA + Content Block).
- All identifiers (tables, columns, namespace, files) English; UI labels in `.xlf` German.

## Repo / deploy

Published at [`github.com/jwtue/jw_roles_and_contacts`](https://github.com/jwtue/jw_roles_and_contacts)
(`main` branch), not yet released as a tagged Composer package. See the repo's git history for the
data-model/architecture/owner-account/naming decision process.
