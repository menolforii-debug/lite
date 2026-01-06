(() => {
    const helpers = {
        formToJson(form) {
            const data = new FormData(form);
            const payload = {};
            data.forEach((value, key) => {
                payload[key] = value;
            });
            return payload;
        },
        escapeHtml(value) {
            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        },
        slugify(text) {
            const map = {
                а: 'a', б: 'b', в: 'v', г: 'g', д: 'd', е: 'e', ё: 'e', ж: 'zh', з: 'z', и: 'i',
                й: 'y', к: 'k', л: 'l', м: 'm', н: 'n', о: 'o', п: 'p', р: 'r', с: 's', т: 't',
                у: 'u', ф: 'f', х: 'h', ц: 'ts', ч: 'ch', ш: 'sh', щ: 'sch', ь: '', ы: 'y', ъ: '',
                э: 'e', ю: 'yu', я: 'ya',
            };
            return text
                .split('')
                .map((char) => map[char] || map[char.toLowerCase()] || char)
                .join('')
                .toLowerCase()
                .replace(/[^a-z0-9\-_]+/g, '-')
                .replace(/^-+|-+$/g, '')
                .substring(0, 120);
        },
        showModal(modalElement) {
            if (window.bootstrap && modalElement) {
                bootstrap.Modal.getOrCreateInstance(modalElement).show();
            }
        },
        hideModal(modalElement) {
            if (window.bootstrap && modalElement) {
                bootstrap.Modal.getOrCreateInstance(modalElement).hide();
            }
        },
        showToast(title, message) {
            if (window.SOW && SOW.core && SOW.core.toast) {
                SOW.core.toast.show(title, message, 'bg-gradient-success');
                return;
            }
            alert(`${title}: ${message}`);
        },
    };

    window.LiteCMSHelpers = helpers;
})();
