(() => {
  const form = document.querySelector('[data-sample-calculator]');
  const panel = document.querySelector('[data-sample-result]');
  if (!form || !panel) return;

  const country = form.elements.country;
  const otherWrap = form.querySelector('[data-other-country]');
  const otherCountry = form.elements.other_country;
  const error = panel.querySelector('[data-sample-error]');
  const summary = panel.querySelector('[data-sample-summary]');
  const copyStatus = panel.querySelector('[data-sample-copy-status]');
  let clipboardText = '';

  const updateOtherCountry = () => {
    const needed = country.value === 'EU-OTHER';
    otherWrap.hidden = !needed;
    otherCountry.required = needed;
    if (!needed) otherCountry.value = '';
  };
  country.addEventListener('change', updateOtherCountry);

  const readNumber = (key, min, max, whole = false) => {
    const value = Number(form.elements[key].value);
    if (!Number.isFinite(value) || value < min || value > max || (whole && !Number.isInteger(value))) return null;
    return value;
  };

  const reviewParcel = () => {
    const pieces = readNumber('pieces', 1, 20, true);
    const weight = readNumber('weight', 0.01, 30);
    const length = readNumber('length', 0.01, 150);
    const width = readNumber('width', 0.01, 150);
    const height = readNumber('height', 0.01, 150);
    const postal = form.elements.postal.value.trim();
    const cargo = form.elements.cargo.selectedOptions[0]?.textContent?.trim() || '';
    const service = form.elements.service.value;
    const name = country.value === 'EU-OTHER' ? otherCountry.value.trim() : country.selectedOptions[0]?.textContent?.trim();
    panel.hidden = false;
    copyStatus.textContent = '';
    if (!country.value || !name || name.length > 80 || postal.length > 24 || [pieces, weight, length, width, height].some(value => value === null)) {
      error.textContent = 'Choose a destination and enter valid weight, dimensions and parcel count.';
      error.hidden = false;
      summary.hidden = true;
      panel.focus();
      return;
    }
    const actual = weight * pieces;
    const volume = length * width * height * pieces / 1000000;
    panel.querySelector('[data-sample-weight]').textContent = `${actual.toFixed(2)} kg`;
    panel.querySelector('[data-sample-volume]').textContent = `${volume.toFixed(4)} m³`;
    panel.querySelector('[data-sample-destination]').textContent = `Destination: ${name}. ${pieces} identical ${pieces === 1 ? 'parcel' : 'parcels'}.`;
    for (const card of panel.querySelectorAll('[data-sample-service]')) card.hidden = service !== 'both' && card.dataset.sampleService !== service;
    const serviceLabel = service === 'express' ? 'Express parcel' : service === 'air' ? 'Air parcel' : 'Express parcel / air parcel';
    clipboardText = `BEXSTAR sample shipping inquiry\nDestination: ${name}\nPostal code: ${postal || 'Not provided'}\nCargo type: ${cargo}\nParcels: ${pieces}\nWeight per parcel: ${weight} kg\nDimensions per parcel: ${length} × ${width} × ${height} cm\nTotal actual weight: ${actual.toFixed(2)} kg\nTotal volume: ${volume.toFixed(4)} m³\nInterested in: ${serviceLabel}`;
    error.hidden = true;
    summary.hidden = false;
    panel.focus();
  };

  form.addEventListener('submit', event => {
    event.preventDefault();
    reviewParcel();
  });
  form.querySelector('[data-sample-review]').addEventListener('click', reviewParcel);

  panel.querySelector('[data-sample-copy]').addEventListener('click', async () => {
    if (!clipboardText) return;
    try {
      await navigator.clipboard.writeText(clipboardText);
      copyStatus.textContent = 'Parcel details copied. Paste them into your quote request.';
    } catch (_) {
      copyStatus.textContent = 'Copy is unavailable in this browser. You can still request a quote and enter the parcel details there.';
    }
  });
})();
