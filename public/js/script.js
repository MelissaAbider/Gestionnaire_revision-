/**
 * Script JavaScript principal
 */
document.addEventListener('DOMContentLoaded', () => {
    const normalize = (value) => value
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .trim();

    document.querySelectorAll('[data-live-search]').forEach((searchInput) => {
        const listName = searchInput.dataset.liveSearch || '';
        const cards = Array.from(document.querySelectorAll(`[data-live-card="${listName}"]`));
        const emptyState = document.querySelector(`[data-live-empty="${listName}"]`);

        if (cards.length === 0 && !emptyState) {
            return;
        }

        const filterCards = () => {
            const query = normalize(searchInput.value);
            let visibleCards = 0;

            cards.forEach((card) => {
                const text = normalize(card.dataset.liveText || '');
                const isVisible = query === '' || text.includes(query);

                card.hidden = !isVisible;
                if (isVisible) {
                    visibleCards += 1;
                }
            });

            if (emptyState) {
                emptyState.hidden = visibleCards > 0;
            }
        };

        searchInput.addEventListener('input', filterCards);
        filterCards();
    });

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

    const qaList = document.querySelector('[data-qa-list]');
    const addQaButton = document.querySelector('[data-add-qa]');

    if (qaList && addQaButton) {
        const updateQaItems = () => {
            const items = Array.from(qaList.querySelectorAll('[data-qa-item]'));

            items.forEach((item, index) => {
                const title = item.querySelector('.question-response-item-header strong');
                const question = item.querySelector('textarea[name="questions[]"]');
                const response = item.querySelector('textarea[name="responses[]"]');
                const labels = item.querySelectorAll('label');
                const removeButton = item.querySelector('[data-remove-qa]');

                if (title) {
                    title.textContent = `Carte ${index + 1}`;
                }

                if (question) {
                    question.id = `flashcard-question-${index}`;
                }

                if (response) {
                    response.id = `flashcard-response-${index}`;
                }

                if (labels[0]) {
                    labels[0].htmlFor = `flashcard-question-${index}`;
                }

                if (labels[1]) {
                    labels[1].htmlFor = `flashcard-response-${index}`;
                }

                if (removeButton) {
                    removeButton.hidden = items.length <= 1;
                }
            });
        };

        const createQaItem = () => {
            const template = qaList.querySelector('[data-qa-item]');
            const item = template ? template.cloneNode(true) : document.createElement('div');

            item.classList.add('question-response-item');
            item.dataset.qaItem = '';

            if (!template) {
                item.innerHTML = `
                    <div class="question-response-item-header">
                        <strong>Carte</strong>
                        <button class="remove-qa-button" type="button" data-remove-qa>Supprimer</button>
                    </div>
                    <label>Question</label>
                    <textarea name="questions[]" rows="3" placeholder="Ex. À quoi sert le modèle OSI ?" required></textarea>
                    <label>Réponse</label>
                    <textarea name="responses[]" rows="4" placeholder="Saisissez la réponse attendue..." required></textarea>
                `;
            }

            item.querySelectorAll('textarea').forEach((textarea) => {
                textarea.value = '';
            });

            return item;
        };

        qaList.addEventListener('click', (event) => {
            const removeButton = event.target.closest('[data-remove-qa]');

            if (!removeButton) {
                return;
            }

            const items = qaList.querySelectorAll('[data-qa-item]');
            if (items.length <= 1) {
                return;
            }

            removeButton.closest('[data-qa-item]')?.remove();
            updateQaItems();
        });

        addQaButton.addEventListener('click', () => {
            const item = createQaItem();
            qaList.appendChild(item);
            updateQaItems();

            const question = item.querySelector('textarea[name="questions[]"]');
            question?.focus();
        });

        updateQaItems();
    }

    const revisionCard = document.querySelector('[data-revision-card]');

    if (revisionCard) {
        const cardText = revisionCard.querySelector('[data-card-text]');
        const cardSide = revisionCard.querySelector('[data-card-side]');
        const prevButton = document.querySelector('[data-card-prev]');
        const nextButton = document.querySelector('[data-card-next]');
        const counter = document.querySelector('[data-card-counter]');
        const flashcardId = revisionCard.dataset.flashcardId || '';
        let cards = [];
        let showingResponse = false;
        let currentIndex = 0;
        let revisionRecorded = false;

        try {
            cards = JSON.parse(revisionCard.dataset.cards || '[]');
        } catch (error) {
            cards = [];
        }

        if (cards.length === 0) {
            cards = [{
                question: revisionCard.dataset.question || '',
                response: revisionCard.dataset.response || '',
            }];
        }

        const updateRevisionCard = () => {
            const card = cards[currentIndex] || { question: '', response: '' };

            if (cardText) {
                cardText.textContent = showingResponse ? card.response : card.question;
            }

            if (cardSide) {
                cardSide.textContent = showingResponse ? 'Réponse' : 'Question';
            }

            if (counter) {
                counter.textContent = `${currentIndex + 1} / ${cards.length}`;
            }

            if (prevButton) {
                prevButton.disabled = cards.length <= 1;
            }

            if (nextButton) {
                nextButton.disabled = cards.length <= 1;
            }

            revisionCard.classList.toggle('is-answer-visible', showingResponse);
        };

        const recordRevision = () => {
            if (!flashcardId || revisionRecorded) {
                return;
            }

            revisionRecorded = true;

            const body = new URLSearchParams();
            body.set('flashcard_id', flashcardId);

            fetch('?action=recordRevision', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: body.toString(),
                credentials: 'same-origin',
            }).catch(() => {
                revisionRecorded = false;
            });
        };

        revisionCard.addEventListener('click', () => {
            showingResponse = !showingResponse;
            updateRevisionCard();

            if (showingResponse) {
                recordRevision();
            }
        });

        const moveCard = (direction) => {
            if (cards.length <= 1) {
                return;
            }

            currentIndex = (currentIndex + direction + cards.length) % cards.length;
            showingResponse = false;
            updateRevisionCard();
        };

        prevButton?.addEventListener('click', () => moveCard(-1));
        nextButton?.addEventListener('click', () => moveCard(1));
        updateRevisionCard();
    }
});
