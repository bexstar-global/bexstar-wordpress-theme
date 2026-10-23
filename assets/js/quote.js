(() => {
    const result = document.querySelector('[data-quote-result]');
    if (result) result.focus();
    const form = document.querySelector('[data-quote-form]');
    if (!form) return;
    const whatsapp = form.elements.whatsapp;
    const email = form.elements.email;
    const contactError = document.getElementById('bex-q-contact-error');
    const handoffNotice = form.querySelector('[data-sample-handoff]');
    try {
        const raw = sessionStorage.getItem('bexstar_sample_inquiry');
        if (raw) {
            sessionStorage.removeItem('bexstar_sample_inquiry');
            const data = JSON.parse(raw);
            const validNumber = (key, min, max, whole = false) => Number.isFinite(data[key]) && data[key] >= min && data[key] <= max && (!whole || Number.isInteger(data[key]));
            const valid = data.version === 1 && Number.isFinite(data.created) && data.created <= Date.now() && Date.now() - data.created < 30 * 60 * 1000
                && typeof data.country === 'string' && data.country.length > 0 && data.country.length <= 80
                && typeof data.postal === 'string' && data.postal.length <= 24
                && ['both', 'express', 'air'].includes(data.service)
                && ['General goods', 'Contains batteries', 'Liquid or powder', 'Not sure / other'].includes(data.cargo)
                && validNumber('pieces', 1, 20, true) && validNumber('weight', 0.01, 30)
                && ['length', 'width', 'height'].every(key => validNumber(key, 0.01, 150));
            // Preserve fields returned by WordPress after a failed form submission.
            if (valid && !result && !form.elements.country.value && !form.elements.package_weight.value) {
                form.elements.country.value = data.country;
                form.elements.postal.value = data.postal;
                form.elements.package_weight.value = data.weight;
                form.elements.namedItem('length').value = data.length;
                form.elements.width.value = data.width;
                form.elements.height.value = data.height;
                form.elements.pieces.value = data.pieces;
                form.elements.description.value = data.cargo;
                const service = data.service === 'express' ? 'Express parcel' : data.service === 'air' ? 'Air parcel' : 'Express parcel / air parcel';
                form.elements.additional.value = `Sample parcel inquiry. Service interest: ${service}.`;
                if (handoffNotice) handoffNotice.hidden = false;
            }
        }
    } catch (_) {
        // A malformed or blocked session store must never prevent an inquiry.
    }
    function updateMethod() {
        const method = form.querySelector('input[name="method"]:checked').value;
        form.querySelectorAll('[data-cargo-method]').forEach(panel => {
            const active = panel.dataset.cargoMethod === method;
            panel.hidden = !active;
            panel.disabled = !active;
            panel.querySelectorAll('input').forEach(input => { input.required = active; });
        });
    }
    function clearContactError() {
        whatsapp.setCustomValidity('');
        contactError.hidden = true;
    }
    form.querySelectorAll('input[name="method"]').forEach(input => input.addEventListener('change', updateMethod));
    [whatsapp, email].forEach(input => input.addEventListener('input', clearContactError));
    // Run before the browser's native constraint validation for mouse and keyboard submission.
    form.querySelector('button[type="submit"]').addEventListener('click', () => {
        const missing = !whatsapp.value.trim() && !email.value.trim();
        contactError.hidden = !missing;
        whatsapp.setCustomValidity(missing ? contactError.textContent.trim() : '');
    });
    form.addEventListener('submit', event => {
        if (!whatsapp.value.trim() && !email.value.trim()) {
            event.preventDefault();
            contactError.hidden = false;
            whatsapp.focus();
        }
    });
    updateMethod();
})();
