<p align="center">
  <img src="assets/icon-256.png" alt="VaultGate" width="112" height="112">
</p>

<h1 align="center">VaultGate</h1>

<p align="center">
  <b>Un seul mot de passe pour tout le site.</b><br>
  Une extension WordPress discrète et sans dépendance, qui garde un site privé pendant sa construction.
</p>

<p align="center">
  <a href="README.md">English</a> ·
  <a href="README.fr.md"><b>Français</b></a> ·
  <a href="README.es.md">Español</a>
</p>

<p align="center">
  <a href="https://github.com/zigmoon/passwordprotect/releases/latest"><b>⬇️ Télécharger</b></a> ·
  <a href="#installation">Installation</a> ·
  <a href="#la-page-de-garde">La page de garde</a> ·
  <a href="#sécurité">Sécurité</a> ·
  <a href="#développement">Développement</a>
</p>

<p align="center">
  <img alt="WordPress 6.5 – 7.1" src="https://img.shields.io/badge/WordPress-6.5%20%E2%86%92%207.1-0b0d10?style=flat-square">
  <img alt="PHP 8.2 – 8.5" src="https://img.shields.io/badge/PHP-8.2%20%E2%86%92%208.5-0b0d10?style=flat-square">
  <img alt="Version 1.1.0" src="https://img.shields.io/badge/version-1.1.0-0a66ff?style=flat-square">
  <img alt="Licence GPL-2.0-or-later" src="https://img.shields.io/badge/licence-GPL--2.0--or--later-0b0d10?style=flat-square">
  <img alt="163 assertions" src="https://img.shields.io/badge/tests-163%20assertions-0b0d10?style=flat-square">
  <img alt="Aucune dépendance" src="https://img.shields.io/badge/d%C3%A9pendances-aucune-0b0d10?style=flat-square">
</p>

<p align="center">
  <img src="docs/gate-light.png" alt="La page de garde VaultGate" width="880">
</p>

---

## Points clés

- **Un mot de passe, tout est fermé.** Pages, archives, flux, sitemaps, API REST, AJAX anonyme et XML-RPC — chaque surface se règle.
- **Votre logo, votre couleur.** Choisissez une image dans la médiathèque WordPress ; huit teintes prêtes à l’emploi ou une couleur libre. Noir par défaut, contraste calculé automatiquement.
- **Les polices dans l’extension.** Inter (la famille de SuperPhoto), Space Grotesk et JetBrains Mono sont livrées en WOFF2 dans l’extension. Aucun Google Fonts, aucun CDN, aucune requête sortante.
- **Rien que vous n’ayez écrit.** Aucune phrase marketing par défaut, aucun lien d’assistance, aucun pied de page. Un logo, un titre, un champ, un bouton.
- **Cookies signés, sans session.** Jeton HMAC-SHA256 sans état ; un clic révoque tous les visiteurs.
- **Durci par défaut.** `wp_hash_password()`, limitation par visiteur, CSP à nonce, `no-store`, `noindex`, `Permissions-Policy`.
- **Auto-hébergé et sobre.** Ni Composer, ni npm, ni build, ni statistiques, ni appel externe.
- **Anglais, français, espagnol.** Chaînes sources en anglais ; les deux traductions sont livrées compilées et vérifiées.
- **163 assertions.** Deux suites, exécutées sous PHP 8.3 et PHP 8.5, sans installation WordPress.

## La page de garde

| Clair | Sombre |
|---|---|
| ![Clair](docs/gate-light.png) | ![Sombre](docs/gate-dark.png) |

| Mobile (390 × 844) |
|---|
| <img src="docs/gate-mobile.png" alt="Mobile" width="330"> |

Une seule colonne, centrée à toutes les largeurs, à partir de 320 px. Un filet sépare l’en-tête du formulaire, un trait d’accent souligne le titre, le bouton est la seule surface colorée. Le même langage sert à l’écran de réglages :

| Réglages — logo, couleur, thème | Réglages — protection |
|---|---|
| ![Réglages de la page](docs/admin-appearance.png) | ![Réglages de protection](docs/admin-protection.png) |

## Installation

**Depuis l’archive**

