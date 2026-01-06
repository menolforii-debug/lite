(() => {
    function init() {
        const app = document.getElementById('app');
        if (!app) {
            return;
        }
        const baseUrl = app.dataset.baseUrl || '';
        const csrf = app.dataset.csrf || '';
        const helpers = window.LiteCMSHelpers;
        const api = window.LiteCMSApi;
        const ui = window.LiteCMSUi;
        api.init(baseUrl, csrf);

        const loginForm = document.getElementById('login-form');
        if (loginForm) {
            bindLoginForm(loginForm, api, helpers);
            return;
        }

        const state = {
            sections: [],
            infoblocks: [],
            currentSectionId: null,
            currentInfoblockId: null,
            entity: 'infoblocks',
        };

        const elements = {
            treeContainer: document.getElementById('sections-tree'),
            tableBody: document.querySelector('#entity-table tbody'),
            panelTitle: document.getElementById('panel-title'),
            entitySwitch: document.getElementById('entity-switch'),
            entityModal: document.getElementById('entity-modal'),
            modalTitle: document.getElementById('modal-title'),
            entityForm: document.getElementById('entity-form'),
            contentWrapper: document.getElementById('content-wrapper'),
            slugPreview: document.getElementById('slug-preview'),
        };
        const createButton = document.getElementById('create-button');
        const saveButton = document.getElementById('save-button');
        const slugGenerate = document.getElementById('slug-generate');
        const entityForm = elements.entityForm;

        const uiController = ui.create(elements, helpers);

        createButton.addEventListener('click', () => openModalForCreate());
        saveButton.addEventListener('click', () => submitEntity());
        slugGenerate.addEventListener('click', () => generateSlug());
        entityForm.slug.addEventListener('input', () => updateSlugPreview());
        entityForm.title.addEventListener('input', () => updateSlugPreview());
        elements.entitySwitch.addEventListener('click', (event) => handleEntitySwitch(event));

        loadSections(true);

        function bindLoginForm(form, apiClient, util) {
            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                const data = util.formToJson(form);
                const response = await apiClient.post('/admin/api/login.php', data);
                handleResponse(response, () => window.location.reload());
            });
        }

        function handleResponse(response, onSuccess) {
            if (response.success) {
                helpers.showToast('Успех', 'Операция выполнена');
                onSuccess();
                return;
            }
            helpers.showToast('Ошибка', response.error || 'Ошибка');
        }

        async function loadSections(selectDefault) {
            const response = await api.post('/admin/api/sections.php', { action: 'list' });
            handleResponse(response, () => {
                state.sections = response.data.sections || [];
                uiController.renderTree(state.sections, selectSection);
                if (state.entity === 'sections') {
                    uiController.renderTable(state.sections, openModalForEdit, deleteEntity, drillDown);
                    return;
                }
                if (selectDefault) {
                    const first = state.sections.find((item) => item.parent_id === null) || state.sections[0];
                    if (first) {
                        selectSection(first.id);
                    }
                }
            });
        }

        function selectSection(sectionId) {
            state.currentSectionId = sectionId;
            state.currentInfoblockId = null;
            setEntity('infoblocks');
            loadInfoblocks(sectionId);
            history.pushState({ sectionId }, '', `/admin/?section=${sectionId}`);
        }

        async function loadInfoblocks(sectionId) {
            const response = await api.post('/admin/api/infoblocks.php', { action: 'list', section_id: sectionId });
            handleResponse(response, () => {
                state.infoblocks = response.data.infoblocks || [];
                uiController.renderTable(state.infoblocks, openModalForEdit, deleteEntity, drillDown);
            });
        }

        async function loadItems(infoblockId) {
            const response = await api.post('/admin/api/items.php', { action: 'list', infoblock_id: infoblockId });
            handleResponse(response, () => {
                uiController.renderTable(response.data.items || [], openModalForEdit, deleteEntity, drillDown);
            });
        }

        function drillDown(row) {
            if (state.entity !== 'infoblocks') {
                return;
            }
            state.currentInfoblockId = row.id;
            setEntity('items');
            loadItems(row.id);
            history.pushState({ infoblockId: row.id }, '', `/admin/?infoblock=${row.id}`);
        }

        function handleEntitySwitch(event) {
            const button = event.target.closest('[data-entity]');
            if (!button) {
                return;
            }
            const entity = button.dataset.entity || 'infoblocks';
            if (entity === 'items' && !state.currentInfoblockId) {
                return;
            }
            setEntity(entity);
            refreshCurrent();
        }

        function setEntity(entity) {
            state.entity = entity;
            uiController.setEntity(entity, Boolean(state.currentInfoblockId));
        }

        function openModalForCreate() {
            uiController.openModalForCreate(state);
            updateSlugPreview();
        }

        function openModalForEdit(row) {
            uiController.openModalForEdit(row, state);
            updateSlugPreview();
        }

        async function submitEntity() {
            const payload = helpers.formToJson(entityForm);
            payload.action = payload.id ? 'update' : 'create';
            const endpoint = entityEndpoint(state.entity);
            const response = await api.post(endpoint, payload);
            handleResponse(response, () => {
                helpers.hideModal(elements.entityModal);
                refreshCurrent(true);
            });
        }

        async function deleteEntity(row) {
            if (!confirm('Удалить запись?')) {
                return;
            }
            const endpoint = entityEndpoint(state.entity);
            const response = await api.post(endpoint, { action: 'delete', id: row.id });
            handleResponse(response, () => refreshCurrent(true));
        }

        function refreshCurrent(forceReload = false) {
            if (state.entity === 'sections') {
                return forceReload ? loadSections(false) : uiController.renderTable(state.sections, openModalForEdit, deleteEntity, drillDown);
            }
            if (state.entity === 'items' && state.currentInfoblockId) {
                return loadItems(state.currentInfoblockId);
            }
            if (state.currentSectionId) {
                loadInfoblocks(state.currentSectionId);
            }
        }

        function generateSlug() {
            entityForm.slug.value = helpers.slugify(entityForm.title.value || '');
            updateSlugPreview();
        }

        async function updateSlugPreview() {
            const slug = entityForm.slug.value || '';
            const sectionId = state.currentSectionId;
            const parentId = getSectionParent(sectionId);
            const infoblock = state.infoblocks.find((item) => item.id === state.currentInfoblockId);
            const response = await api.post('/admin/api/slug-check.php', {
                slug,
                entity: state.entity,
                section_id: sectionId,
                parent_id: entityForm.parent_id.value || parentId,
                infoblock_id: state.currentInfoblockId,
                infoblock_slug: infoblock ? infoblock.slug : '',
                id: entityForm.id.value || null,
            });
            uiController.showSlugPreview(response.success ? (response.data.preview || '') : (response.error || ''));
        }

        function getSectionParent(sectionId) {
            const section = state.sections.find((item) => item.id === sectionId);
            return section ? section.parent_id : null;
        }

        function entityEndpoint(entity) {
            if (entity === 'sections') {
                return '/admin/api/sections.php';
            }
            if (entity === 'items') {
                return '/admin/api/items.php';
            }
            return '/admin/api/infoblocks.php';
        }
    }

    window.LiteCMSApp = { init };
})();
