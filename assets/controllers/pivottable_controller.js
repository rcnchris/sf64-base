import '../vendor/pivottable/dist/pivot.min.css'
import '../vendor/pivottable/pivottable.index.js';
// import 'pivottable/dist/pivot.fr.min.js';

import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        let utils = $.pivotUtilities;
        let heatmap = utils.renderers['Heatmap'];
        let sum = utils.aggregatorTemplates.sum;
        let numberFormat = utils.numberFormat;
        let intFormat = numberFormat({ digitsAfterDecimal: 0 });
        // let moneyFormat = numberFormat({ digitsAfterDecimal: 2 });

        let location = window.location.href;
        if (this.element.dataset.path) {
            location = this.element.dataset.path;
        }

        // let elemId = this.element.id;
        let elemSelector = '#' + this.element.id;
        $.getJSON(location, function (data) {
            $(elemSelector).pivot(data.items, {
                cols: data.cols,
                rows: data.rows,
                aggregator: sum(intFormat)(data.aggregate),
                renderer: heatmap,
            });
        });
    }
}