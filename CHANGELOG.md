# Journal des modifications

Toutes les versions notables de VaultGate sont documentées ici.
Le format suit [Keep a Changelog](https://keepachangelog.com/fr/1.1.0/) et le projet respecte le [versionnage sémantique](https://semver.org/lang/fr/).

## [1.0.0] — 27 octobre 2026

Première version publique.

### Ajouté

- **Protection de tout le site** par un mot de passe unique, activable depuis Réglages → VaultGate.
- **Page de garde autonome**, responsive et accessible : titre, message, logo, couleur d’accent avec contraste calculé, thème clair/sombre/automatique, lien d’assistance et note de pied de page.
- **Verrouillage** des pages, articles, archives, page 404, flux RSS/Atom, sitemaps, API REST, AJAX anonyme, XML-RPC et endpoints directs (commentaires, trackbacks, `wp-mail.php`, `wp-signup.php`, `wp-activate.php`, `admin-post.php`).
- **Cookie d’accès signé HMAC-SHA256**, `HttpOnly`, `SameSite=Lax`, `Secure` en HTTPS, avec révocation globale par version de session.
- **Hachage du mot de passe** via `wp_hash_password()` ; aucun mot de passe en clair, aucun mot de passe journalisé. Longueur minimale comptée en caractères UTF-8.
- **Limitation par visiteur** avec blocage temporaire, messages génériques, champ piège (honeypot) et **compte à rebours** sur le bouton tant que le blocage est actif.
- **Liste d’IP autorisées** IPv4/IPv6/CIDR et **chemins exclus** avec joker `*`, évalués relativement au dossier d’installation (sites en sous-dossier pris en charge).
- **Contournement** pour les utilisateurs connectés selon une capacité configurable ; les administrateurs contournent toujours.
- **En-têtes de sécurité** sur la page de garde : `no-store`, `noindex`, `X-Frame-Options: DENY`, `nosniff`, `Referrer-Policy`, `Permissions-Policy` et **CSP à nonce** autorisant explicitement les origines du site et du dossier d’extensions.
- **Journal d’activité** limité à 20 entrées, IP anonymisées, événements à haute fréquence limités à une entrée par minute.
- **Commandes WP-CLI** : `status`, `enable`, `disable`, `password`, `logout-all`, `log`.
- **Constante d’urgence** `VAULTGATE_DISABLED` et huit filtres d’intégration.
- **Traduction française complète**, catalogue `.pot`, fichier `.mo` compilé et vérifié.
- **Nettoyage optionnel** à la désinstallation, compatible multisite, incluant les transients.
- **139 assertions automatisées** (116 unitaires + 23 d’intégration), exécutées sous PHP 8.3 et PHP 8.5.
- **Identité visuelle** : icône vectorielle, déclinaisons 512/256/128/64 px, bannières 1544×500 et 772×250, aperçu social 1280×640.
- **Outillage reproductible** : génération des traductions, des images et de l’archive installable, avec vérification par empreinte SHA-256.

### Compatibilité

- WordPress 6.5 à 7.1.
- PHP 8.2 à 8.5, tests exécutés sous PHP 8.3.36 et PHP 8.5.12.
- Aucune dépendance Composer ou npm, aucune ressource externe, aucun appel réseau.

### Sécurité

- Comparaisons de signatures à temps constant (`hash_equals`), assainissement systématique des entrées, échappement systématique des sorties.
- Nonces et vérification de capacité sur chaque action d’administration.
- Aucune table créée : les données vivent dans `wp_options`.

[1.0.0]: https://github.com/zigmoon/passwordprotect/releases/tag/v1.0.0
