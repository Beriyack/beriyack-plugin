/**
 * Admin Javascript for Beriyack Plugin
 *
 * Manages tabs, WordPress media library popups, dynamic social preview cards, and AJAX form submissions.
 */

jQuery(document).ready(function($) {
	'use strict';

	// Cache frequently accessed elements
	var $navItems = $('.beriyack-nav-item');
	var $tabContents = $('.beriyack-tab-content');
	var $form = $('#beriyack-settings-form');
	var $submitBtn = $('#beriyack-submit-btn');
	var $loader = $('.spinner-loader');
	var $toast = $('#beriyack-toast');
	var $toastMsg = $toast.find('.toast-message');

	var mediaUploader;

	/**
	 * Initialize Settings Page
	 */
	function init() {
		// Enforce active tab based on load state (handles back/forward history navigation as well)
		var currentUrl = new URL(window.location.href);
		var activeTab = currentUrl.searchParams.get('tab') || 'dashboard';
		
		// If page is specifically the SEO page, switch to 'seo'
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
	 * Handles Tab Switching
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

	// Tab Click Handler
	$navItems.on('click', function(e) {
		e.preventDefault();
		var tabId = $(this).data('tab');
		switchTab(tabId, true);
	});

	// Window popstate event (Browser back/forward)
	$(window).on('popstate', function(e) {
		var state = e.originalEvent.state;
		if (state && state.tab) {
			switchTab(state.tab, false);
		}
	});

	/**
	 * Media Uploader Integration
	 */
	$('#beriyack_upload_btn').on('click', function(e) {
		e.preventDefault();

		// If the uploader object has already been created, reopen the dialog
		if (mediaUploader) {
			mediaUploader.open();
			return;
		}

		// Create the media frame
		mediaUploader = wp.media({
			title: 'Choisir une image sociale par défaut',
			button: {
				text: 'Utiliser cette image'
			},
			multiple: false
		});

		// When an image is selected in the media frame...
		mediaUploader.on('select', function() {
			var attachment = mediaUploader.state().get('selection').first().toJSON();
			
			$('#seo_fallback_img').val(attachment.url);
			$('#seo_fallback_img_preview').attr('src', attachment.url);
			$('.media-preview-container').removeClass('empty');
			$('#beriyack_clear_btn').show();

			// Update the live mockups
			updateSocialPreviews();
		});

		// Open the modal
		mediaUploader.open();
	});

	// Clear/Remove Fallback Image Handler
	$('#beriyack_clear_btn').on('click', function(e) {
		e.preventDefault();
		
		$('#seo_fallback_img').val('');
		$('#seo_fallback_img_preview').attr('src', '');
		$('.media-preview-container').addClass('empty');
		$(this).hide();

		// Update the live mockups
		updateSocialPreviews();
	});

	/**
	 * Live Social Card Previews
	 */
	function updateSocialPreviews() {
		var imageUrl = $('#seo_fallback_img').val();
		var twitterUser = $('#seo_twitter_site').val().trim();

		// Image preview injection
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

		// Update Twitter mockup tag / handle if present
		if (twitterUser) {
			// Ensure it has @ symbol
			if (twitterUser.indexOf('@') !== 0) {
				twitterUser = '@' + twitterUser;
			}
			$('.twitter-mockup .mockup-badge').text('Twitter / X (' + twitterUser + ')');
		} else {
			$('.twitter-mockup .mockup-badge').text('Twitter / X (Summary Card)');
		}
	}

	// Update preview dynamically when typing twitter handle
	$('#seo_twitter_site').on('input', function() {
		updateSocialPreviews();
	});

	/**
	 * AJAX Form Submission
	 */
	$form.on('submit', function(e) {
		e.preventDefault();

		// Enter loading state
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
	 * Helper: Shows Toast Notification
	 */
	function showToast(message, type) {
		$toast.removeClass('success error show');
		
		// Force DOM reflow to restart CSS animation transition
		$toast[0].offsetWidth;

		$toast.addClass(type).addClass('show');
		$toastMsg.text(message);

		// Hide after 4 seconds
		setTimeout(function() {
			$toast.removeClass('show');
		}, 4000);
	}

	/**
	 * Helper: Updates Dashboard status stats
	 */
	function updateDashboardStatus() {
		var seoEnabled = $('#seo_enable').is(':checked');
		$('#status-seo-txt').text(seoEnabled ? 'Activé & Opérationnel' : 'Désactivé');

		// Count active security measures
		var activeSecurity = 0;
		if ($('#sec_revisions').val() !== 'disabled') activeSecurity++;
		if ($('#sec_rm_generator').is(':checked')) activeSecurity++;
		if ($('#sec_hide_login').is(':checked')) activeSecurity++;
		if ($('#sec_dis_xmlrpc').is(':checked')) activeSecurity++;
		if ($('#sec_dis_rest_api').is(':checked')) activeSecurity++;
		if ($('#sec_rm_ver').is(':checked')) activeSecurity++;
		if ($('#sec_dis_emojis').is(':checked')) activeSecurity++;

		$('#status-security-txt').text(activeSecurity + ' mesure(s) active(s)');
	}

	// Trigger initialization
	init();
});
