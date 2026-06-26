# Training course performance snapshot — 2026-06-26

This branch records the verified production performance changes for the course page.

## Verified production source hashes

- `core/components/training/model/training/services/trainingprogress.class.php` — `5b2ab264d5d28a1f4a46c16a68fec927d1f8f059f1f15b89347b6a33ffc7ec36`
- `core/components/training/processors/web/_helpers.php` — `d729a27d15a22d75b2df43fd2ff562975fd79975fea44e720cca0ddaac4926a8`

## What changed

- Request-local GET memoization for repeated module access, lesson progress, lesson lists, active-video checks, module completion gates and module activity rows.
- Request-local GET memoization for repeated per-lesson video statistics.
- POST writes for progress, tests, practices, licences and access remain uncached.

## Validation

- Course-page document time improved from roughly 7 seconds to 966 ms in browser Network.
- Course cards, lesson states, access locks and progress display were checked after deployment.

## Files in this snapshot

- `training-course-performance.patch` — readable performance patch against the verified live baselines.

The repository `main` had older versions of these source files when this snapshot was created, so this branch documents the live, tested patch separately instead of overwriting unrelated source history.