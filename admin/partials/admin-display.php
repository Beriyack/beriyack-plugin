<?php
/**
 * Rendu HTML de la page d'administration du plugin.
 *
 * Ce fichier structure le tableau de bord avec des onglets, des interrupteurs d'options,
 * des explications claires en français et une zone de simulation visuelle pour le SEO.
 *
 * @link       https://github.com/beriyack/beriyack-plugin
 * @since      1.0.0
 * @package    Beriyack_Plugin
 * @subpackage Beriyack_Plugin/admin/partials
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}
?>

<div class="wrap beriyack-wrap">
	<div class="beriyack-container">
		<!-- En-tête (Header) -->
		<header class="beriyack-header">
			<div class="beriyack-logo-area">
				<span class="dashicons dashicons-shield beriyack-logo-icon"></span>
				<div class="beriyack-title-group">
					<h1>Configuration Beriyack</h1>
					<span class="beriyack-version">v<?php echo esc_html( BERIYACK_PLUGIN_VERSION ); ?></span>
				</div>
			</div>
			<div class="beriyack-header-actions">
				<a href="https://github.com/beriyack/beriyack-plugin" target="_blank" class="beriyack-btn beriyack-btn-secondary">
					<span class="dashicons dashicons-external"></span> Dépôt GitHub
				</a>
			</div>
		</header>

		<!-- Structure principale (Main Layout) -->
		<div class="beriyack-layout">
			<!-- Barre de navigation latérale (Sidebar) -->
			<aside class="beriyack-sidebar">
				<nav class="beriyack-nav">
					<button class="beriyack-nav-item <?php echo ( $tab === 'dashboard' ) ? 'active' : ''; ?>" data-tab="dashboard">
						<span class="dashicons dashicons-dashboard"></span> Tableau de bord
					</button>
					<button class="beriyack-nav-item <?php echo ( $tab === 'seo' ) ? 'active' : ''; ?>" data-tab="seo">
						<span class="dashicons dashicons-share"></span> SEO Social
					</button>
					<button class="beriyack-nav-item <?php echo ( $tab === 'security' ) ? 'active' : ''; ?>" data-tab="security">
						<span class="dashicons dashicons-privacy"></span> Sécurité & Vitesse
					</button>
				</nav>
			</aside>

			<!-- Contenu des réglages (Main) -->
			<main class="beriyack-main">
				<form id="beriyack-settings-form" method="post" action="">
					
					<!-- Onglet 1 : Tableau de bord (Dashboard) -->
					<div class="beriyack-tab-content <?php echo ( $tab === 'dashboard' ) ? 'active' : ''; ?>" id="tab-dashboard">
						<div class="beriyack-welcome-card">
							<h2>Bienvenue dans votre espace Beriyack</h2>
							<p>Gérez et configurez la sécurité, les optimisations de vitesse et le SEO social pour l'ensemble de votre site WordPress à partir d'un seul et unique panneau d'administration moderne.</p>
							
							<div class="beriyack-status-grid">
								<div class="beriyack-status-card">
									<div class="status-icon success">
										<span class="dashicons dashicons-yes-alt"></span>
									</div>
									<div class="status-info">
										<h4>SEO Social</h4>
										<p id="status-seo-txt"><?php echo ( $options['seo_enable'] === '1' ) ? 'Activé & Opérationnel' : 'Désactivé'; ?></p>
									</div>
								</div>

								<div class="beriyack-status-card">
									<div class="status-icon info">
										<span class="dashicons dashicons-shield"></span>
									</div>
									<div class="status-info">
										<h4>Optimisations</h4>
										<p id="status-security-txt">Chargement...</p>
									</div>
								</div>
							</div>
						</div>

						<div class="beriyack-card">
							<h3>Guide de démarrage rapide</h3>
							<ul class="beriyack-guide-list">
								<li>
									<span class="step-badge">1</span>
									<div>
										<strong>SEO Social</strong> : Allez dans l'onglet correspondant, renseignez vos réseaux et uploadez une image par défaut.
									</div>
								</li>
								<li>
									<span class="step-badge">2</span>
									<div>
										<strong>Sécurité & Vitesse</strong> : Réglez la limitation de vos révisions pour garder votre base de données légère et activez les filtres de nettoyage.
									</div>
								</li>
								<li>
									<span class="step-badge">3</span>
									<div>
										<strong>Enregistrement fluide</strong> : Cliquez sur "Enregistrer les modifications" au bas de la page. Les réglages s'enregistrent en AJAX sans recharger !
									</div>
								</li>
							</ul>
						</div>
					</div>

					<!-- Onglet 2 : SEO Social -->
					<div class="beriyack-tab-content <?php echo ( $tab === 'seo' ) ? 'active' : ''; ?>" id="tab-seo">
						<div class="beriyack-card">
							<h3>Configuration Open Graph & Twitter Cards</h3>
							<p class="card-subtitle">Ces balises permettent aux réseaux sociaux d'afficher correctement vos pages lors des partages.</p>

							<!-- Option : Activer le SEO -->
							<div class="beriyack-form-group">
								<div class="control-header">
									<label class="control-label" for="seo_enable">Activer les balises SEO Sociales</label>
									<div class="beriyack-switch">
										<input type="checkbox" id="seo_enable" name="seo_enable" value="1" <?php checked( $options['seo_enable'], '1' ); ?>>
										<span class="switch-slider"></span>
									</div>
								</div>
								<p class="description">Génère automatiquement les métadonnées Open Graph (Facebook/LinkedIn) et Twitter Cards dans la balise <code>&lt;head&gt;</code> de chaque page.</p>
							</div>

							<!-- Option : Twitter Site -->
							<div class="beriyack-form-group input-group-row">
								<div class="control-info">
									<label class="control-label" for="seo_twitter_site">Nom d'utilisateur Twitter (Site)</label>
									<p class="description">Le compte Twitter associé au site internet (ex: <code>@moncompte</code> ou simplement <code>moncompte</code>).</p>
								</div>
								<div class="control-input">
									<input type="text" id="seo_twitter_site" name="seo_twitter_site" value="<?php echo esc_attr( $options['seo_twitter_site'] ); ?>" class="regular-text" placeholder="@nom_utilisateur">
								</div>
							</div>

							<!-- Option : Facebook App ID -->
							<div class="beriyack-form-group input-group-row">
								<div class="control-info">
									<label class="control-label" for="seo_fb_app_id">ID de l'application Facebook</label>
									<p class="description">Facultatif. Utile pour activer les statistiques de partage Facebook Insights (ex: <code>123456789012345</code>).</p>
								</div>
								<div class="control-input">
									<input type="text" id="seo_fb_app_id" name="seo_fb_app_id" value="<?php echo esc_attr( $options['seo_fb_app_id'] ); ?>" class="regular-text" placeholder="ID Application Facebook">
								</div>
							</div>

							<!-- Option : Image de fallback -->
							<div class="beriyack-form-group input-group-row">
								<div class="control-info">
									<label class="control-label">Image sociale par défaut (Fallback)</label>
									<p class="description">Cette image s'affichera lors du partage de la page d'accueil ou si l'article/page partagé ne contient pas d'image mise à la une.</p>
									<p class="example-info"><strong>Recommandé :</strong> Dimensions minimales de <code>1200 x 630px</code> pour un affichage optimal (format paysage 1.91:1).</p>
								</div>
								<div class="control-input media-control">
									<input type="hidden" id="seo_fallback_img" name="seo_fallback_img" value="<?php echo esc_url( $options['seo_fallback_img'] ); ?>">
									
									<div class="media-preview-container <?php echo empty( $options['seo_fallback_img'] ) ? 'empty' : ''; ?>">
										<img id="seo_fallback_img_preview" src="<?php echo esc_url( $options['seo_fallback_img'] ); ?>" alt="Aperçu image sociale par défaut">
										<div class="media-placeholder">
											<span class="dashicons dashicons-admin-media"></span>
											<p>Aucune image sélectionnée</p>
										</div>
									</div>

									<div class="media-buttons">
										<button type="button" id="beriyack_upload_btn" class="beriyack-btn beriyack-btn-primary">Choisir une image</button>
										<button type="button" id="beriyack_clear_btn" class="beriyack-btn beriyack-btn-danger" <?php echo empty( $options['seo_fallback_img'] ) ? 'style="display:none;"' : ''; ?>>Supprimer</button>
									</div>
								</div>
							</div>
						</div>

						<!-- Maquette d'aperçu dynamique réseaux sociaux (Social Preview) -->
						<div class="beriyack-card preview-card">
							<h3>Aperçu visuel de partage</h3>
							<p class="card-subtitle">Voici à quoi ressembleront vos liens lorsqu'ils seront partagés. Vous pouvez simuler le rendu avec les derniers articles de votre site.</p>
							
							<!-- Sélecteur d'article pour l'aperçu -->
							<div class="preview-selector-area">
								<label for="beriyack-preview-post-select"><strong>Simuler avec un contenu existant :</strong></label>
								<select id="beriyack-preview-post-select" class="beriyack-select inline-select">
									<option value="default">-- Données par défaut du site (Slogan & Image de fallback) --</option>
									<?php if ( ! empty( $recent_posts ) ) : ?>
										<?php foreach ( $recent_posts as $recent_post ) : ?>
											<option value="<?php echo esc_attr( $recent_post->ID ); ?>">
												<?php echo esc_html( get_the_title( $recent_post->ID ) ); ?> (<?php echo esc_html( $recent_post->post_type ); ?>)
											</option>
										<?php endforeach; ?>
									<?php endif; ?>
								</select>
							</div>

							<div class="preview-flex-container">
								<!-- Maquette Facebook -->
								<div class="mockup-item facebook-mockup">
									<div class="mockup-header">
										<span class="mockup-badge">Facebook</span>
									</div>
									<div class="mockup-body">
										<div class="fb-img-placeholder">
											<img id="fb-preview-img" src="" alt="Aperçu">
											<div class="img-missing-alert">Image par défaut ou mise à la une</div>
										</div>
										<div class="fb-content">
											<span class="fb-domain"><?php echo esc_html( wp_parse_url( home_url(), PHP_URL_HOST ) ); ?></span>
											<h4 class="fb-title" id="fb-preview-title">Titre de votre contenu</h4>
											<p class="fb-desc" id="fb-preview-desc">Ceci est l'extrait de votre article ou page. Il s'affiche ici pour donner envie de cliquer.</p>
										</div>
									</div>
								</div>

								<!-- Maquette Twitter / X -->
								<div class="mockup-item twitter-mockup">
									<div class="mockup-header">
										<span class="mockup-badge">Twitter / X (Summary Card)</span>
									</div>
									<div class="mockup-body">
										<div class="tw-img-placeholder">
											<img id="tw-preview-img" src="" alt="Aperçu">
											<div class="img-missing-alert">Image par défaut ou mise à la une</div>
										</div>
										<div class="tw-content">
											<span class="tw-domain"><?php echo esc_html( wp_parse_url( home_url(), PHP_URL_HOST ) ); ?></span>
											<h4 class="tw-title" id="tw-preview-title">Titre de votre contenu</h4>
											<p class="tw-desc" id="tw-preview-desc">Ceci est l'extrait de votre article ou page...</p>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>

					<!-- Onglet 3 : Sécurité & Vitesse -->
					<div class="beriyack-tab-content <?php echo ( $tab === 'security' ) ? 'active' : ''; ?>" id="tab-security">
						<div class="beriyack-card">
							<h3>Sécurisation & Nettoyage de WordPress</h3>
							<p class="card-subtitle">Activez ces options pour combler les failles de sécurité classiques et alléger le chargement de votre site.</p>

							<!-- Option : Nombre de révisions (Champs numérique direct) -->
							<div class="beriyack-form-group select-group-row">
								<div class="control-info">
									<label class="control-label" for="sec_revisions">Limiter les révisions d'articles et pages</label>
									<p class="description">WordPress enregistre une révision à chaque sauvegarde d'article. Limiter ce nombre évite de surcharger la base de données inutilement.</p>
									<p class="example-info"><strong>Règles :</strong> Saisissez <code>-1</code> pour conserver toutes les révisions (illimité), <code>0</code> pour les désactiver complètement, ou un nombre positif comme <code>10</code> ou <code>50</code>.</p>
								</div>
								<div class="control-input">
									<input type="number" id="sec_revisions" name="sec_revisions" value="<?php echo esc_attr( $options['sec_revisions'] ); ?>" class="beriyack-number-input" min="-1" step="1">
								</div>
							</div>

							<!-- Option : Retirer Generator -->
							<div class="beriyack-form-group">
								<div class="control-header">
									<label class="control-label" for="sec_rm_generator">Masquer la balise <code>generator</code> (Version de WordPress)</label>
									<div class="beriyack-switch">
										<input type="checkbox" id="sec_rm_generator" name="sec_rm_generator" value="1" <?php checked( $options['sec_rm_generator'], '1' ); ?>>
										<span class="switch-slider"></span>
									</div>
								</div>
								<p class="description">Retire la balise meta de la forme <code>&lt;meta name="generator" content="WordPress X.X.X"&gt;</code> dans le code source HTML. Masquer le numéro de version complique la tâche des robots cherchant des failles spécifiques.</p>
							</div>

							<!-- Option : Sécuriser les messages de connexion -->
							<div class="beriyack-form-group">
								<div class="control-header">
									<label class="control-label" for="sec_hide_login">Sécuriser les messages d'erreur de connexion</label>
									<div class="beriyack-switch">
										<input type="checkbox" id="sec_hide_login" name="sec_hide_login" value="1" <?php checked( $options['sec_hide_login'], '1' ); ?>>
										<span class="switch-slider"></span>
									</div>
								</div>
								<p class="description">Remplace les messages précis comme <em>"L'identifiant est incorrect"</em> par un message générique <em>"Identifiants de connexion incorrects"</em>. Cela empêche les robots de deviner des comptes existants (User harvesting).</p>
							</div>

							<!-- Option : Désactiver XML-RPC -->
							<div class="beriyack-form-group">
								<div class="control-header">
									<label class="control-label" for="sec_dis_xmlrpc">Désactiver XML-RPC</label>
									<div class="beriyack-switch">
										<input type="checkbox" id="sec_dis_xmlrpc" name="sec_dis_xmlrpc" value="1" <?php checked( $options['sec_dis_xmlrpc'], '1' ); ?>>
										<span class="switch-slider"></span>
									</div>
								</div>
								<p class="description">Bloque l'accès XML-RPC, une interface système ancienne très fréquemment ciblée par des attaques par force brute (Brute force amplification attacks).</p>
							</div>

							<!-- Option : Restreindre l'accès REST API -->
							<div class="beriyack-form-group">
								<div class="control-header">
									<label class="control-label" for="sec_dis_rest_api">Restreindre l'accès à l'API REST aux utilisateurs connectés</label>
									<div class="beriyack-switch">
										<input type="checkbox" id="sec_dis_rest_api" name="sec_dis_rest_api" value="1" <?php checked( $options['sec_dis_rest_api'], '1' ); ?>>
										<span class="switch-slider"></span>
									</div>
								</div>
								<p class="description"><strong>Attention :</strong> Bloque l'accès anonyme à l'API REST de WordPress (<code>/wp-json/</code>). Laissez cette option **désactivée** si vous prévoyez d'utiliser des scripts ou applications externes de synchronisation de contenu en mode anonyme.</p>
							</div>

							<!-- Option : Retirer Asset Version -->
							<div class="beriyack-form-group">
								<div class="control-header">
									<label class="control-label" for="sec_rm_ver">Retirer la version des ressources statiques</label>
									<div class="beriyack-switch">
										<input type="checkbox" id="sec_rm_ver" name="sec_rm_ver" value="1" <?php checked( $options['sec_rm_ver'], '1' ); ?>>
										<span class="switch-slider"></span>
									</div>
								</div>
								<p class="description">Retire le paramètre <code>?ver=X.Y.Z</code> à la fin des URL de vos fichiers CSS et Javascript. Cela permet de cacher les versions de vos plugins/thèmes et de régler certains anciens problèmes de mise en cache.</p>
							</div>

							<!-- Option : Désactiver Emojis -->
							<div class="beriyack-form-group">
								<div class="control-header">
									<label class="control-label" for="sec_dis_emojis">Désactiver les émojis WordPress natifs</label>
									<div class="beriyack-switch">
										<input type="checkbox" id="sec_dis_emojis" name="sec_dis_emojis" value="1" <?php checked( $options['sec_dis_emojis'], '1' ); ?>>
										<span class="switch-slider"></span>
									</div>
								</div>
								<p class="description">WordPress injecte automatiquement un script JS et du CSS sur chaque page pour supporter les émojis rétrocompatibles. Désactiver cette fonctionnalité allège chaque chargement de page (les navigateurs modernes supportent nativement les émojis).</p>
							</div>
						</div>
					</div>

					<!-- Actions de validation (Bouton d'enregistrement) -->
					<footer class="beriyack-footer">
						<button type="submit" id="beriyack-submit-btn" class="beriyack-btn beriyack-btn-save">
							<span class="dashicons dashicons-saved"></span> Enregistrer les modifications
						</button>
						<span class="spinner-loader" style="display:none;"></span>
					</footer>
				</form>
			</main>
		</div>
	</div>
</div>

<!-- Systèmes de notification Toast (Succès / Erreur) -->
<div id="beriyack-toast" class="beriyack-toast">
	<span class="toast-icon"></span>
	<span class="toast-message"></span>
</div>
