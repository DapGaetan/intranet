import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        console.log("coucou");

        const form = document.getElementById('searchForm');
        const profilesGrid = document.getElementById('profilesGrid');
        const inputs = form.querySelectorAll('input, select');
        const resetButton = document.getElementById('resetButton');
        const searchInput = document.getElementById('searchInput');

        const updateProfiles = (queryString = '') => {
            fetch(`${form.action}?${queryString}`, {
                method: 'GET',
                headers: {
                    'Accept': 'text/html',
                },
            })
            .then(response => response.text())
            .then(html => {
                profilesGrid.innerHTML = new DOMParser().parseFromString(html, 'text/html').getElementById('profilesGrid').innerHTML;
            });
        };

        const buildQueryString = () => {
            return new URLSearchParams(new FormData(form)).toString();
        };

        inputs.forEach(input => {
            input.addEventListener('change', () => {
                const queryString = buildQueryString();
                updateProfiles(queryString);
            });
        });

        searchInput.addEventListener('input', () => {
            const queryString = buildQueryString();
            updateProfiles(queryString);
        });

        resetButton.addEventListener('click', () => {
            form.reset();
            searchInput.value = '';
            const queryString = buildQueryString();
            updateProfiles(queryString);
        });

        const initialQueryString = buildQueryString();
        updateProfiles(initialQueryString);
    }
}
