import { Controller } from '@hotwired/stimulus';

function clamp(n, min, max) {
    return Math.min(Math.max(n, min), max)
}

function randomNumberBetween(min, max) {
    return Math.floor(Math.random() * (max - min + 1) + min)
}

export default class extends Controller {
    connect() {
        const width = parseInt(this.element.getAttribute('width'), 10)
        const height = parseInt(this.element.getAttribute('height'), 10)
        const pieceWidth = parseInt(this.element.getAttribute('piece-width'), 10)
        const pieceHeight = parseInt(this.element.getAttribute('piece-height'), 10)
        const maxX = width - pieceWidth;
        const maxY = height - pieceHeight;

        this.element.classList.add('captcha');
        this.element.classList.add('captcha-waiting-interaction');
        this.element.style.setProperty('--width', `${width}px`)
        this.element.style.setProperty('--height', `${height}px`)
        this.element.style.setProperty('--pieceWidth', `${pieceWidth}px`)
        this.element.style.setProperty('--pieceHeight', `${pieceHeight}px`)
        this.element.style.setProperty('--image', `url(${this.element.getAttribute('src')})`)

        const input = this.element.querySelector('.captcha-answer');
        const piece = document.createElement('div')
        piece.classList.add('captcha-piece')
        this.element.appendChild(piece)

        let isDragging = false;
        let position = {
            x: randomNumberBetween(0, maxX),
            y: randomNumberBetween(0, maxY),
        };
        piece.style.setProperty('transform', `translate(${position.x}px, ${position.y}px)`)

        piece.addEventListener('pointerdown', e => {
            isDragging = true
            document.body.style.setProperty('user-select', 'none')
            this.element.classList.remove('captcha-waiting-interaction');
            piece.classList.add('is-moving')

            window.addEventListener('pointerup', () => {
                document.body.style.removeProperty('user-select')
                piece.classList.remove('is-moving')
                isDragging = false
            }, { once: true })
        })

        this.element.addEventListener('pointermove', e => {
            if (!isDragging) {
                return;
            }
            position.x = clamp(position.x + e.movementX, 0, maxX)
            position.y = clamp(position.y + e.movementY, 0, maxY)
            piece.style.setProperty('transform', `translate(${position.x}px, ${position.y}px)`)
            input.value = `${position.x}-${position.y}`
        })

    }
}