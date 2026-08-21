# Roles and Contacts

TYPO3 extension providing **Person** and **Role** master data plus a Content Block content
element that renders 1–3 roles as a contact card, each with its current holder resolved.

Built to replace flat "one person = one contact" content elements (e.g. `t3/dce`-based ones) in
organizations where the reality is: a person can hold several roles, and contact data (email,
phone, fax, address) belongs primarily to the role rather than the person.

> **Status: alpha / scaffold.** Not yet installed or tested against a real TYPO3 instance. See
> [AGENTS.md](AGENTS.md) for the full architecture, data model, and open items.

## Requirements

- PHP ^8.1
- TYPO3 12.4, 13.4, or 14.x
- [`friendsoftypo3/content-blocks`](https://packagist.org/packages/friendsoftypo3/content-blocks)
  (installed transitively; the required version differs per TYPO3 major — see
  [AGENTS.md](AGENTS.md#typo3-version-support-12-13-14-decision-2026-08-21) for details and a
  known stability caveat on TYPO3 v12)

## Installation

```
composer require jwtue/jw_roles_and_contacts
```

## Data model

A **Role** (e.g. "Chairman", "Youth Officer") has at most one current holder, referencing a
**Person**. Contact fields (email, phone, mobile, fax, address) are set on the role; if a field
is empty there, it falls back to the person's own value. A role can be vacant (no holder).

See [AGENTS.md](AGENTS.md#data-model) for the full model and rationale.

## Content element

`role-and-contact-card` — place it on a page and select 1–3 role records. A single role renders
as a single contact card; 2–3 roles render as a shared block (e.g. chairman + deputy chairman
side by side).

## License

GPL-2.0-or-later
