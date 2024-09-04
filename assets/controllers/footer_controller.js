import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        this.updateBrowserIcon();
        this.updateTwitterIcon();
    }

    updateBrowserIcon() {
        var iconElement = document.getElementById('browser-icon');
        if (iconElement) {
            iconElement.className = 'fa-brands ' + this.getBrowserIcon();
        }
    }

    updateTwitterIcon() {
        var iconElement = document.getElementById('twitter-icon');
        if (iconElement) {
            var isOldLogo = Math.random() < 0.5;
            iconElement.className = isOldLogo ? 'fa-brands fa-twitter' : 'fa-brands fa-x-twitter';
        }
    }

    getBrowserIcon() {
        var userAgent = navigator.userAgent.toLowerCase();
        if (userAgent.indexOf('chrome') > -1 && userAgent.indexOf('edg') === -1 && userAgent.indexOf('opr') === -1) {
            return 'fa-chrome';
        } else if (userAgent.indexOf('firefox') > -1) {
            return 'fa-firefox-browser';
        } else if (userAgent.indexOf('safari') > -1 && userAgent.indexOf('chrome') === -1) {
            return 'fa-safari';
        } else if (userAgent.indexOf('edg') > -1) {
            return 'fa-edge';
        } else if (userAgent.indexOf('opr') > -1 || userAgent.indexOf('opera') > -1) {
            return 'fa-opera';
        } else if (userAgent.indexOf('msie') > -1 || userAgent.indexOf('trident') > -1) {
            return 'fa-internet-explorer';
        } else {
            return 'fa-globe'; // Default icon
        }
    }
}

