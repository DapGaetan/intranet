import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        document.querySelectorAll('.feature-item').forEach(item => {
            item.addEventListener('click', function() {
                this.classList.toggle('active');
            });
        });
    }
}