1. Téléchargez `vaultgate-1.1.0.zip` depuis les [versions](https://github.com/zigmoon/passwordprotect/releases/latest).
2. WordPress → **Extensions → Ajouter → Téléverser une extension**.
3. Activez **VaultGate**.
4. Ouvrez **Réglages → VaultGate**.

**Depuis un dépôt cloné**

```bash
cp -r vaultgate /chemin/vers/wp-content/plugins/
wp plugin activate vaultgate
wp vaultgate password 'une longue phrase secrète'
wp vaultgate enable
```

Nécessite WordPress 6.5+ et PHP 8.2+. Aucune extension PHP au-delà d’une installation WordPress standard.

## Configuration

**Réglages → VaultGate** comporte cinq onglets.

| Onglet | Contenu |
|---|---|
| Protection | Interrupteur, nouveau mot de passe, « déconnecter tous les visiteurs », aide au déblocage |
| Page de garde | **Logo**, titre, message facultatif, **couleur d’accent**, **thème** |
| Accès | Durée de session, « rester connecté », capacité de contournement, IP autorisées, confiance au proxy, chemins exclus |
| Avancé | Basculements REST / flux / sitemaps / AJAX / XML-RPC, anti-force brute, journal, nettoyage à la désinstallation |
| Activité | Les 20 derniers événements, adresses IP anonymisées |

### Le logo

Choisissez une image avec **Choisir une image** : la médiathèque WordPress s’ouvre. VaultGate enregistre l’identifiant de la pièce jointe, pas une copie :

- le fichier vit dans `wp-content/uploads/AAAA/MM/`, comme n’importe quel média ;
- il revient avec vos sauvegardes de médias et reste réutilisable par un thème ;
- WordPress génère les tailles, la page de garde sert donc une image adaptée ;
- **Retirer** vide le champ et fait revenir la marque géométrique intégrée.

La page de garde limite le logo à 168 × 56 px CSS en conservant les proportions : un logo large fonctionne mieux. PNG, JPEG, WebP et GIF sont acceptés ; le SVG demande un filtre `upload_mimes`, comme partout dans WordPress.

### La couleur

Huit préréglages — **Noir d’encre** (par défaut), Ardoise, Bleu, Vert, Ambre, Rouge, Violet, Rose — ou le sélecteur natif. L’accent colore le bouton, l’anneau de focus et le trait sous le titre. La couleur du texte du bouton bascule entre blanc et noir selon la luminance relative WCAG, et un accent presque noir devient blanc sur le thème sombre pour que le bouton reste visible.

### Le thème

**Clair** (par défaut), **Sombre**, ou **Système** pour suivre le système d’exploitation du visiteur.

## Sécurité

- **Mot de passe** — `wp_hash_password()` / `wp_check_password()`. Jamais en clair, jamais journalisé, jamais transmis.
- **Cookie** — `expiration.signature`, HMAC-SHA256 sur le contexte, la version de session et l’expiration, avec `wp_salt('auth')` et le `COOKIEHASH` du site. Vérifié par `hash_equals()`.
- **Révocation** — changer le mot de passe, basculer la protection ou cliquer sur « déconnecter tous les visiteurs » incrémente `vaultgate_auth_version` : tous les cookies émis deviennent invalides immédiatement.
- **Attributs du cookie** — `HttpOnly`, `SameSite=Lax`, `Secure` en HTTPS (ou derrière un proxy de confiance), chemin et domaine WordPress.
- **Limitation** — fenêtre glissante par visiteur dans un transient dont la clé est un HMAC de l’adresse. Blocage avec compte à rebours sur le bouton.
- **Formulaire** — jeton signé à expiration, champ piège invisible, messages génériques, redirection `303` après traitement.
- **En-têtes** — `no-store`, `noindex, nofollow, noarchive`, `X-Frame-Options: DENY`, `nosniff`, `Referrer-Policy: no-referrer`, `Permissions-Policy` restrictive, CSP à nonce par requête.
- **Administration** — `manage_options`, nonce sur chaque action, validation serveur, sortie échappée.

### Limites

- **Fichiers statiques.** Les fichiers servis directement par le serveur (`wp-content/uploads/…`) ne passent jamais par WordPress. Utilisez une authentification serveur ou un stockage privé s’ils sont sensibles.
- **Cache de page.** La page de garde envoie `no-store` et vérifie le cookie à chaque requête, mais excluez le site d’un cache externe tant qu’il est verrouillé.
- **`wp-login.php` reste ouvert.** C’est nécessaire : c’est la porte de secours. VaultGate est une porte publique, pas un remplacement de l’authentification WordPress.
- **Un mot de passe partagé.** Ce n’est pas un gestionnaire d’utilisateurs ni de rôles.
- **Multisite.** Les réglages sont propres à chaque site.

## Accès d’urgence

1. `wp-login.php` n’est jamais filtré, et les sessions administrateur contournent toujours la page de garde.
2. Ou, sans tableau de bord, dans `wp-config.php` :
   ```php
   define( 'VAULTGATE_DISABLED', true );
   ```
3. Ou en WP-CLI :
   ```bash
   wp vaultgate status
   wp vaultgate disable
   wp vaultgate password 'une nouvelle phrase secrète'
   ```

Un visiteur peut se reverrouiller avec `https://exemple.com/?vaultgate=logout`.

## WP-CLI

```text
wp vaultgate status              État complet : protection, mot de passe, surfaces, capacité
wp vaultgate enable              Active la protection (un mot de passe doit exister)
wp vaultgate disable             Désactive, conserve le mot de passe
wp vaultgate password <mdp>      Définit le mot de passe (8 caractères minimum)
wp vaultgate password --stdin    Le lit sur l’entrée standard
wp vaultgate logout-all          Révoque tous les cookies émis
wp vaultgate log                 Affiche le journal d’activité
wp vaultgate log --clear         Le vide
```

## Filtres

| Filtre | Rôle |
|---|---|
| `vaultgate_should_gate` | Autoriser ou refuser la page de garde pour une requête |
| `vaultgate_client_ip` | Remplacer l’adresse détectée |
| `vaultgate_bypass_capabilities` | Ajouter ou retirer des capacités de contournement |
| `vaultgate_blocked_endpoints` | Modifier la liste des points d’entrée directs bloqués |
| `vaultgate_secure_cookie` | Forcer ou désactiver l’attribut `Secure` |
| `vaultgate_cookie_name` | Renommer le cookie |
| `vaultgate_swatches` | Modifier les préréglages de couleur |
| `vaultgate_throttled_log_events` | Choisir les événements limités à une entrée par minute |
| `vaultgate_log_size` | Nombre d’entrées conservées (20 par défaut) |

```php
// Laisser passer un webhook de paiement sans ouvrir tout le site.
add_filter(
	'vaultgate_should_gate',
	static function ( bool $gate ): bool {
		return str_starts_with( $_SERVER['REQUEST_URI'] ?? '', '/webhooks/stripe' ) ? false : $gate;
	}
);
```

## Développement

PHP 8.2+ et rien d’autre.

```bash
php tests/run.php                 # tests unitaires
php tests/smoke.php               # démarrage, guard, REST, rendu de l’admin
php tools/build-translations.php  # .pot + fr_FR + es_ES, compilés et vérifiés
pwsh -File tools/build-images.ps1 # tailles d’icônes depuis assets/icon-master.png
pwsh -File tools/build-zip.ps1    # dist/vaultgate-<version>.zip, vérifié par empreinte

# Aperçus de design (sans WordPress)
php -S 127.0.0.1:8450 tests/preview-router.php
#   /                        page de garde, thème clair
#   /?theme=dark             page de garde, thème sombre
#   /?accent=%230a66ff       page de garde, accent personnalisé
#   /?logo=1                 page de garde avec un logo d’exemple
#   /admin-preview.php       écran de réglages
```

Sortie attendue :

```text
140 passed, 0 failed
23 passed, 0 failed
```

Les suites utilisent un petit jeu de fonctions WordPress simulées (`tests/bootstrap.php`) : pas de base de données, pas de serveur web, pas d’installation WordPress. Elles tournent sous **PHP 8.3.36** et **PHP 8.5.12**.

## Arborescence

```text
passwordprotect/
├── vaultgate/                     l’extension, telle qu’elle s’installe
│   ├── vaultgate.php              en-tête WordPress + amorçage
│   ├── includes/                  réglages, règles d’accès, jetons, limitation, guard, admin, CLI
│   ├── templates/gate.php         la page de garde
│   ├── assets/css|js/             styles et scripts, sans CDN
│   ├── assets/fonts/              Inter, Space Grotesk, JetBrains Mono (WOFF2, OFL)
│   └── languages/                 .pot, fr_FR, es_ES (sources, compilés et vérifiés)
├── assets/                        sources d’icône, bannières, aperçu social
├── docs/                          captures utilisées ci-dessus
├── dist/                          archive installable versionnée
├── tests/                         suites de tests + aperçus de design
└── tools/                         traductions, images, archive
```

## Compatibilité

| | |
|---|---|
| WordPress | 6.5 → 7.1 (dernière stable au moment de la rédaction : 7.1.3) |
| PHP | 8.2 → 8.5, tests sous 8.3.36 et 8.5.12 |
| Base de données | Aucune table créée ; uniquement `wp_options` |
| Multisite | Oui, réglages par site |
| Extensions PHP | Aucune au-delà d’une installation WordPress standard |
| Navigateurs | Tout navigateur actuel ; la page de garde fonctionne sans JavaScript |

## FAQ

**Où va le logo ?**
Dans la médiathèque WordPress. VaultGate enregistre l’identifiant de la pièce jointe : le fichier est sauvegardé avec vos médias et réutilisable ailleurs.

**Comment supprimer la phrase sous le titre ?**
Il n’y en a pas par défaut. Le champ message de l’onglet **Page de garde** est facultatif ; sans lui, la page est une seule colonne sobre.

**Puis-je utiliser ma propre police ?**
Les trois familles sont embarquées dans l’extension. Pour en changer, remplacez les fichiers WOFF2 dans `vaultgate/assets/fonts/` et mettez à jour les règles `@font-face` dans `vaultgate/assets/css/`. Rien n’est chargé depuis un tiers.

**Est-ce compatible avec un cache de page ?**
Les en-têtes et la vérification du cookie sont corrects, mais excluez le site d’un cache externe tant qu’il est verrouillé.

**Les fichiers téléversés sont-ils protégés ?**
Non — ils contournent WordPress. Voir [Limites](#limites).

**Que se passe-t-il si je désactive l’extension ?**
Le site redevient public et tous les cookies émis sont révoqués : réactiver redemandera le mot de passe.

## Licence

GPL-2.0-or-later — voir [LICENSE](LICENSE). Copyright © 2026 zigmoon.

Signalements de sécurité : [SECURITY.md](SECURITY.md).
