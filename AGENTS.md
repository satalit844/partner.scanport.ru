# AGENTS.md — project operating contract

This repository follows the owner's global project-working canon.

## Mandatory start

Before substantive work:

1. read this file;
2. read README/current status/worklog or equivalent project docs;
3. inspect the newest relevant Git commits/checkpoints;
4. search Git for accepted precedents for the same class of problem;
5. verify fresh runtime state when the task depends on current live/DEV facts.

Do not ask the owner to retell history already recorded in Git.

## Question -> Git -> answer -> Git

Every meaningful owner requirement, correction, constraint, acceptance/rejection, architecture decision, READ_ONLY finding, APPLY/rollback result, failed hypothesis and next point must be recorded in Git before the next meaningful stage.

Chat/model memory alone is never a project source of truth.

## Precedent memory

Precedent memory is mandatory and searchable in Git.

Before inventing a new solution, reuse an applicable accepted precedent. Record a new precedent with:

- problem/context;
- accepted solution;
- scope;
- verification/owner acceptance;
- constraints/protected behavior;
- superseded behavior, if any;
- DO NOT REPEAT notes;
- date and relevant commit/checkpoint.

A deviation from an applicable precedent requires a concrete reason recorded in Git.

For product/business/design decisions: newest explicit owner decision -> current Git canon -> accepted precedent -> older logs/chats.

For factual runtime state: fresh guarded runtime evidence -> verified Git checkpoint/status -> older evidence. If runtime contradicts Git, investigate and update Git.

## Safe change lifecycle

For persistent changes use the project's specific safe path. When applicable:

READ_ONLY -> PRECHECK -> BACKUP -> APPLY -> POSTFLIGHT -> ROLLBACK if needed -> Git checkpoint.

Do not turn a technical PASS into DONE when visual/business acceptance is still pending.

## Desktop Commander / Remote MCP

Desktop Commander is not part of the normal development contour.

Do not use it for ordinary HTTP/site checks, Git reads/writes, repository browsing, routine file inspection, normal CMS/site diagnostics or deployment when GitHub, project jobs/bridge, web access, attachments or local sandbox can do the work.

Desktop Commander is reserved only for bounded host-level recovery when the canonical Git/runner/bridge path itself is broken, for example runner/service/Windows recovery. Exceptional use must be recorded in Git. Its quota or billing must never be a project dependency.

## Secrets

Do not commit passwords, API keys, access tokens, private keys, service-account secrets, connection keys or production user data to Git.
