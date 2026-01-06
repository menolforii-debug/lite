(() => {
    function create(elements, helpers) {
        const {
            treeContainer,
            tableBody,
            panelTitle,
            entitySwitch,
            entityModal,
            modalTitle,
            entityForm,
            contentWrapper,
            slugPreview,
        } = elements;

        function renderTree(sections, onSelectSection) {
            treeContainer.innerHTML = '';
            const rootItems = sections.filter((item) => item.parent_id === null);
            const list = document.createElement('ul');
            rootItems.forEach((item) => list.appendChild(renderTreeItem(item, sections, onSelectSection)));
            treeContainer.appendChild(list);
        }

        function renderTreeItem(section, sections, onSelectSection) {
            const li = document.createElement('li');
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'btn btn-link p-0';
            button.textContent = section.title;
            button.addEventListener('click', () => onSelectSection(section.id));
            li.appendChild(button);
            const children = sections.filter((item) => item.parent_id === section.id);
            if (children.length) {
                const ul = document.createElement('ul');
                children.forEach((child) => ul.appendChild(renderTreeItem(child, sections, onSelectSection)));
                li.appendChild(ul);
            }
            return li;
        }

        function renderTable(rows, onEdit, onDelete, onDrill) {
            tableBody.innerHTML = '';
            rows.forEach((row) => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${row.id}</td>
                    <td>${helpers.escapeHtml(row.title)}</td>
                    <td>${helpers.escapeHtml(row.slug || '')}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary" data-action="edit">Редактировать</button>
                        <button class="btn btn-sm btn-outline-danger" data-action="delete">Удалить</button>
                    </td>
                `;
                tr.querySelector('[data-action="edit"]').addEventListener('click', () => onEdit(row));
                tr.querySelector('[data-action="delete"]').addEventListener('click', () => onDelete(row));
                tr.addEventListener('dblclick', () => onDrill(row));
                tableBody.appendChild(tr);
            });
        }

        function setEntity(entity, hasInfoblock) {
            panelTitle.textContent = entity === 'sections' ? 'Разделы' : entity === 'items' ? 'Элементы' : 'Инфоблоки';
            entitySwitch.querySelectorAll('button').forEach((button) => {
                const isActive = button.dataset.entity === entity;
                button.classList.toggle('btn-primary', isActive);
                button.classList.toggle('btn-outline-primary', !isActive);
            });
            const itemsButton = entitySwitch.querySelector('[data-entity="items"]');
            if (itemsButton) {
                itemsButton.disabled = !hasInfoblock;
            }
            contentWrapper.style.display = entity === 'items' ? 'block' : 'none';
        }

        function openModalForCreate(state) {
            entityForm.reset();
            entityForm.id.value = '';
            entityForm.section_id.value = state.currentSectionId || '';
            entityForm.infoblock_id.value = state.currentInfoblockId || '';
            entityForm.parent_id.value = state.entity === 'sections' ? (state.currentSectionId || '') : '';
            modalTitle.textContent = 'Создать';
            helpers.showModal(entityModal);
        }

        function openModalForEdit(row, state) {
            entityForm.reset();
            entityForm.id.value = row.id;
            entityForm.section_id.value = state.currentSectionId || '';
            entityForm.infoblock_id.value = state.currentInfoblockId || '';
            entityForm.parent_id.value = row.parent_id || '';
            entityForm.title.value = row.title || '';
            entityForm.slug.value = row.slug || '';
            entityForm.content_html.value = row.content_html || '';
            modalTitle.textContent = 'Редактировать';
            helpers.showModal(entityModal);
        }

        function showSlugPreview(text) {
            slugPreview.textContent = text;
        }

        return {
            renderTree,
            renderTable,
            setEntity,
            openModalForCreate,
            openModalForEdit,
            showSlugPreview,
        };
    }

    window.LiteCMSUi = { create };
})();
