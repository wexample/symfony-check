# Prerequisite gate: redirect to the form that is missing data, highlight the missing fields

Opened: 2026-10-09
Updated: 2026-10-13
Author: agent:archeology-drawers

## Read this first — status of this todo

> **This is a proposal for discussion, not an order to code.** It comes from the 2026-10 "bottom of the drawers" archaeology pass. Read it, then discuss it with the owner: every design choice below is to be challenged and validated **before** any code is written. Do not start implementing on your own.
>
> - **Before anything else**: `/home/weeger/Desktop/WIP/WEB/WEXAMPLE/PACKAGES/PHP/.wex/local/package-agent-intro.md` — where you stand in the suite, what a package must look like, how to work, and how to judge a drawer todo.
> - Context: `/home/weeger/Desktop/WIP/WEB/WEXAMPLE/NETWORK/local/network/.wex/knowledge/readme/archeology/index.md.j2`, `sources.md.j2`, and the drawers page `/home/weeger/Desktop/WIP/WEB/WEXAMPLE/NETWORK/local/network/.wex/knowledge/readme/archeology/drawers-backend.md.j2`.
> - Safety: `NETWORK/local/network` runs on production data — read its code only, never run anything against it. Anonymize anything taken from it. Never copy secrets found in its history.

## What it was (2018-11 → 2020-06, deleted)

A "checker" declared conditions an action requires. When one failed, the user was redirected to the page where the data is entered, with the missing fields marked "required" and a message, then sent back to the original action after saving. Only rule: you could not create an invoice or quote until your organization had title, SIRET, bank details and address. Nothing equivalent exists in prod or in the packages today.

## Sources

- `git -C /home/weeger/Desktop/WIP/WEB/WEXAMPLE/NETWORK/archeo/repo.git show fe2c35a26^:project/src/Wex/BaseBundle/Checker/BaseChecker.php`
- `git -C /home/weeger/Desktop/WIP/WEB/WEXAMPLE/NETWORK/archeo/repo.git show fe2c35a26^:project/src/Checker/InvoiceAdd.php`
- `git -C /home/weeger/Desktop/WIP/WEB/WEXAMPLE/NETWORK/archeo/repo.git show fe2c35a26^:project/src/Wex/BaseBundle/Controller/Traits/CheckerController.php`
- history: `git -C /home/weeger/Desktop/WIP/WEB/WEXAMPLE/NETWORK/archeo/repo.git log --all --format='%h %ci %s' -- project/src/Checker project/src/Wex/BaseBundle/Checker` (`1485561e1`, `899909101` embedded forms support).

## Proposed shape (to challenge)

- In `symfony-check` (has `CheckerInterface` + `Finding`): a `Gate` = list of conditions, each pointing to a route/form and a list of field paths (dotted for embedded forms).
- A controller helper / attribute that runs the gate and redirects with `destination`.
- In `symfony-forms`: mark listed fields as required with an error message on render; redirect back to `destination` on success.

## Open questions

Is the "redirect to complete your profile" UX wanted, or rather an inline modal (symfony-loader) asking only for the missing fields?

## Done (2026-10-13)

Discussed with the owner, the proposed shape was changed: the gate does not rest on forms. The action is a subject of its own (`new InvoiceCreation($org)`), checked by ordinary checkers; what lifts the findings is a remedy given by a separate provider; the gate assumes no rendering. Answer to the open question: redirect or modal is decided by whoever catches the gate, not by the gate.

- `symfony-check` (`e01d8c4`, `4846e20`): `GateService::evaluate()` / `guard()`, `GateVerdict`, `GateClosedException` (403 if uncaught), `RemedyProviderInterface`, `RouteRemedy`, `AutomaticRemedyInterface` (applied by `guard()`), `AbstractRequiredFieldsChecker` (dotted paths, the legacy case), `GateClosedExceptionSubscriber` (page request redirected to the remedy with `gate` + `redirect`, json left with the 403), `GateHelper` (refuses an off-site destination).
- `symfony-forms` (`50d1db4`): a form reached by a gate points at the missing fields with its own texts (`assets/common/gate.trans.yml`), and once valid sends back to the stopped action.
- `symfony-design-system-demo` (`46852cd`): Forms → « Gated action », the full loop checked over HTTP in the showcase.

## Left

- `symfony-forms/composer.lock`: rewritten by `composer require` (stale lock), not committed.
- Raise the `wexample/symfony-check` constraint in forms and the demo once a version with the gate is published.
- The real invoice rule (title, SIRET, bank details, address) in `symfony-accounting`, on `AbstractRequiredFieldsChecker`.
