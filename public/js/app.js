'use strict';

document.querySelectorAll('[data-toggle-password]').forEach(button => {
    button.addEventListener('click', () => {
        const input = document.getElementById(button.dataset.togglePassword);
        const visible = input.type === 'password';
        input.type = visible ? 'text' : 'password';
        button.textContent = visible ? 'Ocultar' : 'Ver';
        button.setAttribute('aria-pressed', String(visible));
        button.setAttribute('aria-label', (visible ? 'Ocultar ' : 'Mostrar ') +
            document.querySelector('label[for="' + input.id + '"]').textContent.toLowerCase());
    });
});

document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', () => {
        const button = form.querySelector('button[type="submit"]');
        if (button) {
            button.disabled = true;
            button.setAttribute('aria-busy', 'true');
        }
    });
});

window.addEventListener('pageshow', event => {
    document.querySelectorAll('button[aria-busy]').forEach(button => {
        button.disabled = false;
        button.removeAttribute('aria-busy');
    });
    if (event.persisted) location.reload();
});
