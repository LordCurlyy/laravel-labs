import $ from 'jquery';
import * as bootstrap from 'bootstrap';
window.$ = window.jQuery = $;
const form = document.getElementById('cityForm');
if (form) {
    const places = JSON.parse(document.getElementById('oceaniaPlaces').textContent);
    const usedCities = JSON.parse(document.getElementById('usedCities').textContent);
    const factData = JSON.parse(document.getElementById('cityFacts').textContent);
    const country = document.getElementById('country');
    const city = document.getElementById('name');
    const summary = document.getElementById('summary');
    const description = document.getElementById('description');
    const descriptionHelp = document.getElementById('descriptionHelp');
    const removeGeneratedFact = () => {
        description.value = description.value.replace(/^Интересный факт:[^\n]*(?:\n\n)?/, '');
    };
    const refreshDescriptionCount = () => {
        form.querySelector('[data-count-for="description"]').textContent = description.value.length;
    };
    city.addEventListener('change', () => {
        const fact = factData.facts[city.value];
        removeGeneratedFact();
        if (fact) {
            const prefix = `Интересный факт: ${fact}`;
            const remaining = description.value;
            description.value = prefix + (remaining ? '\n\n' + remaining.slice(0, description.maxLength - prefix.length - 2) : '');
        }
        refreshDescriptionCount();
        updateDescriptionAccess();
    });
    const updateDescriptionAccess = () => {
        const hasCity = !city.disabled && (places[country.value] || []).includes(city.value) &&
            !(usedCities[country.value] || []).includes(city.value);
        summary.disabled = !hasCity;
        const remaining = Math.max(0, summary.minLength - summary.value.trim().length);
        description.disabled = !hasCity || remaining > 0;
        descriptionHelp.textContent = !hasCity ? 'Сначала выберите страну и город, чтобы заполнить описание.' : remaining > 0
            ? `Сначала заполните краткое описание: минимум ${summary.minLength} символов. Осталось: ${remaining}.`
            : 'Подробное описание доступно для заполнения.';
    };
    country.addEventListener('change', () => {
        removeGeneratedFact();
        refreshDescriptionCount();
        const cities = places[country.value] || [];
        city.replaceChildren(new Option(cities.length ? 'Выберите город' : 'Сначала выберите страну', ''));
        cities.forEach(name => {
            const used = (usedCities[country.value] || []).includes(name);
            const option = new Option(name + (used ? ' — уже в каталоге' : ''), name);
            option.disabled = used;
            city.add(option);
        });
        city.disabled = !cities.length;
        updateDescriptionAccess();
    });
    form.querySelectorAll('[data-character-count]').forEach(input => {
        const counter = form.querySelector(`[data-count-for="${input.id}"]`);
        const update = () => {
            counter.textContent = input.value.length;
            if (input === summary) updateDescriptionAccess();
        };
        input.addEventListener('input', update);
        update();
    });
    const date = document.getElementById('visited_at');
    const validateDate = () => {
        const match = /^(\d{2})\.(\d{2})\.(\d{4})$/.exec(date.value);
        let valid = !date.value;
        if (match) {
            const [, day, month, year] = match.map(Number);
            const parsed = new Date(0);
            parsed.setUTCFullYear(year, month - 1, day);
            valid = year > 0 && parsed.getUTCFullYear() === year &&
                parsed.getUTCMonth() === month - 1 && parsed.getUTCDate() === day;
        }
        let message = valid ? '' : 'Введите существующую дату в формате ДД.ММ.ГГГГ.';
        if (valid && match) {
            const parts = new Intl.DateTimeFormat('en', {
                timeZone: factData.timezone, year: 'numeric', month: '2-digit', day: '2-digit'
            }).formatToParts(new Date());
            const part = type => parts.find(item => item.type === type).value;
            const today = `${part('year')}-${part('month')}-${part('day')}`;
            const chosen = `${match[3]}-${match[2]}-${match[1]}`;
            if (chosen > today) message = 'Дата посещения не может быть позже сегодняшней.';
        }
        date.setCustomValidity(message);
        date.classList.toggle('is-invalid', Boolean(message));
        date.setAttribute('aria-invalid', message ? 'true' : 'false');
        document.getElementById('dateError').textContent = message;
    };
    date.addEventListener('keydown', event => {
        const pos = date.selectionStart;
        if (event.key === 'Backspace' && pos === date.selectionEnd && date.value[pos - 1] === '.') {
            date.setSelectionRange(pos - 2, pos);
        }
    });
    date.addEventListener('input', () => {
        const digitsBeforeCaret = date.value.slice(0, date.selectionStart).replace(/\D/g, '').length;
        const digits = date.value.replace(/\D/g, '').slice(0, 8);
        date.value = digits.slice(0, 2) + (digits.length > 2 ? '.' + digits.slice(2, 4) : '') +
            (digits.length > 4 ? '.' + digits.slice(4, 8) : '');
        let pos = 0, seen = 0;
        while (pos < date.value.length && seen < digitsBeforeCaret) {
            if (/\d/.test(date.value[pos])) seen++;
            pos++;
        }
        date.setSelectionRange(pos, pos);
        validateDate();
    });
    validateDate();
    form.addEventListener('submit', validateDate);
    const image = document.getElementById('image');
    image.addEventListener('change', () => {
        image.setCustomValidity(image.files[0]?.size > 2 * 1024 * 1024 ? 'Выберите файл до 2 МБ.' : '');
    });
}
document.querySelectorAll('[data-bs-toggle="popover"]').forEach(el => new bootstrap.Popover(el));
$('.needs-validation').on('submit', function(event) {
    if (!this.checkValidity()) { event.preventDefault(); event.stopPropagation(); }
    this.classList.add('was-validated');
});
$('[data-confirm-delete]').on('submit', function(event) {
    if (!window.confirm('Удалить этот город из каталога?')) event.preventDefault();
});
