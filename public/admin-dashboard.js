const dashboardShell = document.querySelector('.dashboard-shell');
const sidebarToggle = document.querySelector('#sidebarToggle');
const languageToggle = document.querySelector('#languageToggle');
const modalTriggers = document.querySelectorAll('[data-modal-target]');
const modalCloseControls = document.querySelectorAll('[data-modal-close]');
const customerSearchInputs = document.querySelectorAll('[data-customer-search]');
const orderForms = document.querySelectorAll('[data-order-form]');
const productForms = document.querySelectorAll('[data-product-form]');
const globalSearch = document.querySelector('[data-global-search]');
const notificationMenu = document.querySelector('[data-notification-menu]');
const dashboardLanguageStorageKey = 'foodify-admin-language';
let currentDashboardLanguage = localStorage.getItem(dashboardLanguageStorageKey) || 'en';

const dashboardTranslations = () => window.foodifyDashboardTranslations || { en: {}, ar: {} };
const translateDashboardText = (value, language = currentDashboardLanguage) => dashboardTranslations()[language]?.[value] || value;

if (dashboardShell && sidebarToggle) {
    const storageKey = 'foodify-sidebar-collapsed';
    const savedState = localStorage.getItem(storageKey);

    if (savedState === 'true') {
        dashboardShell.classList.add('sidebar-collapsed');
        sidebarToggle.setAttribute('aria-expanded', 'false');
    }

    sidebarToggle.addEventListener('click', () => {
        const isCollapsed = dashboardShell.classList.toggle('sidebar-collapsed');
        sidebarToggle.setAttribute('aria-expanded', String(!isCollapsed));
        localStorage.setItem(storageKey, String(isCollapsed));
    });
}

modalTriggers.forEach((trigger) => {
    trigger.addEventListener('click', () => {
        const modal = document.querySelector(trigger.dataset.modalTarget);

        if (!modal) {
            return;
        }

        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
    });
});

modalCloseControls.forEach((control) => {
    control.addEventListener('click', () => {
        const modal = control.closest('.modal-backdrop');

        if (!modal) {
            return;
        }

        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
    });
});

document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') {
        return;
    }

    document.querySelectorAll('.modal-backdrop.is-open').forEach((modal) => {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
    });
});

if (globalSearch) {
    const input = globalSearch.querySelector('[data-global-search-input]');
    const results = globalSearch.querySelector('[data-global-search-results]');
    const previewUrl = globalSearch.dataset.searchPreviewUrl;
    let timer;
    const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (character) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;',
    }[character]));

    const renderGroups = (payload, query) => {
        if (!results) {
            return;
        }

        const groups = payload.groups || [];

        if (!query || groups.length === 0) {
            results.classList.remove('is-open');
            results.innerHTML = '';
            return;
        }

        results.innerHTML = groups.map((group) => `
            <section>
                <h3>${escapeHtml(translateDashboardText(group.label))} <span>${escapeHtml(group.count)}</span></h3>
                ${group.items.map((item) => `<a href="${escapeHtml(item.url)}">${escapeHtml(item.title)}</a>`).join('')}
            </section>
        `).join('') + `<a class="view-all-search" href="${escapeHtml(payload.all_url)}">${escapeHtml(translateDashboardText('View all results'))}</a>`;
        results.classList.add('is-open');
    };

    input?.addEventListener('input', () => {
        clearTimeout(timer);
        const query = input.value.trim();

        timer = setTimeout(async () => {
            if (!query) {
                renderGroups({ groups: [] }, query);
                return;
            }

            const response = await fetch(`${previewUrl}?q=${encodeURIComponent(query)}`, {
                headers: { Accept: 'application/json' },
            });

            renderGroups(await response.json(), query);
        }, 250);
    });

    document.addEventListener('click', (event) => {
        if (!globalSearch.contains(event.target)) {
            results?.classList.remove('is-open');
        }
    });
}

if (notificationMenu) {
    const toggle = notificationMenu.querySelector('[data-notification-toggle]');
    const dropdown = notificationMenu.querySelector('[data-notification-dropdown]');

    const closeNotifications = () => {
        dropdown?.classList.remove('is-open');
        toggle?.setAttribute('aria-expanded', 'false');
    };

    toggle?.addEventListener('click', () => {
        const isOpen = dropdown?.classList.toggle('is-open') || false;
        toggle.setAttribute('aria-expanded', String(isOpen));
    });

    document.addEventListener('click', (event) => {
        if (!notificationMenu.contains(event.target)) {
            closeNotifications();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeNotifications();
        }
    });
}

