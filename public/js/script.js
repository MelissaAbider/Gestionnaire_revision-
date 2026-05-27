/**
 * Script JavaScript principal
 *
 * RESPONSABLES :
 * - Melissa ABIDER : validations des formulaires inscription/connexion.
 * - Jana CHEHWAN : validation des formulaires de matieres.
 * - Asma AZRI : interactions du formulaire de fiches et carte de revision.
 * - Alban COUSIN : recherche et compteur des utilisateurs partages.
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

    const registerForm = document.querySelector('[data-register-form]');

    if (registerForm) {
        // RESPONSABLE : Melissa ABIDER - validation front du formulaire d'inscription.
        const fields = {
            firstName: registerForm.querySelector('[name="firstName"]'),
            lastName: registerForm.querySelector('[name="lastName"]'),
            email: registerForm.querySelector('[name="email"]'),
            birthDate: registerForm.querySelector('[name="birthDate"]'),
            password: registerForm.querySelector('[name="password"]'),
            confirmPassword: registerForm.querySelector('[name="confirmPassword"]'),
        };

        const setFieldError = (name, message) => {
            const field = fields[name];
            const group = field?.closest('.form-group');
            const error = registerForm.querySelector(`[data-error-for="${name}"]`);

            group?.classList.toggle('has-error', message !== '');
            if (error) {
                error.textContent = message;
            }
        };

        const isValidBirthDate = (value) => {
            if (!/^\d{8}$/.test(value)) {
                return false;
            }

            const year = Number(value.slice(0, 4));
            const month = Number(value.slice(4, 6));
            const day = Number(value.slice(6, 8));
            const date = new Date(year, month - 1, day);

            return date.getFullYear() === year
                && date.getMonth() === month - 1
                && date.getDate() === day;
        };

        registerForm.addEventListener('submit', (event) => {
            const values = Object.fromEntries(
                Object.entries(fields).map(([name, field]) => [name, field?.value.trim() || ''])
            );
            const errors = {};

            if (values.firstName === '') errors.firstName = 'Le prénom est requis.';
            if (values.lastName === '') errors.lastName = 'Le nom est requis.';
            if (values.email === '') {
                errors.email = 'Le mail est requis.';
            } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(values.email)) {
                errors.email = 'Le mail doit respecter le format login@domaine.extension.';
            }
            if (values.birthDate === '') {
                errors.birthDate = 'La date de naissance est requise.';
            } else if (!isValidBirthDate(values.birthDate)) {
                errors.birthDate = 'La date de naissance doit respecter le format AAAAMMJJ.';
            }
            if (values.password === '') {
                errors.password = 'Le mot de passe est requis.';
            } else if (values.password.length < 6) {
                errors.password = 'Le mot de passe doit contenir au moins 6 caractères.';
            }
            if (values.confirmPassword === '') {
                errors.confirmPassword = 'La confirmation du mot de passe est requise.';
            } else if (values.password !== values.confirmPassword) {
                errors.confirmPassword = 'Les mots de passe ne correspondent pas.';
            }

            Object.keys(fields).forEach((name) => {
                setFieldError(name, errors[name] || '');
            });

            if (Object.keys(errors).length > 0) {
                event.preventDefault();
            }
        });
    }

    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    const loginForm = document.querySelector('[data-login-form]');

    if (loginForm) {
        // RESPONSABLE : Melissa ABIDER - validation front du formulaire de connexion.
        const email = loginForm.querySelector('[name="email"]');
        const password = loginForm.querySelector('[name="password"]');

        const setAuthError = (field, errorName, message) => {
            const group = field?.closest('.form-group');
            const error = loginForm.querySelector(`[data-error-for="${errorName}"]`);

            group?.classList.toggle('has-error', message !== '');
            if (error) {
                error.textContent = message;
            }
        };

        loginForm.addEventListener('submit', (event) => {
            const emailValue = email?.value.trim() || '';
            const passwordValue = password?.value || '';
            let hasErrors = false;

            if (emailValue === '') {
                setAuthError(email, 'loginEmail', 'Le mail est requis.');
                hasErrors = true;
            } else if (!emailPattern.test(emailValue)) {
                setAuthError(email, 'loginEmail', 'Le mail doit respecter le format login@domaine.extension.');
                hasErrors = true;
            } else {
                setAuthError(email, 'loginEmail', '');
            }

            if (passwordValue === '') {
                setAuthError(password, 'loginPassword', 'Le mot de passe est requis.');
                hasErrors = true;
            } else {
                setAuthError(password, 'loginPassword', '');
            }

            if (hasErrors) {
                event.preventDefault();
            }
        });
    }

    document.querySelectorAll('[data-matiere-form]').forEach((form) => {
        // RESPONSABLE : Jana CHEHWAN - validation front des formulaires de matieres.
        const nameInput = form.querySelector('[name="name"]');
        const error = form.querySelector('[data-matiere-error]');

        form.addEventListener('submit', (event) => {
            const message = (nameInput?.value.trim() || '') === ''
                ? 'Le nom de la matière est requis.'
                : '';

            form.classList.toggle('has-error', message !== '');
            if (error) {
                error.textContent = message;
            }

            if (message !== '') {
                event.preventDefault();
            }
        });
    });

    const flashcardForm = document.querySelector('[data-flashcard-form]');

    if (flashcardForm) {
        // RESPONSABLE : Asma AZRI - validation front des fiches et questions/reponses.
        const setFormFieldError = (field, message) => {
            const wrapper = field?.closest('.form-field');
            const fallbackError = field?.parentElement?.querySelector('[data-error-for]');

            wrapper?.classList.toggle('has-error', message !== '');
            if (fallbackError) {
                fallbackError.textContent = message;
            }
        };

        flashcardForm.addEventListener('submit', (event) => {
            const title = flashcardForm.querySelector('[name="title"]');
            const questionItems = Array.from(flashcardForm.querySelectorAll('[data-qa-item]'));
            const questionResponseError = flashcardForm.querySelector('[data-error-for="questionResponses"]');
            let hasErrors = false;

            if ((title?.value.trim() || '') === '') {
                setFormFieldError(title, 'Le titre est requis.');
                hasErrors = true;
            } else {
                setFormFieldError(title, '');
            }

            const invalidQuestionResponses = questionItems.some((item) => {
                const question = item.querySelector('[name="questions[]"]');
                const response = item.querySelector('[name="responses[]"]');
                const questionEmpty = (question?.value.trim() || '') === '';
                const responseEmpty = (response?.value.trim() || '') === '';

                item.classList.toggle('has-error', questionEmpty || responseEmpty);
                question?.classList.toggle('is-invalid', questionEmpty);
                response?.classList.toggle('is-invalid', responseEmpty);

                return questionEmpty || responseEmpty;
            });

            if (invalidQuestionResponses) {
                if (questionResponseError) {
                    questionResponseError.textContent = 'Chaque carte doit avoir une question et une réponse.';
                }
                hasErrors = true;
            } else if (questionResponseError) {
                questionResponseError.textContent = '';
            }

            if (hasErrors) {
                event.preventDefault();
            }
        });
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
        // RESPONSABLE : Alban COUSIN - recherche dans les destinataires du partage.
        const filterUsers = () => {
            const query = normalize(shareSearch.value);

            shareUsers.forEach((user) => {
                user.hidden = query !== '' && !normalize(user.dataset.shareUser || '').includes(query);
            });
        };

        shareSearch.addEventListener('input', filterUsers);
    }

    if (shareCount && shareCheckboxes.length > 0) {
        // RESPONSABLE : Alban COUSIN - compteur des utilisateurs selectionnes pour le partage.
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
        // RESPONSABLE : Asma AZRI - ajout/suppression dynamique des cartes question/reponse.
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
        // RESPONSABLE : Asma AZRI - navigation dans les cartes. Alexandre BRUGGER : enregistrement statistique de revision.
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
