(() => {
    const result = document.querySelector('[data-quote-result]');
    if (result) result.focus();
    const form = document.querySelector('[data-quote-form]');
    if (!form) return;
    const whatsapp = form.elements.whatsapp;
    const email = form.elements.email;
    const contactError = document.getElementById('bex-q-contact-error');
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