if (languageToggle) {
    const originalTitle = document.title;
    const ignoredTranslationParents = new Set(['SCRIPT', 'STYLE', 'SVG', 'NOSCRIPT', 'TEXTAREA']);

    const applyTextTranslations = (language) => {
        const walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT, {
            acceptNode(node) {
                const parent = node.parentElement;

                if (!parent || ignoredTranslationParents.has(parent.tagName)) {
                    return NodeFilter.FILTER_REJECT;
                }

                return node.nodeValue.trim() ? NodeFilter.FILTER_ACCEPT : NodeFilter.FILTER_REJECT;
            },
        });

        const textNodes = [];

        while (walker.nextNode()) {
            textNodes.push(walker.currentNode);
        }

        textNodes.forEach((node) => {
            node.foodifyOriginalText = node.foodifyOriginalText || node.nodeValue.trim();

            const leadingWhitespace = node.nodeValue.match(/^\s*/)?.[0] || '';
            const trailingWhitespace = node.nodeValue.match(/\s*$/)?.[0] || '';

            node.nodeValue = `${leadingWhitespace}${translateDashboardText(node.foodifyOriginalText, language)}${trailingWhitespace}`;
        });

        document.querySelectorAll('input[placeholder], textarea[placeholder]').forEach((field) => {
            field.dataset.originalPlaceholder = field.dataset.originalPlaceholder || field.getAttribute('placeholder');
            field.setAttribute('placeholder', translateDashboardText(field.dataset.originalPlaceholder, language));
        });

        document.querySelectorAll('[title], [aria-label]').forEach((element) => {
            ['title', 'aria-label'].forEach((attribute) => {
                const value = element.getAttribute(attribute);

                if (!value) {
                    return;
                }

                const originalAttribute = `original${attribute.replace(/(^|-)([a-z])/g, (_, __, letter) => letter.toUpperCase())}`;

                element.dataset[originalAttribute] = element.dataset[originalAttribute] || value;
                element.setAttribute(attribute, translateDashboardText(element.dataset[originalAttribute], language));
            });
        });

        document.title = translateDashboardText(originalTitle, language);
    };

    const applyLanguage = (language) => {
        const isArabic = language === 'ar';

        currentDashboardLanguage = language;
        document.documentElement.lang = isArabic ? 'ar' : 'en';
        document.documentElement.dir = isArabic ? 'rtl' : 'ltr';
        applyTextTranslations(language);
        languageToggle.setAttribute('aria-pressed', String(isArabic));
        languageToggle.querySelector('span').textContent = isArabic ? 'EN' : 'AR';
    };

    applyLanguage(currentDashboardLanguage);

    languageToggle.addEventListener('click', () => {
        const nextLanguage = document.documentElement.lang === 'ar' ? 'en' : 'ar';

        localStorage.setItem(dashboardLanguageStorageKey, nextLanguage);
        applyLanguage(nextLanguage);
    });
}

const normalizeDigits = (value) => value.replace(/\D/g, '');
const localPhoneVariant = (digits) => digits.startsWith('20') ? `0${digits.slice(2)}` : digits;

