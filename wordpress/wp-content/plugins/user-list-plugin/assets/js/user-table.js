/**
 * @package UserListPlugin
 */

document.addEventListener('DOMContentLoaded', function() {
    let selectedIds = new Set();

    //create button
    const createBtn = document.getElementById('createNewUserBtn');
    if (createBtn) {
        createBtn.addEventListener('click', function() {
            window.location.href = 'admin.php?page=ulp-user-create';
        });
    }

    // filters
    document.querySelectorAll('.ulp-filter-select').forEach(select => {
        if(select.closest('.ulp-filter-group')) {
            select.addEventListener('change', function() {
                const filterGroup = this.closest('.ulp-filter-group');
                const filterType = filterGroup.dataset.filterType;

                if(filterType === 'status') {
                    document.getElementById('status_input').value = this.value;
                } else if(filterType === 'gender') {
                    document.getElementById('gender_input').value = this.value;
                }
                document.getElementById('filterForm').submit();
            });
        }
    });

    // search by name
    document.querySelector('.ulp-search-input')?.addEventListener('keypress', function(e) {
        if(e.key === 'Enter') {
            this.closest('form').submit();
        }
    });

    // edit buttons
    document.querySelectorAll('.ulp-users-table .ulp-table-button[data-id]').forEach(btn => {
        btn.addEventListener('click', function() {
            const userId = this.getAttribute('data-id');
            window.location.href = 'admin.php?page=ulp-user-edit&id=' + userId;
        });
    });

    // checkbox handling
    function updateSelectedCount() {
        const count = selectedIds.size;
        document.getElementById('selectedCount').innerText = count;
        const deleteBtn = document.getElementById('deleteSelectedBtn');
        if (deleteBtn) {
            deleteBtn.disabled = count === 0;
        }
    }

    document.querySelectorAll('.ulp-user-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const id = parseInt(this.getAttribute('data-id'));
            if (this.checked) {
                selectedIds.add(id);
            } else {
                selectedIds.delete(id);
            }
            updateSelectedCount();

            const allCheckboxes = document.querySelectorAll('.ulp-user-checkbox');
            const selectAllCheckbox = document.getElementById('selectAllCheckbox');
            if (selectAllCheckbox) {
                selectAllCheckbox.checked = allCheckboxes.length === selectedIds.size && allCheckboxes.length > 0;
            }
        });
    });

    // select all checkbox
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const allCheckboxes = document.querySelectorAll('.ulp-user-checkbox');
            allCheckboxes.forEach(checkbox => {
                const id = parseInt(checkbox.getAttribute('data-id'));
                checkbox.checked = this.checked;
                if (this.checked) {
                    selectedIds.add(id);
                } else {
                    selectedIds.delete(id);
                }
            });
            updateSelectedCount();
        });
    }

    // show confirm modal
    const deleteBtn = document.getElementById('deleteSelectedBtn');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', function() {
            if (selectedIds.size === 0) return;

            const message = ulp_i18n.confirmMessage.replace('%d', selectedIds.size);
            const modalHtml = `
                <div class="ulp-modal-overlay" id="confirmModal">
                    <div class="ulp-modal">
                        <div class="ulp-modal-content">
                            <h3>${ulp_i18n.confirmTitle}</h3>
                            <p class="ulp-modal-message">${message}</p>
                            <div class="ulp-modal-actions">
                                <button class="ulp-modal-button" id="modalCancelBtn">${ulp_i18n.cancelBtn}</button>
                                <button class="ulp-modal-button" id="modalConfirmBtn">${ulp_i18n.deleteBtn}</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            const modalContainer = document.getElementById('confirmModalContainer');
            modalContainer.style.display = 'block';
            modalContainer.innerHTML = modalHtml;

            // cancel button
            document.getElementById('modalCancelBtn').addEventListener('click', function() {
                modalContainer.style.display = 'none';
                modalContainer.innerHTML = '';
            });

            // confirm button
            document.getElementById('modalConfirmBtn').addEventListener('click', function() {
                const idsArray = Array.from(selectedIds);
                document.getElementById('deleteIdsInput').value = JSON.stringify(idsArray);
                document.getElementById('deleteUsersForm').submit();
                modalContainer.style.display = 'none';
            });
        });
    }
});