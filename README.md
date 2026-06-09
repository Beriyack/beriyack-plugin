# Beriyack Plugin

[![WordPress Version](https://img.shields.io/badge/WordPress-5.0+-blue.svg)](https://wordpress.org)
[![PHP Version](https://img.shields.io/badge/PHP-7.4+-green.svg)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

**Beriyack Plugin** est une extension WordPress complète, légère et performante conçue pour optimiser le **SEO social** et renforcer la **sécurité et la vitesse** de votre site web, le tout depuis un unique panneau d'administration moderne et intuitif en français.

Ce plugin est idéal pour les propriétaires de sites qui souhaitent une solution tout-en-un sans subir la lourdeur des grosses suites de sécurité ou de référencement.

---

## 🌟 Fonctionnalités principales

### 1. 📢 SEO Réseaux Sociaux & Métadonnées Générales
Ajoute automatiquement les métadonnées requises pour un affichage optimal sur Google et lors des partages sur Facebook, LinkedIn, Twitter/X, etc.
*   **Balise standard** : Génère la balise de référencement standard `<meta name="description" content="..." />` indispensable pour les moteurs de recherche.
*   **Balises Open Graph** : `og:title`, `og:description`, `og:image`, `og:url`, `og:locale`, `og:site_name` et `fb:app_id`.
*   **Balises Twitter Cards** : `twitter:card` (Summary Large Image), `twitter:title`, `twitter:description`, `twitter:image`, `twitter:image:alt`, `twitter:site` et `twitter:creator`.
*   **Génération dynamique contextuelle** : Le titre, l'extrait et l'image à la une (avec son texte alternatif `alt`) sont automatiquement récupérés selon le contexte (Page d'accueil, Articles, Pages, Catégories, Tags, Auteurs, Recherches et erreurs 404).
*   **Image sociale par défaut (Fallback)** : Définissez une image de secours dans l'administration si aucun visuel n'est associé à votre page.
*   **Sitemaps de taxonomies** : Sur les pages de catégories, d'étiquettes ou de taxonomies personnalisées, insère automatiquement une balise `<link rel="sitemap" ... />` pointant vers leur sitemap spécifique.
*   **Optimisation robots.txt** : Déclaration automatique de votre sitemap WordPress natif dans le fichier `robots.txt` virtuel.
*   **Directives d'indexation** : Empêche l'indexation par les moteurs de recherche (injection de `noindex, nofollow`) sur les pages de résultats de recherche et les pages d'erreur 404.

### 2. 🛡️ Sécurité & Vitesse (Optimisation système)
Protège votre installation des menaces courantes et allège les temps de chargement.
*   **Limitation des révisions** : Choix précis du nombre maximal de révisions conservées en base de données pour les articles et pages (`-1` pour illimité, `0` pour désactiver, ou une limite positive comme `10`).
*   **Masquage de la version WordPress** : Supprime la balise meta `generator` du code source pour compliquer la tâche des robots cherchant des failles spécifiques.
*   **Sécurisation de la connexion** : Remplacement des messages d'erreur de connexion trop précis par un message générique pour éviter la détection d'identifiants existants (User harvesting).
*   **Désactivation XML-RPC** : Bloque l'accès à `xmlrpc.php` et supprime l'en-tête `X-Pingback` pour contrer les attaques par force brute.
*   **Restriction API REST** : Bloque l'accès anonyme aux points de terminaison de l'API REST (`/wp-json/`) pour les utilisateurs non connectés.
*   **Nettoyage des ressources statiques** : Retire le paramètre de version `?ver=` à la fin des URL des fichiers CSS et Javascript.
*   **Désactivation des émojis** : Supprime les scripts et styles obsolètes de détection d'émojis natifs WordPress pour accélérer le temps de rendu des navigateurs modernes.

### 3. 🖥️ Tableau de bord d'administration haut de gamme
*   **Design Premium** : Interface asynchrone conçue avec une typographie moderne, des boutons d'options (Switches) conviviaux et une mise en page soignée.
*   **Sauvegarde AJAX ultra-fluide** : Enregistrement instantané des modifications sans aucun rechargement de page avec notifications Toast.
*   **Aperçu social en temps réel** : Zone de simulation dynamique montrant à quoi ressemblera le partage de votre site sur Facebook et Twitter/X.
*   **Simulateur d'articles** : Sélectionnez l'un de vos 10 derniers articles publiés pour charger dynamiquement ses données réelles (titre, extrait, image) dans le simulateur grâce à des requêtes AJAX sécurisées.

---

## ⚙️ Installation

1.  Téléchargez le dossier du plugin ou clonez le dépôt Git dans le répertoire `/wp-content/plugins/` de votre site WordPress :
    ```bash
    git clone https://github.com/beriyack/beriyack-plugin.git
    ```
2.  Dans votre tableau de bord WordPress, allez dans **Extensions > Extensions installées**.
3.  Trouvez **Beriyack Plugin** et cliquez sur **Activer**.
4.  Cliquez sur le lien rapide **Réglages** sous le nom du plugin (ou rendez-vous dans le menu latéral **Beriyack Plugin**) pour configurer vos options.

---

## ❓ FAQ (Foire Aux Questions)

#### Comment fonctionne la logique de sélection d'image pour les réseaux sociaux ?
Pour un article ou une page individuelle, le plugin récupère l'**image mise en avant** ainsi que son texte alternatif (pour la balise `twitter:image:alt`). Si aucune image mise en avant n'est définie, ou si vous êtes sur une page d'archive/accueil, le plugin applique l'**image par défaut (Fallback)** configurée dans vos réglages.

#### WordPress ne gère-t-il pas déjà les balises canonical ?
Oui. Depuis la version 4.6, WordPress intègre automatiquement et nativement la balise de lien canonique (`<link rel="canonical" href="..." />`) dans le `<head>`. Le plugin n'intervient pas sur ces balises afin d'éviter tout conflit avec le cœur de WordPress.

#### L'activation de la restriction API REST peut-elle bloquer mon site ?
Cette option empêche les accès anonymes à l'API REST de WordPress (`/wp-json/`). Elle est très utile pour sécuriser votre site. Cependant, si vous utilisez des services tiers ou des applications mobiles non connectés qui requièrent l'API REST de façon publique, vous devriez laisser cette option désactivée. Les administrateurs connectés conservent toujours un accès complet.

#### Comment le plugin gère-t-il la sécurité d'XML-RPC ?
Le plugin désactive le protocole XML-RPC via le filtre `xmlrpc_enabled` et supprime l'en-tête HTTP `X-Pingback`. Notez que si votre hébergeur (comme Infomaniak) bloque déjà `xmlrpc.php` au niveau serveur (erreur 403), cette option apporte une couche de sécurité applicative complémentaire et redondante tout à fait saine.

---

## 📝 Historique des versions (Changelog)

### Version 1.0.0 (Lancement initial)
*   Implémentation du système complet de SEO social (Open Graph et Twitter Cards) avec logique de fallback d'image.
*   Ajout de la limitation dynamique des révisions avec saisie numérique directe (`-1`, `0`, `X`).
*   Intégration des filtres de sécurité : masquage de la version WP, messages d'erreur de login génériques, désactivation XML-RPC, restriction de l'API REST aux utilisateurs connectés.
*   Nettoyage système : retrait du paramètre `?ver=` et désactivation complète des émojis.
*   Création de l'interface d'administration moderne sous forme d'onglets asynchrones avec enregistrement AJAX, notifications Toast et simulateur dynamique de partages sociaux (Facebook / Twitter).
*   Ajout du lien d'accès rapide "Réglages" sur la page des extensions WordPress.

---

## 📄 Licence

Ce projet est sous licence MIT - voir le fichier [LICENSE](file:///d:/Project/beriyack.ch/beriyack-plugin/LICENSE) pour plus de détails.
