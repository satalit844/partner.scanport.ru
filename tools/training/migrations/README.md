# Training completion notification migration

Production implementation was applied and end-to-end tested on 2026-08-28.

Files in this directory:

- `2026-08-28_completion_notifications.php.gz` — reproducible repository migration that updates the Training course model/schema/processors/manager UI/progress hook and creates the DB fields/log table. It does **not** send email.
- The production notification service itself is committed at `core/components/training/model/training/services/trainingcompletionnotification.class.php`.

To inspect/run the migration, unpack it first:

```bash
gzip -dc 2026-08-28_completion_notifications.php.gz > 2026-08-28_completion_notifications.php
php -l 2026-08-28_completion_notifications.php
```

Then deploy the committed service file and run the unpacked migration from MODX Console on a matching Training codebase.

Checksums of the source used to build the archive:

- migration PHP SHA-256: `3da87c3297660a59e96c3a52297c64710bcb2a63d35389bac9ed04a3de860f22`
- production service SHA-256: `dced329ba64f75000006e66fc44d4c63c6ea0dc1a2b6c86385fb4eab30d48c0b`

The live production installation has already been verified by a real `in_progress -> completed` transition, successful `modPHPMailer` send, notification log `status=sent`, recipient-set match, and full restoration of the temporary test `training_user_courses` row.
