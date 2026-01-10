=== Template Videos Interslide ===
Contributors: openai
Tags: video, template, brut_video, interslide
Requires at least: 6.0
Tested up to: 6.5
Stable tag: 1.0.0
Requires PHP: 8.0
License: GPLv2 or later

== Description ==
Plugin unifié qui fournit un nouveau format d'affichage pour toutes les pages du post type brut_video, ainsi qu'un CPT interslide_video complet avec templates dédiés.

== Installation ==
1. Uploader le dossier `template-videos-interslide` dans `/wp-content/plugins/`.
2. Activer le plugin via Extensions.
3. Vérifier l'affichage des posts brut_video.

== Usage ==
- brut_video : applique automatiquement le template du plugin.
- interslide_video : CPT + archive `/videos/` + shortcode `[interslide_video_grid]`.

== FAQ ==
= Le template brut_video ne s'affiche pas ? =
Assurez-vous que le thème ne surcharge pas `single-brut_video.php`.
