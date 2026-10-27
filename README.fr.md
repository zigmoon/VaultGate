<p align="center">
  <img src="assets/icon-256.png" alt="VaultGate" width="92" height="92">
</p>

<h1 align="center">VaultGate</h1>

<p align="center">
  <b>Un mot de passe pour tout le site — rien d’autre sur la page.</b><br>
  Une extension WordPress discrète, pour les sites en cours de construction.
</p>

<p align="center">
  <a href="README.md">English</a> ·
  <a href="README.fr.md"><b>Français</b></a> ·
  <a href="README.es.md">Español</a>
</p>

<p align="center">
  <a href="https://github.com/zigmoon/passwordprotect/releases/latest"><b>⬇ Télécharger</b></a> ·
  <a href="#installation">Installation</a> ·
  <a href="#ce-que-vous-obtenez">Ce que vous obtenez</a> ·
  <a href="#credits">Crédits</a>
</p>

<p align="center">
  <img alt="WordPress 6.5 → 7.1" src="https://img.shields.io/badge/WordPress-6.5%20%E2%86%92%207.1-0b0d10?style=flat-square">
  <img alt="PHP 8.2 → 8.5" src="https://img.shields.io/badge/PHP-8.2%20%E2%86%92%208.5-0b0d10?style=flat-square">
  <img alt="Version 1.1.1" src="https://img.shields.io/badge/version-1.1.1-0a66ff?style=flat-square">
  <img alt="181 assertions" src="https://img.shields.io/badge/tests-181%20assertions-0b0d10?style=flat-square">
  <img alt="Licence GPL-2.0-or-later" src="https://img.shields.io/badge/licence-GPL--2.0--or--later-0b0d10?style=flat-square">
</p>

![Réglages → VaultGate → Page de garde : logo de la médiathèque, nuances d’accent, thème, textes](docs/admin-appearance.png)

## Ce que vous obtenez

**Réglages → VaultGate** — cinq onglets, sans bruit : **Protection**, **Page de garde**, **Accès**, **Avancé**, **Activité**.

![Protection : interrupteur principal, mot de passe, déconnexion de tous les visiteurs](docs/admin-protection.png)

- **Un mot de passe, tout est fermé.** Pages, archives, flux, sitemaps, API REST, AJAX anonyme et XML-RPC — chaque surface a son interrupteur.
- **Votre logo, votre couleur.** Logo choisi dans la médiathèque (`wp-content/uploads`), huit nuances ou n’importe quel code hexadécimal, page de garde claire / sombre / système. Le contraste est calculé, jamais deviné.
- **Rien que vous n’ayez écrit.** Aucune phrase marketing, aucun lien de support, aucun pied de page sur la garde. Un logo, un titre, un champ, un bouton.
- **Aucun tiers.** Polices, icônes et styles sont dans l’extension. Pas de Google Fonts, pas de CDN, pas de mesure d’audience, aucune requête sortante.
- **Cookie signé, pas de session.** HMAC-SHA256, `HttpOnly`, `SameSite` ; un clic révoque tous les visiteurs.
- **Conçue pour être retirée.** Aucune table, aucun `.htaccess`, aucune règle de réécriture. Désactivez : le site est de nouveau public.

## La page de garde

| Claire | Sombre |
|---|---|
| ![Claire](docs/gate-light.png) | ![Sombre](docs/gate-dark.png) |

Une colonne, centrée dès 320 px, et elle fonctionne sans JavaScript.

## Installation

1. Téléchargez `vaultgate-1.1.1.zip` depuis les [versions](https://github.com/zigmoon/passwordprotect/releases/latest).
2. **Extensions → Ajouter → Téléverser une extension**, choisissez le ZIP, installez, activez.
3. **Réglages → VaultGate** : définissez le mot de passe, puis activez la protection.

Ou depuis un dépôt :

```bash
cp -r vaultgate /chemin/vers/wp-content/plugins/
wp plugin activate vaultgate
wp vaultgate password 'une longue phrase secrète'
wp vaultgate enable
```

WordPress 6.5+ et PHP 8.2+ — rien d’autre à installer. La première activation affiche une seule notification qui mène au champ mot de passe : elle se ferme.

![Première activation](docs/admin-welcome.png)

## Qualité

- **Dernières versions.** WordPress 6.5 → 7.1, PHP 8.2 → 8.5, aucun appel déprécié, aucune bibliothèque embarquée. Les traductions se chargent sur `init`, comme l’exige WordPress 6.7+.
- **181 assertions.** Deux suites (`php tests/run.php`, `php tests/smoke.php`) sur PHP 8.3 et 8.5, sans installation de WordPress.
- **Vérifiée sur une vraie installation.** Toutes les surfaces testées sur WordPress 7.1.3 / PHP 8.3 : accueil, articles, flux, sitemaps, REST, AJAX, XML-RPC, `robots.txt`, `wp-login.php`, administration, activation et suppression.
- **Back office responsive.** L’écran de réglages reste utilisable jusqu’à 360 px ; la garde est centrée à toutes les largeurs.

## Limites

- **Fichiers statiques.** `wp-content/uploads/...` est servi par le serveur web et n’atteint jamais WordPress. Utilisez l’authentification serveur pour les fichiers sensibles.
- **Cache de page.** La garde envoie `no-store` et vérifie le cookie à chaque requête ; excluez le site d’un cache de page tant qu’il est verrouillé.
- **`wp-login.php` reste ouvert.** C’est la porte de sortie : les sessions administrateur ne sont jamais bloquées.
- **Un mot de passe partagé.** Ce n’est pas un gestionnaire d’utilisateurs ou de rôles.

## La retirer

La désactivation rend le site public et révoque tous les cookies émis. La suppression retire les réglages, l’empreinte du mot de passe et le journal — **activé par défaut** — donc rien ne reste en base. En cas de blocage : `define( 'VAULTGATE_DISABLED', true );` dans `wp-config.php`, ou `wp vaultgate disable`.

## Crédits

Un plugin de **[2.13](https://2points13.fr)** · dev by **[zigmoon](https://zigmoon.com)**.

![La ligne du plugin dans la liste des extensions](docs/admin-plugins.png)

GPL-2.0-or-later — voir [LICENSE](LICENSE). Polices : Inter, Space Grotesk, JetBrains Mono (SIL Open Font License, embarquées).
