# Training iOS PWA snapshot — 2026-06-27

This snapshot records the tested production rollout that lets the training site be opened from an iPhone Home Screen icon without Safari’s browser chrome.

## Production result

- Training PWA plugin: `Training PWA Head` (MODX plugin id `39`).
- Plugin event: `OnWebPagePrerender`.
- Scope: every rendered request under `/obuchenie/`.
- Manifest: `/assets/components/training/pwa/manifest.json`.
- Final manifest SHA-256: `40eb1a5923eafe57b00c672d2c4ff6bc02aedad6ea6c7de0f327f4b2a55e1005`.
- Apple touch icon: `/assets/components/training/pwa/icons/scanport-training-180-v2.png`.
- Generated PWA icons: `180×180`, `192×192`, `512×512` PNG.
- The iPhone test passed after adding `/obuchenie/` to the Home Screen and launching the icon.

## Behaviour

- In an ordinary iPhone Safari tab, the lesson player uses the existing pseudo-fullscreen layout: slides fill the available page area and the speaker video remains in the floating-video container.
- When launched from the Home Screen, iOS opens the training area in standalone mode without Safari’s address and bottom browser bars.
- The iOS system status bar may remain visible; that is controlled by iOS.
- No service worker was added. Lessons continue to use current server data and assets.

## Cleanup verified before snapshot

- `theme/js/app-training.js` was restored to its clean pre-diagnostic SHA-256:
  `4d55aa59a582f7ca0c3257e4cf73ce6f4cb7d0361c8c66a2bc0845173b6946dd`.
- `theme/js/training-fsdiag.js` was removed.
- The temporary `fsdiag` loader and native-video-fullscreen test are not part of the final state.

## Source files captured here

- `manifest.json` — exact final manifest content.
- `training-pwa-head.plugin.php` — source of the MODX plugin stored in the database.
- `generate-training-pwa-icons.php` — source used to generate the three production PNG icons.

## Deployment notes

The plugin is database-backed rather than a static MODX element. To reproduce the change on another environment:

1. Put the manifest and generated icons under `assets/components/training/pwa/`.
2. Create a MODX plugin from `training-pwa-head.plugin.php`.
3. Bind it to `OnWebPagePrerender`.
4. Refresh the MODX cache.
5. In iPhone Safari open `/obuchenie/`, choose **Share → Add to Home Screen**, then launch the new icon.
