import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        const form = document.getElementById('searchForm');
        const documentsGrid = document.getElementById('documentsGrid');
        const inputs = form.querySelectorAll('input, select');
        const resetButton = document.getElementById('resetButton');
        const searchInput = document.getElementById('searchInput');

        const updateDocuments = (queryString = '') => {
            fetch(`${form.action}?${queryString}`, {
                method: 'GET',
                headers: {
                    'Accept': 'text/html',
                },
            })
            .then(response => response.text())
            .then(html => {
                documentsGrid.innerHTML = new DOMParser().parseFromString(html, 'text/html').getElementById('documentsGrid').innerHTML;
            });
        };

        const buildQueryString = () => {
            return new URLSearchParams(new FormData(form)).toString();
        };

        inputs.forEach(input => {
            input.addEventListener('change', () => {
                const queryString = buildQueryString();
                updateDocuments(queryString);
            });
        });

        searchInput.addEventListener('input', () => {
            const queryString = buildQueryString();
            updateDocuments(queryString);
        });

        resetButton.addEventListener('click', () => {
            form.reset();
            searchInput.value = '';
            const queryString = buildQueryString();
            updateDocuments(queryString);
        });

        const initialQueryString = buildQueryString();
        updateDocuments(initialQueryString);
    }
}
