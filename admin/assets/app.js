(() => {
    const app = document.getElementById('app');
    if (!app) {
        return;
    }
    const baseUrl = app.dataset.baseUrl || '';
    const csrf = app.dataset.csrf || '';

    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            const data = formToJson(loginForm);
            const response = await apiPost('/admin/api/login.php', data);
            handleResponse(response, () => window.location.reload());
        });
        return;
    }

    const state = {
        sections: [],
        infoblocks: [],
        currentSectionId: null,
        currentInfoblockId: null,
        entity: 'infoblocks',
    };

    const treeContainer = document.getElementById('sections-tree');
    const tableBody = document.querySelector('#entity-table tbody');
    const panelTitle = document.getElementById('panel-title');
    const createButton = document.getElementById('create-button');
    const entityModal = document.getElementById('entity-modal');
    const modalTitle = document.getElementById('modal-title');
    const entityForm = document.getElementById('entity-form');
    const saveButton = document.getElementById('save-button');
    const slugGenerate = document.getElementById('slug-generate');
    const slugPreview = document.getElementById('slug-preview');
    const contentWrapper = document.getElementById('content-wrapper');

    createButton.addEventListener('click', () => openModalForCreate());
    saveButton.addEventListener('click', () => submitEntity());
    slugGenerate.addEventListener('click', () => generateSlug());
    entityForm.slug.addEventListener('input', () => updateSlugPreview());
    entityForm.title.addEventListener('input', () => updateSlugPreview());

    loadSections();

    function formToJson(form) {
        const data = new FormData(form);
        const payload = {};
        data.forEach((value, key) => {
            payload[key] = value;
        });
        return payload;
    }

    async function apiPost(url, payload) {
        const body = { ...payload, csrf_token: csrf };
        const response = await fetch(baseUrl + url.replace('/admin', ''), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body),
        });
        return response.json();
    }

    function handleResponse(response, onSuccess) {
        if (response.success) {
            showToast('Успех', 'Операция выполнена');
            onSuccess();
            return;
        }
        showToast('Ошибка', response.error || 'Ошибка');
    }

    async function loadSections() {
        const response = await apiPost('/admin/api/sections.php', { action: 'list' });
        handleResponse(response, () => {
            state.sections = response.data.sections || [];
            renderTree();
            const first = state.sections.find((item) => item.parent_id === null) || state.sections[0];
            if (first) {
                selectSection(first.id);
            }
        });
    }

    function renderTree() {
        treeContainer.innerHTML = '';
        const rootItems = state.sections.filter((item) => item.parent_id === null);
        const list = document.createElement('ul');
        rootItems.forEach((item) => list.appendChild(renderTreeItem(item)));
        treeContainer.appendChild(list);
    }

    function renderTreeItem(section) {
        const li = document.createElement('li');
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'btn btn-link p-0';
        button.textContent = section.title;
        button.addEventListener('click', () => selectSection(section.id));
        li.appendChild(button);
        const children = state.sections.filter((item) => item.parent_id === section.id);
        if (children.length) {
            const ul = document.createElement('ul');
            children.forEach((child) => ul.appendChild(renderTreeItem(child)));
            li.appendChild(ul);
        }
        return li;
    }

    function selectSection(sectionId) {
        state.currentSectionId = sectionId;
        state.currentInfoblockId = null;
        state.entity = 'infoblocks';
        panelTitle.textContent = 'Инфоблоки';
        loadInfoblocks(sectionId);
        history.pushState({ sectionId }, '', `/admin/?section=${sectionId}`);
    }

    async function loadInfoblocks(sectionId) {
        const response = await apiPost('/admin/api/infoblocks.php', { action: 'list', section_id: sectionId });
        handleResponse(response, () => {
            state.infoblocks = response.data.infoblocks || [];
            renderTable(state.infoblocks);
        });
    }

    async function loadItems(infoblockId) {
        const response = await apiPost('/admin/api/items.php', { action: 'list', infoblock_id: infoblockId });
        handleResponse(response, () => {
            renderTable(response.data.items || []);
        });
    }

    function renderTable(rows) {
        tableBody.innerHTML = '';
        rows.forEach((row) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${row.id}</td>
                <td>${escapeHtml(row.title)}</td>
                <td>${escapeHtml(row.slug || '')}</td>
                <td>
                    <button class="btn btn-sm btn-outline-primary" data-action="edit">Редактировать</button>
                    <button class="btn btn-sm btn-outline-danger" data-action="delete">Удалить</button>
                </td>
            `;
            tr.querySelector('[data-action="edit"]').addEventListener('click', () => openModalForEdit(row));
            tr.querySelector('[data-action="delete"]').addEventListener('click', () => deleteEntity(row));
            tr.addEventListener('dblclick', () => drillDown(row));
            tableBody.appendChild(tr);
        });
    }

    function drillDown(row) {
        if (state.entity === 'infoblocks') {
            state.entity = 'items';
            state.currentInfoblockId = row.id;
            panelTitle.textContent = 'Элементы';
            loadItems(row.id);
            history.pushState({ infoblockId: row.id }, '', `/admin/?infoblock=${row.id}`);
        }
    }

    function openModalForCreate() {
        entityForm.reset();
        entityForm.id.value = '';
        entityForm.section_id.value = state.currentSectionId || '';
        entityForm.infoblock_id.value = state.currentInfoblockId || '';
        toggleContentField();
        modalTitle.textContent = 'Создать';
        updateSlugPreview();
        showModal();
    }

    function openModalForEdit(row) {
        entityForm.reset();
        entityForm.id.value = row.id;
        entityForm.section_id.value = state.currentSectionId || '';
        entityForm.infoblock_id.value = state.currentInfoblockId || '';
        entityForm.title.value = row.title || '';
        entityForm.slug.value = row.slug || '';
        entityForm.content_html.value = row.content_html || '';
        toggleContentField();
        modalTitle.textContent = 'Редактировать';
        updateSlugPreview();
        showModal();
    }

    function toggleContentField() {
        if (state.entity === 'items') {
            contentWrapper.style.display = 'block';
        } else {
            contentWrapper.style.display = 'none';
        }
    }

    async function submitEntity() {
        const payload = formToJson(entityForm);
        payload.action = payload.id ? 'update' : 'create';
        let endpoint = '/admin/api/sections.php';
        if (state.entity === 'infoblocks') {
            endpoint = '/admin/api/infoblocks.php';
        }
        if (state.entity === 'items') {
            endpoint = '/admin/api/items.php';
        }
        const response = await apiPost(endpoint, payload);
        handleResponse(response, () => {
            hideModal();
            refreshCurrent();
        });
    }

    async function deleteEntity(row) {
        if (!confirm('Удалить запись?')) {
            return;
        }
        let endpoint = '/admin/api/sections.php';
        if (state.entity === 'infoblocks') {
            endpoint = '/admin/api/infoblocks.php';
        }
        if (state.entity === 'items') {
            endpoint = '/admin/api/items.php';
        }
        const response = await apiPost(endpoint, { action: 'delete', id: row.id });
        handleResponse(response, () => refreshCurrent());
    }

    function refreshCurrent() {
        if (state.entity === 'items' && state.currentInfoblockId) {
            loadItems(state.currentInfoblockId);
            return;
        }
        if (state.currentSectionId) {
            loadInfoblocks(state.currentSectionId);
        }
    }

    function generateSlug() {
        const title = entityForm.title.value || '';
        entityForm.slug.value = slugify(title);
        updateSlugPreview();
    }

    async function updateSlugPreview() {
        const slug = entityForm.slug.value || '';
        const sectionId = state.currentSectionId;
        const parentId = getSectionParent(sectionId);
        const infoblock = state.infoblocks.find((item) => item.id === state.currentInfoblockId);
        const response = await apiPost('/admin/api/slug-check.php', {
            slug,
            entity: state.entity,
            section_id: sectionId,
            parent_id: parentId,
            infoblock_id: state.currentInfoblockId,
            infoblock_slug: infoblock ? infoblock.slug : '',
            id: entityForm.id.value || null,
        });
        if (!response.success) {
            slugPreview.textContent = response.error || '';
            return;
        }
        slugPreview.textContent = response.data.preview || '';
    }

    function getSectionParent(sectionId) {
        const section = state.sections.find((item) => item.id === sectionId);
        return section ? section.parent_id : null;
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function slugify(text) {
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
    }

    function showModal() {
        if (window.bootstrap && entityModal) {
            const modal = bootstrap.Modal.getOrCreateInstance(entityModal);
            modal.show();
        }
    }

    function hideModal() {
        if (window.bootstrap && entityModal) {
            const modal = bootstrap.Modal.getOrCreateInstance(entityModal);
            modal.hide();
        }
    }

    function showToast(title, message) {
        if (window.SOW && SOW.core && SOW.core.toast) {
            SOW.core.toast.show(title, message, 'bg-gradient-success');
            return;
        }
        alert(`${title}: ${message}`);
    }
})();
