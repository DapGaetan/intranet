import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['whiteboard', 'color', 'brushSize'];

    connect() {
        this.canvas = this.whiteboardTarget;
        this.ctx = this.canvas.getContext('2d');

        this.colorInput = this.colorTarget;
        this.brushSizeInput = this.brushSizeTarget;
        
        this.isDrawing = false;
        this.currentColor = this.colorInput.value;
        this.currentBrushSize = this.brushSizeInput.value;
        this.currentAction = null;

        this.setupCanvas();
        this.setupEvents();
        this.setupSpecialActions();

        window.addEventListener('resize', this.resizeCanvas.bind(this));
        this.resizeCanvas();
    }

    disconnect() {
        window.removeEventListener('resize', this.resizeCanvas.bind(this));
    }

    setupCanvas() {
        // Définir le fond du canvas en blanc
        this.ctx.fillStyle = "white";
        this.ctx.fillRect(0, 0, this.canvas.width, this.canvas.height);
    }

    resizeCanvas() {
        // Sauvegarder le contenu actuel du canvas
        const dataUrl = this.canvas.toDataURL();

        // Ajuster la taille du canvas
        this.canvas.width = this.canvas.clientWidth;
        this.canvas.height = this.canvas.clientHeight;

        // Redessiner le contenu sauvegardé
        const img = new Image();
        img.src = dataUrl;
        img.onload = () => {
            this.ctx.drawImage(img, 0, 0, this.canvas.width, this.canvas.height);
        };
    }

    setupEvents() {
        // Événements de souris pour dessiner
        this.canvas.addEventListener('mousedown', this.startDrawing.bind(this));
        this.canvas.addEventListener('mousemove', this.draw.bind(this));
        this.canvas.addEventListener('mouseup', this.stopDrawing.bind(this));
        this.canvas.addEventListener('mouseout', this.stopDrawing.bind(this));

        // Événements pour les inputs de couleur et de taille du pinceau
        this.colorInput.addEventListener('input', (event) => {
            this.currentColor = event.target.value;
        });

        this.brushSizeInput.addEventListener('input', (event) => {
            this.currentBrushSize = event.target.value;
        });
    }

    setupSpecialActions() {
        // Gestion des clics pour les actions spéciales
        this.canvas.addEventListener('click', this.handleCanvasClick.bind(this));
    }

    startDrawing(event) {
        if (this.currentAction) return; // Ne dessine pas si une action spéciale est sélectionnée

        this.isDrawing = true;
        this.ctx.beginPath();
        this.ctx.moveTo(event.offsetX, event.offsetY);
    }

    draw(event) {
        if (!this.isDrawing || this.currentAction) return;

        this.ctx.lineWidth = this.currentBrushSize;
        this.ctx.lineCap = 'round';
        this.ctx.strokeStyle = this.currentColor;

        this.ctx.lineTo(event.offsetX, event.offsetY);
        this.ctx.stroke();
    }

    stopDrawing() {
        if (this.isDrawing) {
            this.ctx.stroke();
            this.ctx.closePath();
            this.isDrawing = false;
        }
    }

    clearCanvas() {
        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
        this.setupCanvas();
    }

    drawRectangle() {
        this.setAction('drawRectangle');
    }
    
    drawCircle() {
        this.setAction('drawCircle');
    }
    
    addImage() {
        this.setAction('addImage');
    }

    setAction(action) {
        this.currentAction = action;
        // Optionnel: Indiquer visuellement l'action sélectionnée
        console.log(`Action sélectionnée: ${action}`);
    }

    exportImage() {
        const link = document.createElement('a');
        link.download = 'whiteboard.jpg';
        link.href = this.canvas.toDataURL('image/jpeg', 1.0);
        link.click();
    }

    handleCanvasClick(event) {
        if (this.currentAction === 'drawRectangle') {
            this.drawRectangleAt(event);
        } else if (this.currentAction === 'drawCircle') {
            this.drawCircleAt(event);
        } else if (this.currentAction === 'addImage') {
            this.addImageAt(event);
        }
        // Réinitialiser l'action après l'exécution
        this.currentAction = null;
    }

    drawRectangleAt(event) {
        const width = 100; // Largeur fixe ou dynamique
        const height = 50; // Hauteur fixe ou dynamique
        const x = event.offsetX;
        const y = event.offsetY;

        this.ctx.fillStyle = this.currentColor;
        this.ctx.fillRect(x, y, width, height);
    }

    drawCircleAt(event) {
        const radius = 25; // Rayon fixe ou dynamique
        const x = event.offsetX;
        const y = event.offsetY;

        this.ctx.beginPath();
        this.ctx.arc(x, y, radius, 0, Math.PI * 2);
        this.ctx.fillStyle = this.currentColor;
        this.ctx.fill();
        this.ctx.closePath();
    }

    addImageAt(event) {
        const imgUrl = prompt("Entrez l'URL de l'image:");
        if (!imgUrl) return;

        const img = new Image();
        img.crossOrigin = "anonymous"; // Pour éviter les problèmes CORS
        img.src = imgUrl;
        img.onload = () => {
            const x = event.offsetX;
            const y = event.offsetY;
            this.ctx.drawImage(img, x, y, 100, 100); // Taille fixe ou dynamique
        };
        img.onerror = () => {
            alert("Impossible de charger l'image. Vérifiez l'URL.");
        };
    }
}
