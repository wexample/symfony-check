# Prerequisite gate: redirect to the form that is missing data, highlight the missing fields

Opened: 2026-10-09
Updated: 2026-10-09
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
