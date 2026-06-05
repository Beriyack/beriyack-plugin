/**
 * Logique Javascript de l'administration du plugin Beriyack
 *
 * Gère les onglets, l'intégration du sélecteur de médias WordPress, les requêtes AJAX
 * de sauvegarde et de prévisualisation d'articles ainsi que les maquettes d'aperçu dynamique.
 */

jQuery(document).ready(function($) {
	'use strict';

	// Mise en cache des sélecteurs jQuery principaux
	var $navItems = $('.beriyack-nav-item');
	var $tabContents = $('.beriyack-tab-content');
	var $form = $('#beriyack-settings-form');
	var $submitBtn = $('#beriyack-submit-btn');
	var $loader = $('.spinner-loader');
	var $toast = $('#beriyack-toast');
	var $toastMsg = $toast.find('.toast-message');

	var mediaUploader;

	/**
	 * Initialise la page d'administration
	 */
	function init() {
		// Détection de l'onglet actif dans l'URL pour persister l'état après rechargement
		var currentUrl = new URL(window.location.href);
		var activeTab = currentUrl.searchParams.get('tab') || 'dashboard';
		
		if (currentUrl.searchParams.get('page') === 'beriyack-plugin-seo') {
			activeTab = 'seo';
		} else if (currentUrl.searchParams.get('page') === 'beriyack-plugin-security') {
			activeTab = 'security';
		}

		switchTab(activeTab, false);
		updateSocialPreviews();
		updateDashboardStatus();
	}

	/**
	 * Gère le changement d'onglet
	 */
	function switchTab(tabId, updateHistory) {
		$navItems.removeClass('active');
		$tabContents.removeClass('active');

		$navItems.filter('[data-tab="' + tabId + '"]').addClass('active');
		$('#tab-' + tabId).addClass('active');

		if (updateHistory !== false) {
			var currentUrl = new URL(window.location.href);
			currentUrl.searchParams.set('tab', tabId);
			window.history.pushState({ tab: tabId }, '', currentUrl.toString());
		}
	}

	// Écouteur sur le clic des onglets
	$navItems.on('click', function(e) {
		e.preventDefault();
		var tabId = $(this).data('tab');
		switchTab(tabId, true);
	});

	// Gère le retour en arrière / bouton suivant du navigateur (popstate)
	$(window).on('popstate', function(e) {
		var state = e.originalEvent.state;
		if (state && state.tab) {
			switchTab(state.tab, false);
		}
	});

	/**
	 * Intégration de la bibliothèque de médias WordPress (Uploader)
	 */
	$('#beriyack_upload_btn').on('click', function(e) {
		e.preventDefault();

		if (mediaUploader) {
			mediaUploader.open();
			return;
		}

		mediaUploader = wp.media({
			title: 'Choisir une image sociale par défaut',
			button: {
				text: 'Utiliser cette image'
			},
			multiple: false
		});

		mediaUploader.on('select', function() {
			var attachment = mediaUploader.state().get('selection').first().toJSON();
			
			$('#seo_fallback_img').val(attachment.url);
			$('#seo_fallback_img_preview').attr('src', attachment.url);
			$('.media-preview-container').removeClass('empty');
			$('#beriyack_clear_btn').show();

			// Met à jour la maquette d'aperçu
			updateSocialPreviews();
		});

		mediaUploader.open();
	});

	// Vider l'image sélectionnée
	$('#beriyack_clear_btn').on('click', function(e) {
		e.preventDefault();
		
		$('#seo_fallback_img').val('');
		$('#seo_fallback_img_preview').attr('src', '');
		$('.media-preview-container').addClass('empty');
		$(this).hide();

		// Met à jour la maquette d'aperçu
		updateSocialPreviews();
	});

	/**
	 * Mise à jour de la prévisualisation des partages sociaux
	 */
	function updateSocialPreviews() {
		// Si on prévisualise un article réel existant, on ne modifie pas les valeurs courantes
		if ($('#beriyack-preview-post-select').val() !== 'default') {
			return;
		}

		var imageUrl = $('#seo_fallback_img').val();
		var twitterUser = $('#seo_twitter_site').val().trim();

		// Rendu de l'image
		if (imageUrl) {
			$('#fb-preview-img').attr('src', imageUrl);
			$('.fb-img-placeholder').removeClass('empty');
			
			$('#tw-preview-img').attr('src', imageUrl);
			$('.tw-img-placeholder').removeClass('empty');
		} else {
			$('#fb-preview-img').attr('src', '');
			$('.fb-img-placeholder').addClass('empty');
			
			$('#tw-preview-img').attr('src', '');
			$('.tw-img-placeholder').addClass('empty');
		}

		// Rendu du compte Twitter
		if (twitterUser) {
			if (twitterUser.indexOf('@') !== 0) {
				twitterUser = '@' + twitterUser;
			}
			$('.twitter-mockup .mockup-badge').text('Twitter / X (' + twitterUser + ')');
		} else {
			$('.twitter-mockup .mockup-badge').text('Twitter / X (Summary Card)');
		}
	}

	// Écouteur de frappe sur le compte Twitter
	$('#seo_twitter_site').on('input', function() {
		updateSocialPreviews();
	});

	/**
	 * Écouteur sur le sélecteur d'article d'aperçu (Simulation)
	 */
	$('#beriyack-preview-post-select').on('change', function() {
		var postId = $(this).val();

		if (postId === 'default') {
			// Restaure le modèle par défaut avec les données générales du site
			$('#fb-preview-title, #tw-preview-title').text('Titre de votre contenu');
			$('#fb-preview-desc').text("Ceci est l'extrait de votre article ou page. Il s'affiche ici pour donner envie de cliquer.");
			$('#tw-preview-desc').text("Ceci est l'extrait de votre article ou page...");
			updateSocialPreviews();
			return;
		}

		// Requête AJAX pour récupérer le titre, l'extrait et l'image à la une de l'article choisi
		$.ajax({
			url: beriyack_ajax.ajax_url,
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'beriyack_get_post_preview',
				nonce: beriyack_ajax.nonce,
				post_id: postId
			},
			success: function(response) {
				if (response.success) {
					var data = response.data;
					$('#fb-preview-title, #tw-preview-title').text(data.title);
					$('#fb-preview-desc').text(data.excerpt);
					$('#tw-preview-desc').text(data.excerpt);

					// Priorité à l'image de l'article, sinon fallback sur l'image par défaut du plugin
					var imgUrl = data.image || $('#seo_fallback_img').val();
					if (imgUrl) {
						$('#fb-preview-img').attr('src', imgUrl);
						$('.fb-img-placeholder').removeClass('empty');
						
						$('#tw-preview-img').attr('src', imgUrl);
						$('.tw-img-placeholder').removeClass('empty');
					} else {
						$('#fb-preview-img').attr('src', '');
						$('.fb-img-placeholder').addClass('empty');
						
						$('#tw-preview-img').attr('src', '');
						$('.tw-img-placeholder').addClass('empty');
					}
				} else {
					showToast("Impossible de charger les données de l'article.", 'error');
				}
			},
			error: function() {
				showToast("Erreur lors de la récupération des données.", 'error');
			}
		});
	});

	/**
	 * Soumission du formulaire en AJAX
	 */
	$form.on('submit', function(e) {
		e.preventDefault();

		$submitBtn.prop('disabled', true);
		$loader.fadeIn(150);

		$.ajax({
			url: beriyack_ajax.ajax_url,
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'beriyack_save_settings',
				nonce: beriyack_ajax.nonce,
				form_data: $form.serialize()
			},
			success: function(response) {
				$submitBtn.prop('disabled', false);
				$loader.fadeOut(150);

				if (response.success) {
					showToast(response.data.message, 'success');
					updateDashboardStatus();
				} else {
					showToast(response.data.message || 'Une erreur est survenue lors de la sauvegarde.', 'error');
				}
			},
			error: function() {
				$submitBtn.prop('disabled', false);
				$loader.fadeOut(150);
				showToast('Erreur réseau ou serveur. Veuillez réessayer.', 'error');
			}
		});
	});

	/**
	 * Affiche une notification Toast temporaire
	 */
	function showToast(message, type) {
		$toast.removeClass('success error show');
		
		// Force le calcul de rendu CSS pour redémarrer l'animation de transition
		$toast[0].offsetWidth;

		$toast.addClass(type).addClass('show');
		$toastMsg.text(message);

		// Cache la notification après 4 secondes
		setTimeout(function() {
			$toast.removeClass('show');
		}, 4000);
	}

	/**
	 * Met à jour les compteurs du tableau de bord
	 */
	function updateDashboardStatus() {
		var seoEnabled = $('#seo_enable').is(':checked');
		$('#status-seo-txt').text(seoEnabled ? 'Activé & Opérationnel' : 'Désactivé');

		// Calcule le nombre de filtres de sécurité activés
		var activeSecurity = 0;
		var revValue = $('#sec_revisions').val();
		if (revValue !== '' && parseInt(revValue, 10) !== -1) {
			activeSecurity++;
		}
		if ($('#sec_rm_generator').is(':checked')) activeSecurity++;
		if ($('#sec_hide_login').is(':checked')) activeSecurity++;
		if ($('#sec_dis_xmlrpc').is(':checked')) activeSecurity++;
		if ($('#sec_dis_rest_api').is(':checked')) activeSecurity++;
		if ($('#sec_rm_ver').is(':checked')) activeSecurity++;
		if ($('#sec_dis_emojis').is(':checked')) activeSecurity++;

		$('#status-security-txt').text(activeSecurity + ' mesure(s) active(s)');
	}

	// Lancement de l'initialisation
	init();
});
