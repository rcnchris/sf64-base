import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        this.element.setAttribute('class', 'shadow')
        this.element.setAttribute('title', this.element.dataset.src)
        this.element.setAttribute('data-toggle', 'tooltip')

        const defaultWidth = 50
        const colors = ['#1abc9c', '#3498db', '#9b59b6', '#f1c40f', '#d35400', '#e74c3c']

        var width = this.element.getAttribute('width')
        if (width == null) {
            width = defaultWidth;
            this.element.setAttribute('width', width)
        }
        var height = this.element.getAttribute('height')
        if (height == null) {
            height = width;
            this.element.setAttribute('height', height)
        }

        var color = this.element.dataset.color
        if (color == null) {
            color = colors[Math.floor(Math.random() * colors.length)]
        }

        const initials = this.element.dataset.src
            .split(' ')
            .map((word) => word.substring(0, 1))
            .join('')
        const font = 'Helvetica'
        const fontSize = (width / 2) * 1.1

        var ctx = this.element.getContext('2d')

        ctx.fillStyle = color
        ctx.beginPath()
        ctx.roundRect(0, 0, width, height, [7]);
        ctx.fill()

        ctx.font = "normal " + fontSize + "px " + font
        ctx.textAlign = 'center'
        ctx.textBaseline = 'middle'
        ctx.fillStyle = "#ecf0f1"
        ctx.fillText(initials, width / 2, height / 2)
    }
}
