# Phase 2 — Household check-in

Implemented household selection using a normal GET form, followed by a POST form listing active members. All are initially selected; absent members can be unchecked. A small script updates the arriving count and capacity preview. Server validation remains authoritative.

The model locks the center and household, validates every selected member and active stay, then inserts individual stays in one transaction. Errors roll back the whole batch. Existing walk-ins, individual checkout and history remain available. No schema changes or duplicated counts.

Validation: PHP syntax checks passed for model, controller and view. Twelve MySQL checks passed in a uniquely named fictional database, removed afterward: five-person check-in, duplicate same/other center, capacity rejection, foreign household member, empty/duplicate selection, archived member, later registry change, individual checkout/history, partial household exact capacity, and walk-in regression. Browser layout and live checkbox interaction have not yet been visually checked.

Application changes are in the project worktree; the separate MAMP website checkout has not been updated by this phase.
