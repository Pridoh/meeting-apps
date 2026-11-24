document.querySelector('main input[aria-invalid="true"]')?.focus();

for (const form of document.querySelectorAll('form[data-confirm-with]')) {
    const dialog = document.getElementById(form.dataset.confirmWith);

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        dialog.returnValue = '';
        dialog.showModal();
    });

    dialog.addEventListener('close', () => {
        if (dialog.returnValue === 'confirm') {
            form.submit();
        }
    });

    dialog.addEventListener('click', (event) => {
        if (event.target !== dialog) {
            return;
        }

        const { top, right, bottom, left } = dialog.getBoundingClientRect();
        if (event.clientX < left || event.clientX > right || event.clientY < top || event.clientY > bottom) {
            dialog.close();
        }
    });
}