customerSearchInputs.forEach((input) => {
    const combobox = input.closest('.customer-combobox');
    const hiddenInput = combobox?.querySelector('[data-customer-id]');
    const clearButton = combobox?.querySelector('[data-customer-clear]');
    const options = Array.from(combobox?.querySelectorAll('[data-customer-option]') || []);

    const rankOption = (option, query) => {
        const normalizedQuery = query.trim().toLowerCase();
        const queryDigits = normalizeDigits(normalizedQuery);
        const queryLocalPhone = localPhoneVariant(queryDigits);
        const name = option.dataset.customerName || '';
        const phone = option.dataset.customerPhone || '';
        const localPhone = option.dataset.customerLocalPhone || '';
        const searchText = option.dataset.customerSearch || '';

        if (!normalizedQuery) {
            return 10;
        }

        if (queryDigits && (phone === queryDigits || localPhone === queryDigits || phone === queryLocalPhone || localPhone === queryLocalPhone)) {
            return 0;
        }

        if (name === normalizedQuery) {
            return 1;
        }

        if (queryDigits && (phone.startsWith(queryDigits) || localPhone.startsWith(queryDigits) || phone.startsWith(queryLocalPhone) || localPhone.startsWith(queryLocalPhone))) {
            return 2;
        }

        if (name.startsWith(normalizedQuery)) {
            return 3;
        }

        if (queryDigits && (phone.includes(queryDigits) || localPhone.includes(queryDigits) || phone.includes(queryLocalPhone) || localPhone.includes(queryLocalPhone))) {
            return 4;
        }

        if (searchText.includes(normalizedQuery)) {
            return 5;
        }

        return 99;
    };

    const refreshOptions = () => {
        const ranked = options
            .map((option) => ({ option, rank: rankOption(option, input.value) }))
            .sort((a, b) => a.rank - b.rank);

        ranked.forEach(({ option, rank }) => {
            option.classList.toggle('is-hidden', rank === 99);
            option.parentElement?.appendChild(option);
        });
    };

    const showAllOptions = () => {
        options.forEach((option) => {
            option.classList.remove('is-hidden');
            option.parentElement?.appendChild(option);
        });
    };

    input.addEventListener('input', () => {
        if (hiddenInput) {
            hiddenInput.value = '';
        }
        refreshOptions();
    });

    input.addEventListener('focus', () => {
        if (!input.value.trim()) {
            showAllOptions();
        }
    });

    clearButton?.addEventListener('click', () => {
        input.value = '';
        if (hiddenInput) {
            hiddenInput.value = '';
        }
        options.forEach((option) => option.classList.remove('is-selected'));
        showAllOptions();
        input.focus();
    });

    options.forEach((option) => {
        option.addEventListener('click', () => {
            options.forEach((currentOption) => currentOption.classList.remove('is-selected'));
            option.classList.add('is-selected');
            input.value = option.dataset.customerLabel || '';
            if (hiddenInput) {
                hiddenInput.value = option.dataset.customerId || '';
            }
            refreshOptions();
        });
    });

    refreshOptions();
});

orderForms.forEach((form) => {
    const builder = form.querySelector('[data-order-meal-builder]');

    if (!builder) {
        return;
    }

    const searchInput = builder.querySelector('[data-meal-search]');
    const mealOptions = Array.from(builder.querySelectorAll('[data-meal-option]'));
    const itemsBody = builder.querySelector('[data-order-items-body]');
    const template = builder.querySelector('[data-order-item-template]');
    const emptyState = builder.querySelector('[data-empty-order-items]');
    const formatCurrency = (value) => `$${Number(value).toFixed(2)}`;

    const rows = () => Array.from(itemsBody?.querySelectorAll('[data-order-item-row]') || []);

    const updateRowTotal = (row) => {
        const quantityInput = row.querySelector('[data-item-quantity]');
        const totalCell = row.querySelector('[data-item-total]');
        const price = Number(row.dataset.mealPrice || 0);
        const quantity = Number(quantityInput?.value || 1);

        if (totalCell) {
            totalCell.textContent = formatCurrency(price * quantity);
        }
    };

    const syncRows = () => {
        rows().forEach((row, index) => {
            const mealInput = row.querySelector('[data-item-meal-id]');
            const quantityInput = row.querySelector('[data-item-quantity]');

            if (mealInput) {
                mealInput.name = `items[${index}][meal_id]`;
            }

            if (quantityInput) {
                quantityInput.name = `items[${index}][quantity]`;
            }

            updateRowTotal(row);
        });

        emptyState?.classList.toggle('is-hidden', rows().length > 0);
    };

    const addMealRow = (option) => {
        if (!itemsBody || !template) {
            return;
        }

        const mealId = option.dataset.mealId || '';
        const existingRow = rows().find((row) => row.dataset.mealId === mealId);

        if (existingRow) {
            const quantityInput = existingRow.querySelector('[data-item-quantity]');

            if (quantityInput) {
                quantityInput.value = String(Math.min(Number(quantityInput.value || 1) + 1, 99));
                quantityInput.focus();
            }

            syncRows();
            return;
        }

        const fragment = template.content.cloneNode(true);
        const row = fragment.querySelector('[data-order-item-row]');
        const mealInput = fragment.querySelector('[data-item-meal-id]');
        const nameCell = fragment.querySelector('[data-item-name]');
        const priceCell = fragment.querySelector('[data-item-price]');

        row.dataset.mealId = mealId;
        row.dataset.mealPrice = option.dataset.mealPrice || '0';
        mealInput.value = mealId;
        nameCell.textContent = option.dataset.mealName || '';
        priceCell.textContent = formatCurrency(option.dataset.mealPrice || 0);

        itemsBody.appendChild(fragment);
        syncRows();
    };

    const filterMealOptions = () => {
        const query = (searchInput?.value || '').trim().toLowerCase();

        mealOptions.forEach((option) => {
            option.classList.toggle('is-hidden', query !== '' && !(option.dataset.mealSearch || '').includes(query));
        });
    };

    mealOptions.forEach((option) => {
        option.addEventListener('click', () => {
            addMealRow(option);

            if (searchInput) {
                searchInput.value = '';
            }

            filterMealOptions();
        });
    });

    searchInput?.addEventListener('input', filterMealOptions);

    builder.addEventListener('input', (event) => {
        if (event.target?.matches('[data-item-quantity]')) {
            syncRows();
        }
    });

    builder.addEventListener('click', (event) => {
        const removeButton = event.target?.closest('[data-remove-order-item]');

        if (!removeButton) {
            return;
        }

        removeButton.closest('[data-order-item-row]')?.remove();
        syncRows();
    });

    form.addEventListener('submit', syncRows);
    filterMealOptions();
    syncRows();
});

