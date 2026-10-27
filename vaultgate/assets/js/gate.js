/**
 * VaultGate — gate page behaviour.
 * Progressive enhancement only: the password form works without JavaScript.
 */
(function () {
	'use strict';

	var button = document.querySelector('[data-vg-reveal]');
	var input = document.getElementById('vg-password');

	if (button && input) {
		var label = button.querySelector('[data-vg-reveal-label]');
		var showLabel = button.getAttribute('data-label-show') || 'Show';
		var hideLabel = button.getAttribute('data-label-hide') || 'Hide';

		button.addEventListener('click', function () {
			var show = input.type === 'password';
			input.type = show ? 'text' : 'password';
			button.setAttribute('aria-pressed', show ? 'true' : 'false');

			if (label) {
				label.textContent = show ? hideLabel : showLabel;
			}

			var position = input.value.length;
			input.focus();

			try {
				input.setSelectionRange(position, position);
			} catch (error) {
				// Some input types do not support selection; ignore.
			}
		});
	}

	var submit = document.querySelector('[data-vg-lock]');
	var submitLabel = submit && submit.querySelector('[data-vg-submit-label]');
	var remaining = submit ? parseInt(submit.getAttribute('data-vg-lock'), 10) : 0;

	if (!submit || !submitLabel || !remaining || remaining < 1) {
		return;
	}

	var template = submit.getAttribute('data-label-wait') || 'Try again in %s';
	var original = submitLabel.textContent;

	function format(seconds) {
		var minutes = Math.floor(seconds / 60);
		var rest = seconds % 60;

		return minutes + ':' + (rest < 10 ? '0' + rest : String(rest));
	}

	function tick() {
		if (remaining <= 0) {
			submitLabel.textContent = original;
			submit.disabled = false;
			submit.removeAttribute('aria-disabled');
			return;
		}

		submit.disabled = true;
		submit.setAttribute('aria-disabled', 'true');
		submitLabel.textContent = template.replace('%s', format(remaining));
		remaining -= 1;

		window.setTimeout(tick, 1000);
	}

	tick();
})();
