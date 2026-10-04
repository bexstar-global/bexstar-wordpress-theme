/* BEXSTAR API client only. No provider URLs, keys or number-format inference. */
(() => {
    'use strict';
    document.querySelectorAll('.bex-tracking-foundation').forEach((root) => {
        const form = root.querySelector('.bex-tracking-form');
        const input = root.querySelector('#bex-tracking-number');
        const button = form.querySelector('button');
        const results = root.querySelector('.bex-tracking-results');
        const timeline = root.querySelector('.bex-tracking-timeline');
        const feedback = root.querySelector('.bex-tracking-feedback');
        const error = root.querySelector('.bex-tracking-error');
        const date = (value) => {
            if (!value) return 'Not available';
            const time = new Date(value);
            return Number.isNaN(time.valueOf()) ? 'Not available' : time.toLocaleString(undefined, {timeZoneName: 'short'});
        };
        const label = (value) => value ? String(value).replaceAll('_', ' ').replace(/^./, (c) => c.toUpperCase()) : 'Not available';
        const messages = {
            INVALID_REQUEST: 'Please enter a valid tracking reference.',
            TRACKING_NOT_FOUND: "We couldn't find tracking information for this reference yet. Please check the number or contact BEXSTAR.",
            PROVIDER_NOT_MAPPED: "We couldn't find tracking information for this reference yet. Please check the number or contact BEXSTAR.",
            RATE_LIMITED: 'Please wait a minute before trying again.'
        };
        let controller;
        let sequence = 0;
        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            if (form.querySelector('fieldset').disabled) return;
            const reference = input.value.trim();
            if (!reference) { input.reportValidity(); return; }
            const current = ++sequence;
            if (controller) controller.abort();
            controller = new AbortController();
            const timeout = setTimeout(() => controller.abort(), 25000);
            results.hidden = true; error.hidden = true; input.removeAttribute('aria-invalid');
            feedback.textContent = 'Looking up your shipment…';
            form.setAttribute('aria-busy', 'true'); button.disabled = true;
            try {
                const response = await fetch(form.dataset.apiUrl, {
                    method: 'POST', credentials: 'omit', cache: 'no-store',
                    headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
                    body: JSON.stringify({reference}), signal: controller.signal
                });
                const data = await response.json();
                if (current !== sequence) return;
                if (!response.ok || data.success !== true) {
                    const code = data.error?.code;
                    if (code === 'INVALID_REQUEST') input.setAttribute('aria-invalid', 'true');
                    throw new Error(messages[code] || 'Tracking information is temporarily unavailable. Please try again later or contact BEXSTAR.');
                }
                if (data.reference !== reference || data.schema_version !== 1 || !data.shipment || !Array.isArray(data.events)) throw new Error('Tracking information is temporarily unavailable. Please try again later.');
                root.querySelectorAll('[data-tracking-field]').forEach((field) => {
                    const key = field.dataset.trackingField;
                    const value = data.shipment[key];
                    field.textContent = key === 'last_updated' ? date(value) : ['current_status','transport_mode'].includes(key) ? label(value) : (value || 'Not available');
                });
                timeline.replaceChildren();
                data.events.forEach((item) => {
                    const fragment = root.querySelector('#bex-tracking-event-template').content.cloneNode(true);
                    const time = fragment.querySelector('time');
                    time.textContent = date(item.timestamp);
                    if (item.timestamp) time.dateTime = item.timestamp;
                    fragment.querySelector('h4').textContent = label(item.normalized_status);
                    const location = fragment.querySelector('[data-event-location]');
                    location.textContent = item.location || ''; location.hidden = !item.location;
                    const description = fragment.querySelector('[data-event-description]');
                    description.textContent = item.description || ''; description.hidden = !item.description;
                    timeline.append(fragment);
                });
                root.querySelector('.bex-tracking-partial').hidden = data.meta?.partial !== true;
                root.querySelector('.bex-tracking-empty').hidden = data.events.length !== 0;
                results.hidden = false;
                feedback.textContent = 'Shipment updates loaded.';
            } catch (failure) {
                if (current !== sequence) return;
                feedback.textContent = '';
                // Only locally authored messages. Never display server exception prose.
                const allowed = Object.values(messages).concat([
                    'Tracking information is temporarily unavailable. Please try again later or contact BEXSTAR.',
                    'Tracking information is temporarily unavailable. Please try again later.'
                ]);
                error.textContent = allowed.includes(failure.message) ? failure.message : allowed[allowed.length - 1];
                error.hidden = false; error.focus();
            } finally {
                clearTimeout(timeout);
                if (current === sequence) { button.disabled = false; form.removeAttribute('aria-busy'); }
            }
        });
    });
})();
