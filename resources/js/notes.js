for (const list of document.querySelectorAll('[data-list]')) {
    const rows = list.querySelector('[data-rows]');
    const add = list.querySelector('[data-add]');
    add.addEventListener('click', () => {
        rows.append(list.querySelector('template').content.cloneNode(true));
        list.dispatchEvent(new Event('input', { bubbles: true }));
        rows.lastElementChild.querySelector('textarea').focus();
    });
    list.addEventListener('click', (event) => {
        if (event.target.closest('[data-remove]')) {
            event.target.closest('[data-row]').remove();
            list.dispatchEvent(new Event('input', { bubbles: true }));
            add.focus();
        }
    });
    list.form.addEventListener('submit', () => {
        Array.from(rows.children).forEach((row, index) => {
            for (const input of row.querySelectorAll('[name]')) {
                input.name = input.name.replace(/\[\d+\]/, '[' + index + ']');
            }
        });
    });
}
const aiForm = document.querySelector('[data-ai-form]');
aiForm?.addEventListener('submit', () => {
    aiForm.querySelector('button').disabled = true;
    aiForm.querySelector('[role=status]').textContent = 'Memproses catatan…';
});

const finalizeForm = document.querySelector('[data-finalize-form]');
document.querySelector('[data-review-form]')?.addEventListener('input', () => {
    if (finalizeForm) {
        finalizeForm.querySelector('button').disabled = true;
        finalizeForm.querySelector('[role=status]').textContent = 'Simpan perubahan review sebelum finalisasi.';
    }
});
document.querySelector('[data-draft-form]')?.addEventListener('input', () => {
    if (aiForm) {
        aiForm.querySelector('button').disabled = true;
        aiForm.querySelector('[role=status]').textContent = 'Simpan perubahan draf sebelum memprosesnya.';
    }
});
