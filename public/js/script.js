/**
 * Script JavaScript principal
 */
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.querySelector('#flashcard-search');
    const rows = Array.from(document.querySelectorAll('[data-flashcard-row]'));
    const emptyRow = document.querySelector('[data-search-empty]');

    const normalize = (value) => value
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .trim();

    if (searchInput && rows.length > 0) {
        const filterRows = () => {
            const query = normalize(searchInput.value);
            let visibleRows = 0;

            rows.forEach((row) => {
                const title = normalize(row.dataset.flashcardTitle || '');
                const isVisible = query === '' || title.includes(query);

                row.hidden = !isVisible;
                if (isVisible) {
                    visibleRows += 1;
                }
            });

            if (emptyRow) {
                emptyRow.hidden = visibleRows > 0 || query === '';
            }
        };

        searchInput.addEventListener('input', filterRows);
        filterRows();
    }

    const titleInput = document.querySelector('#flashcard-title');
    const titleCount = document.querySelector('[data-title-count]');

    if (titleInput && titleCount) {
        const updateTitleCount = () => {
            titleCount.textContent = `${titleInput.value.length}/150`;
        };

        titleInput.addEventListener('input', updateTitleCount);
        updateTitleCount();
    }

    const shareSearch = document.querySelector('#share-user-search');
    const shareUsers = Array.from(document.querySelectorAll('[data-share-user]'));
    const shareCount = document.querySelector('[data-share-selected-count]');
    const shareCheckboxes = Array.from(document.querySelectorAll('input[name="sharedUserIds[]"]'));

    if (shareSearch && shareUsers.length > 0) {
        const filterUsers = () => {
            const query = normalize(shareSearch.value);

            shareUsers.forEach((user) => {
                user.hidden = query !== '' && !normalize(user.dataset.shareUser || '').includes(query);
            });
        };

        shareSearch.addEventListener('input', filterUsers);
    }

    if (shareCount && shareCheckboxes.length > 0) {
        const updateShareCount = () => {
            const count = shareCheckboxes.filter((checkbox) => checkbox.checked).length;
            shareCount.textContent = `${count} sélectionné${count > 1 ? 's' : ''}`;
        };

        shareCheckboxes.forEach((checkbox) => {
            checkbox.addEventListener('change', updateShareCount);
        });
        updateShareCount();
    }
});
