=== Interslide Video Formats ===
Contributors: openai
Tags: video, custom post type, template, interslide
Requires at least: 6.0
Tested up to: 6.5
Stable tag: 1.0.0
Requires PHP: 8.0
License: GPLv2 or later

== Description ==
Plugin complet pour créer un type de contenu vidéo "Interslide" avec un template front soigné, des métadonnées enrichies et des sections éditoriales (À suivre, Sur le même sujet, Pour aller plus loin).

== Installation ==
1. Uploader le dossier `interslide-video-formats` dans `/wp-content/plugins/`.
2. Activer le plugin via Extensions.
3. Aller dans "Interslide Vidéos" et créer une nouvelle vidéo.

== Usage ==
- CPT: `interslide_video` (slug public `/videos/`).
- Archive: `/videos/`.
- Shortcode: `[interslide_video_grid category="" topic="" limit="6" order="desc"]`.

== Configuration vidéo ==
1. Renseigner la source (MP4, YouTube, Vimeo, Embed).
2. Ajouter la durée, série, épisode, vidéo suivante et vidéos liées.
3. Ajouter des sources sous forme "Label | URL" (une ligne par source).

== FAQ ==
= Le template ne s'affiche pas ? =
Assurez-vous que le thème n'a pas de fichier `single-interslide_video.php`. Le plugin fournit un fallback.

= Comment supprimer les données à la désinstallation ? =
Activez l'option "Supprimer les données à la désinstallation" dans Réglages > Interslide Video Formats.

== Checklist de tests ==
- Desktop: vérifier mise en page, player, cards, liens de partage.
- Mobile: vérifier responsive et lisibilité.
- SEO: vérifier JSON-LD VideoObject et balises OpenGraph.
- Performance: vérifier que CSS/JS ne se chargent que sur pages vidéo.
