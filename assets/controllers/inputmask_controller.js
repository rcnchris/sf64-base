import '../vendor/inputmask/inputmask.index.js';
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        let phone_mask = new Inputmask('99.99.99.99.99');
        let siren_mask = new Inputmask('999-999-999');
        let siret_mask = new Inputmask('999-999-999-99999');
        let nif_mask = new Inputmask('aa-99-999-99-999');

        let maskType = this.element.dataset.type;
        if (maskType == 'phone') {
            phone_mask.mask(this.element);
        } else if (maskType == 'siret') {
            siret_mask.mask(this.element);
        } else if (maskType == 'siren') {
            siren_mask.mask(this.element);
        } else if (maskType == 'nif') {
            nif_mask.mask(this.element);
        }
    }
}