@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0" id="formTitle">Добавить контакт</h5>
            </div>
            <div class="card-body">
                <form id="contactForm">
                    <input type="hidden" id="contactId">

                    <div class="mb-3">
                        <label for="name" class="form-label">Имя</label>
                        <input type="text" class="form-control" id="name" required>
                        <div class="invalid-feedback" id="nameError"></div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" required>
                        <div class="invalid-feedback" id="emailError"></div>
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">Телефон</label>
                        <input type="text" class="form-control" id="phone">
                        <div class="invalid-feedback" id="phoneError"></div>
                    </div>

                    <div class="mb-3">
                        <label for="tags" class="form-label">Теги (разделенные запятыми)</label>
                        <input type="text" class="form-control" id="tags">
                        <div class="invalid-feedback" id="tagsError"></div>
                    </div>

                    <div class="mb-3">
                        <label for="comment" class="form-label">Коментарий</label>
                        <textarea class="form-control" id="comment" rows="3"></textarea>
                        <div class="invalid-feedback" id="commentError"></div>
                    </div>

                    <button type="submit" class="btn btn-primary" id="saveBtn">Сохранить</button>
                    <button type="button" class="btn btn-secondary" id="resetBtn">Сбросить</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Контакты</h5>
            </div>
            <div class="card-body">
                <div id="tagFilters" class="mb-3">
                    <strong>Фильтр по тегу:</strong>
                    <span class="tag tag-filter active" data-tag="all">Все</span>
                    <!-- Фильтры тегов будут заполнены JS -->
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Имя</th>
                                <th>Email</th>
                                <th>Телефон</th>
                                <th>Теги</th>
                                <th>Коментарий</th>
                                <th>Редактировать</th>
                            </tr>
                        </thead>
                        <tbody id="contactsList">
                            <!-- Список контактов будет заполнен JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // API configuration
        const apiUrl = '{{ url("/api/contacts") }}';
        const apiToken = 'stab-app-secret-token'; // Такой же как в ApiTokenMiddleware

        // Axios configuration
        axios.defaults.headers.common['Authorization'] = `Bearer ${apiToken}`;

        // DOM elements
        const contactForm = document.getElementById('contactForm');
        const formTitle = document.getElementById('formTitle');
        const contactId = document.getElementById('contactId');
        const nameInput = document.getElementById('name');
        const emailInput = document.getElementById('email');
        const phoneInput = document.getElementById('phone');
        const tagsInput = document.getElementById('tags');
        const commentInput = document.getElementById('comment');
        const saveBtn = document.getElementById('saveBtn');
        const resetBtn = document.getElementById('resetBtn');
        const contactsList = document.getElementById('contactsList');
        const tagFilters = document.getElementById('tagFilters');

        let contacts = [];
        let allTags = new Set(['all']);
        let activeTag = 'all';

        // Функции
        function resetForm() {
            contactForm.reset();
            contactId.value = '';
            formTitle.textContent = 'Добавить контакт';
            clearValidationErrors();
        }

        function clearValidationErrors() {
            document.querySelectorAll('.is-invalid').forEach(el => {
                el.classList.remove('is-invalid');
            });
        }

        function showValidationErrors(errors) {
            clearValidationErrors();

            if (errors.name) {
                nameInput.classList.add('is-invalid');
                document.getElementById('nameError').textContent = errors.name[0];
            }

            if (errors.email) {
                emailInput.classList.add('is-invalid');
                document.getElementById('emailError').textContent = errors.email[0];
            }

            if (errors.phone) {
                phoneInput.classList.add('is-invalid');
                document.getElementById('phoneError').textContent = errors.phone[0];
            }

            if (errors.tags) {
                tagsInput.classList.add('is-invalid');
                document.getElementById('tagsError').textContent = errors.tags[0];
            }

            if (errors.comment) {
                commentInput.classList.add('is-invalid');
                document.getElementById('commentError').textContent = errors.comment[0];
            }
        }

        function loadContacts() {
            axios.get(apiUrl)
                .then(response => {
                    contacts = response.data;
                    extractAllTags();
                    renderTagFilters();
                    renderContacts();
                })
                .catch(error => {
                    console.error('Ошибка загрузки контактов:', error);
                    alert('Ошибка загрузки контактов. Проверьте консоль для получения деталей.');
                });
        }

        function extractAllTags() {
            allTags = new Set(['all']);

            contacts.forEach(contact => {
                if (contact.tags && Array.isArray(contact.tags)) {
                    contact.tags.forEach(tag => {
                        allTags.add(tag);
                    });
                }
            });
        }

        function renderTagFilters() {
            // Очистить существующие фильтры, кроме «Все»
            const allFilter = tagFilters.querySelector('[data-tag="all"]');
            tagFilters.innerHTML = '';
            tagFilters.appendChild(document.createTextNode('Фильтр по тегу: '));
            tagFilters.appendChild(allFilter);

            // Добавить новые фильтры тегов
            allTags.forEach(tag => {
                if (tag === 'all') return;

                const tagElement = document.createElement('span');
                tagElement.className = 'tag tag-filter';
                tagElement.setAttribute('data-tag', tag);
                tagElement.textContent = tag;

                if (tag === activeTag) {
                    tagElement.classList.add('active');
                }

                tagElement.addEventListener('click', () => {
                    setActiveTag(tag);
                });

                tagFilters.appendChild(tagElement);
            });
        }

        function setActiveTag(tag) {
            activeTag = tag;

            // Обновить активный класс
            document.querySelectorAll('.tag-filter').forEach(el => {
                if (el.getAttribute('data-tag') === tag) {
                    el.classList.add('active');
                } else {
                    el.classList.remove('active');
                }
            });

            renderContacts();
        }

        function renderContacts() {
            contactsList.innerHTML = '';

            const filteredContacts = contacts.filter(contact => {
                if (activeTag === 'all') return true;
                return contact.tags && Array.isArray(contact.tags) && contact.tags.includes(activeTag);
            });

            if (filteredContacts.length === 0) {
                const tr = document.createElement('tr');
                tr.innerHTML = '<td colspan="5" class="text-center">Контакты не найдены.</td>';
                contactsList.appendChild(tr);
                return;
            }

            filteredContacts.forEach(contact => {
                const tr = document.createElement('tr');

                tr.innerHTML = `
                    <td>${contact.name}</td>
                    <td>${contact.email}</td>
                    <td>${contact.phone || '-'}</td>
                    <td>${renderTags(contact.tags)}</td>
                    <td>${contact.comment || '-'}</td>
                    <td>
                        <button class="btn btn-sm btn-primary edit-btn" data-id="${contact.id}">Редактировать</button>
                        <button class="btn btn-sm btn-danger delete-btn" data-id="${contact.id}">Удалить</button>
                    </td>
                `;

                contactsList.appendChild(tr);

                // Добавление прослушивателя события
                tr.querySelector('.edit-btn').addEventListener('click', () => {
                    editContact(contact);
                });

                tr.querySelector('.delete-btn').addEventListener('click', () => {
                    deleteContact(contact.id);
                });
            });
        }

        function renderTags(tags) {
            if (!tags || !Array.isArray(tags) || tags.length === 0) {
                return '-';
            }

            return tags.map(tag => `<span class="tag">${tag}</span>`).join(' ');
        }

        function saveContact(e) {
            e.preventDefault();
            clearValidationErrors();

            const id = contactId.value;
            const isEditing = id !== '';

            const formData = {
                name: nameInput.value,
                email: emailInput.value,
                phone: phoneInput.value,
                tags: tagsInput.value ? tagsInput.value.split(',').map(tag => tag.trim()).filter(tag => tag !== '') : [],
                comment: commentInput.value
            };

            const method = isEditing ? 'put' : 'post';
            const url = isEditing ? `${apiUrl}/${id}` : apiUrl;

            axios[method](url, formData)
                .then(response => {
                    if (isEditing) {
                        // Обновить существующий контакт
                        const index = contacts.findIndex(c => c.id == id);
                        if (index !== -1) {
                            contacts[index] = response.data;
                        }
                    } else {
                        // Добавить новый контакт
                        contacts.push(response.data);
                    }

                    extractAllTags();
                    renderTagFilters();
                    renderContacts();
                    resetForm();
                    alert(isEditing ? 'Контакт успешно обновлен!' : 'Контакт успешно добавлен!');
                })
                .catch(error => {
                    console.error('Ошибка сохранения контакта:', error);

                    if (error.response && error.response.data && error.response.data.errors) {
                        showValidationErrors(error.response.data.errors);
                    } else {
                        alert('Ошибка сохранения контакта. Проверьте консоль для получения деталей.');
                    }
                });
        }

        function editContact(contact) {
            contactId.value = contact.id;
            nameInput.value = contact.name;
            emailInput.value = contact.email;
            phoneInput.value = contact.phone || '';
            tagsInput.value = Array.isArray(contact.tags) ? contact.tags.join(', ') : '';
            commentInput.value = contact.comment || '';

            formTitle.textContent = 'Редактировать контакт';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function deleteContact(id) {
            if (!confirm('Вы уверены что хотите удалить контакт?')) {
                return;
            }

            axios.delete(`${apiUrl}/${id}`)
                .then(() => {
                    contacts = contacts.filter(c => c.id != id);
                    extractAllTags();
                    renderTagFilters();
                    renderContacts();
                    resetForm();
                    alert('Контакт успешно удален!');
                })
                .catch(error => {
                    console.error('Ошибка удаления контакта:', error);
                    alert('Ошибка удаления контакта. Проверьте консоль для получения деталей.');
                });
        }

        // Прослушиватель событий
        contactForm.addEventListener('submit', saveContact);
        resetBtn.addEventListener('click', resetForm);
        document.querySelector('.tag-filter[data-tag="all"]').addEventListener('click', () => {
            setActiveTag('all');
        });

        // Инициализация
        loadContacts();
    });
</script>
@endsection
