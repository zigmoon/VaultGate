/**
 * VaultGate — admin screen.
 *
 * Tabs, logo picker (WordPress media library) and accent swatches.
 * Every control degrades gracefully: without JavaScript the panels stay open,
 * the hidden logo field keeps its value and the native colour input works.
 */
(function () {
	'use strict';

	var root = document.querySelector('[data-vg-admin]');

	if (!root) {
		return;
	}

	root.classList.add('vg-admin--js');

	/* ── Tabs ───────────────────────────────────────────────────────────── */

	var tabs = Array.prototype.slice.call(root.querySelectorAll('[data-vg-tab]'));
	var panels = Array.prototype.slice.call(root.querySelectorAll('[data-vg-panel]'));

	if (tabs.length && panels.length) {
		var activate = function (id, focus) {
			var found = false;

			tabs.forEach(function (tab) {
				var on = tab.getAttribute('data-vg-tab') === id;
				tab.setAttribute('aria-selected', on ? 'true' : 'false');
				tab.tabIndex = on ? 0 : -1;

				if (on && focus) {
					tab.focus();
				}
			});

			panels.forEach(function (panel) {
				var on = panel.getAttribute('data-vg-panel') === id;
				panel.hidden = !on;

				if (on) {
					found = true;
				}
			});

			if (!found) {
				activate(tabs[0].getAttribute('data-vg-tab'), focus);
				return;
			}

			if (window.history && window.history.replaceState) {
				try {
					window.history.replaceState(null, '', '#vg-panel-' + id);
				} catch (error) {
					// Ignore URL restrictions in sandboxed previews.
				}
			}
		};

		tabs.forEach(function (tab, index) {
			tab.addEventListener('click', function () {
				activate(tab.getAttribute('data-vg-tab'), false);
			});

			tab.addEventListener('keydown', function (event) {
				var next = null;

				if (event.key === 'ArrowRight' || event.key === 'ArrowDown') {
					next = tabs[(index + 1) % tabs.length];
				} else if (event.key === 'ArrowLeft' || event.key === 'ArrowUp') {
					next = tabs[(index - 1 + tabs.length) % tabs.length];
				} else if (event.key === 'Home') {
					next = tabs[0];
				} else if (event.key === 'End') {
					next = tabs[tabs.length - 1];
				}

				if (next) {
					event.preventDefault();
					activate(next.getAttribute('data-vg-tab'), true);
				}
			});
		});

		var initial = (window.location.hash || '').replace('#vg-panel-', '');
		activate(initial || tabs[0].getAttribute('data-vg-tab'), false);
	}

	/* ── Show / hide a password ─────────────────────────────────────────── */

	Array.prototype.slice.call(root.querySelectorAll('[data-vg-reveal]')).forEach(function (button) {
		var target = button.getAttribute('aria-controls');
		var input = target ? document.getElementById(target) : null;

		if (!input) {
			return;
		}

		var showLabel = button.getAttribute('data-label-show') || 'Show password';
		var hideLabel = button.getAttribute('data-label-hide') || 'Hide password';

		button.addEventListener('click', function (event) {
			event.preventDefault();

			var show = input.type === 'password';

			input.type = show ? 'text' : 'password';
			button.setAttribute('aria-pressed', show ? 'true' : 'false');
			button.setAttribute('aria-label', show ? hideLabel : showLabel);
			button.setAttribute('title', show ? hideLabel : showLabel);

			var position = input.value.length;

			input.focus();

			try {
				input.setSelectionRange(position, position);
			} catch (error) {
				// Some input types do not expose a selection; ignore.
			}
		});
	});

	/* ── Accent colour ──────────────────────────────────────────────────── */

	var accent = root.querySelector('[data-vg-accent]');
	var accentValue = root.querySelector('[data-vg-accent-value]');
	var swatches = Array.prototype.slice.call(root.querySelectorAll('[data-vg-swatch]'));

	var syncSwatches = function (value) {
		swatches.forEach(function (swatch) {
			swatch.setAttribute('aria-pressed', swatch.getAttribute('data-vg-swatch') === value ? 'true' : 'false');
		});
	};

	if (accent && accentValue) {
		accent.addEventListener('input', function () {
			accentValue.textContent = accent.value;
			syncSwatches(accent.value);
		});
	}

	swatches.forEach(function (swatch) {
		swatch.addEventListener('click', function () {
			var value = swatch.getAttribute('data-vg-swatch');

			if (!accent || !value) {
				return;
			}

			accent.value = value;

			if (accentValue) {
				accentValue.textContent = value;
			}

			syncSwatches(value);
			accent.focus();
		});
	});

	/* ── Logo picker ────────────────────────────────────────────────────── */

	var logoInput = root.querySelector('[data-vg-logo-input]');
	var logoPick = root.querySelector('[data-vg-logo-pick]');
	var logoClear = root.querySelector('[data-vg-logo-clear]');
	var logoImage = root.querySelector('[data-vg-logo-image]');
	var logoPlaceholder = root.querySelector('[data-vg-logo-placeholder]');
	var logoPreview = root.querySelector('[data-vg-logo-preview]');

	if (!logoInput || !logoPick || !logoClear || !logoImage || !logoPlaceholder) {
		return;
	}

	var strings = window.vaultGateAdmin || {};
	var frame = null;

	var renderLogo = function (url) {
		if (url) {
			logoImage.src = url;
			logoImage.hidden = false;
			logoPlaceholder.hidden = true;
			logoClear.hidden = false;

			if (logoPreview) {
				logoPreview.classList.remove('is-empty');
			}

			return;
		}

		logoImage.removeAttribute('src');
		logoImage.hidden = true;
		logoPlaceholder.hidden = false;
		logoClear.hidden = true;

		if (logoPreview) {
			logoPreview.classList.add('is-empty');
		}
	};

	logoPick.addEventListener('click', function (event) {
		event.preventDefault();

		if (!window.wp || !window.wp.media) {
			return;
		}

		if (!frame) {
			frame = window.wp.media({
				title: strings.chooseLogo || 'Choose a logo',
				button: { text: strings.useLogo || 'Use this image' },
				library: { type: 'image' },
				multiple: false
			});

			frame.on('select', function () {
				var attachment = frame.state().get('selection').first().toJSON();
				var url = attachment.sizes && attachment.sizes.medium
					? attachment.sizes.medium.url
					: attachment.url;

				logoInput.value = String(attachment.id);
				renderLogo(url);
			});
		}

		frame.open();
	});

	logoClear.addEventListener('click', function (event) {
		event.preventDefault();
		logoInput.value = '0';
		renderLogo('');
		logoPick.focus();
	});
})();
