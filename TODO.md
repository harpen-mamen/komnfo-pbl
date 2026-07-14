# TODO - Admin Dashboard operational

## Milestone 1: SubmissionBatchService refactor
- [ ] Create SubmissionSnapshotService (done)
- [ ] Create SubmissionWorkflowService (done)
- [ ] Create SubmissionPublishService (scaffold, done)
- [ ] Create SubmissionVersionService (scaffold, done)
- [x] Update SubmissionBatchService::submit to delegate snapshot/workflow while preserving public API
- [ ] Keep existing logic for moduleDefinitions, moduleQuery, createItems until later split


## Milestone 2+: Mixed batch + immutable snapshot + workflow/publication/dashboard/resources/API/GIS/etc.
- [ ] Continue with next milestones once refactor compiles and passes migrations/tests.