productForms.forEach((form) => {
    const nutritionJsonInput = form.querySelector('[data-nutrition-json]');
    const nutritionInputs = Array.from(form.querySelectorAll('[data-nutrition-key]'));
    const ingredientsJsonInput = form.querySelector('[data-ingredients-json]');
    const ingredientTags = form.querySelector('[data-ingredient-tags]');
    const newIngredientInput = form.querySelector('[data-new-ingredient]');
    const addIngredientButton = form.querySelector('[data-add-ingredient]');

    const createIngredientChip = (value = '') => {
        if (!ingredientTags) {
            return;
        }

        const chip = document.createElement('span');
        chip.className = 'ingredient-chip';
        chip.dataset.ingredientChip = '';
        chip.innerHTML = `
            <span class="ingredient-row-index"></span>
            <input type="text" value="" data-ingredient-input aria-label="المكون">
            <button type="button" data-remove-ingredient aria-label="حذف المكون">×</button>
        `;
        chip.querySelector('[data-ingredient-input]').value = value;
        ingredientTags.appendChild(chip);
        refreshIngredientIndexes();
    };

    const refreshIngredientIndexes = () => {
        form.querySelectorAll('[data-ingredient-chip]').forEach((chip, index) => {
            const indexElement = chip.querySelector('.ingredient-row-index');

            if (indexElement) {
                indexElement.textContent = String(index + 1);
            }
        });
    };

    const syncNutritionJson = () => {
        if (!nutritionJsonInput) {
            return;
        }

        const nutrition = {};

        nutritionInputs.forEach((input) => {
            const value = input.value.trim();

            if (value !== '') {
                nutrition[input.dataset.nutritionKey] = Number(value);
            }
        });

        nutritionJsonInput.value = Object.keys(nutrition).length ? JSON.stringify(nutrition) : '';
    };

    const syncIngredientsJson = () => {
        if (!ingredientsJsonInput) {
            return;
        }

        const ingredients = Array.from(form.querySelectorAll('[data-ingredient-input]'))
            .map((input) => input.value.trim())
            .filter(Boolean);

        ingredientsJsonInput.value = ingredients.length ? JSON.stringify(ingredients) : '';
    };

    const addIngredient = () => {
        const value = newIngredientInput?.value.trim() || '';

        if (!value) {
            newIngredientInput?.focus();
            return;
        }

        createIngredientChip(value);
        newIngredientInput.value = '';
        syncIngredientsJson();
        newIngredientInput.focus();
    };

    nutritionInputs.forEach((input) => input.addEventListener('input', syncNutritionJson));
    ingredientTags?.addEventListener('input', syncIngredientsJson);
    ingredientTags?.addEventListener('click', (event) => {
        const removeButton = event.target?.closest('[data-remove-ingredient]');

        if (!removeButton) {
            return;
        }

        removeButton.closest('[data-ingredient-chip]')?.remove();
        refreshIngredientIndexes();
        syncIngredientsJson();
    });

    addIngredientButton?.addEventListener('click', addIngredient);
    newIngredientInput?.addEventListener('keydown', (event) => {
        if (event.key !== 'Enter') {
            return;
        }

        event.preventDefault();
        addIngredient();
    });

    form.closest('form')?.addEventListener('submit', () => {
        syncNutritionJson();
        syncIngredientsJson();
    });

    syncNutritionJson();
    refreshIngredientIndexes();
    syncIngredientsJson();
});
