import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['whiteboard', 'color', 'brushSize'];

    connect() {
        this.canvas = document.getElementById('whiteboard');
        this.ctx = this.canvas.getContext('2d');

        this.colorInput = document.getElementById('color');
        this.brushSizeInput = document.getElementById('brushSize');
        
        this.isDrawing = false;
        this.currentColor = this.colorInput.value;
        this.currentBrushSize = this.brushSizeInput.value;

        this.setupCanvas();
        this.setupEvents();
    }

    setupCanvas() {
        // Set canvas background to white
        this.ctx.fillStyle = "white";
        this.ctx.fillRect(0, 0, this.canvas.width, this.canvas.height);
    }

    setupEvents() {
        // Mouse events for drawing
        this.canvas.addEventListener('mousedown', this.startDrawing.bind(this));
        this.canvas.addEventListener('mousemove', this.draw.bind(this));
        this.canvas.addEventListener('mouseup', this.stopDrawing.bind(this));
        this.canvas.addEventListener('mouseout', this.stopDrawing.bind(this));

        // Input changes for color and brush size
        this.colorInput.addEventListener('input', (event) => {
            this.currentColor = event.target.value;
        });

        this.brushSizeInput.addEventListener('input', (event) => {
            this.currentBrushSize = event.target.value;
        });
    }

    startDrawing(event) {
        this.isDrawing = true;
        this.ctx.beginPath();
        this.ctx.moveTo(event.offsetX, event.offsetY);
    }

    draw(event) {
        if (!this.isDrawing) return;

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
        this.setupCanvas(); // Reset the canvas
    }

    drawRectangle(event) {
        const width = 100; // Largeur fixe
        const height = 50; // Hauteur fixe
        const x = event.offsetX; // Position X de clic
        const y = event.offsetY; // Position Y de clic

        this.ctx.fillStyle = this.currentColor;
        this.ctx.fillRect(x, y, width, height);
    }
    
    drawCircle(event) {
        const radius = 25; // Rayon fixe
        const x = event.offsetX; // Position X de clic
        const y = event.offsetY; // Position Y de clic

        this.ctx.beginPath();
        this.ctx.arc(x, y, radius, 0, Math.PI * 2);
        this.ctx.fillStyle = this.currentColor;
        this.ctx.fill();
        this.ctx.closePath();
    }
    
    addImage(event) {
        const img = new Image();
        img.src = prompt("Enter image URL:"); // Demande l'URL de l'image à l'utilisateur
        img.onload = () => {
            const x = event.offsetX; // Position X de clic
            const y = event.offsetY; // Position Y de clic
            this.ctx.drawImage(img, x, y);
        };
    }

    exportImage() {
        const link = document.createElement('a');
        link.download = 'whiteboard.jpg';
        link.href = this.canvas.toDataURL('image/jpeg', 1.0);
        link.click();
    }
}
