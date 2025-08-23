import '../vendor/daterangepicker/daterangepicker.css';
import '../vendor/daterangepicker/daterangepicker.js';
import moment from '../vendor/moment/moment.index.js';
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {

        let dataset = this.element.dataset;
        moment.locale(dataset.locale);

        let start = null;
        let withStartIn = dataset.start != '';
        if (withStartIn) {
            start = moment(dataset.start, dataset.format);
        } else {
            start = moment().startOf('day');
        }

        let end = null;
        let withEndIn = dataset.end != '';
        if (withEndIn) {
            end = moment(dataset.end, dataset.format);
        } else {
            end = moment().endOf('day');
        }

        let withRange = dataset.range != '';
        let allRanges = {
            yesterday: [moment().subtract(1, 'days').startOf('day'), moment().subtract(1, 'days').endOf('day')],
            today: [moment().startOf('day'), moment().endOf('day')],
            tomorow: [moment().add(1, 'days').startOf('day'), moment().add(1, 'days').endOf('day')],

            lastWeek: [moment().subtract(1, 'weeks').startOf('week'), moment().subtract(1, 'weeks').endOf('week')],
            thisWeek: [moment().startOf('week').startOf('day'), moment().endOf('week').endOf('day')],
            nextWeek: [moment().add(1, 'weeks').startOf('week'), moment().add(1, 'weeks').endOf('week')],

            lastMonth: [moment().subtract(1, 'months').startOf('month'), moment().subtract(1, 'months').endOf('month')],
            thisMonth: [moment().startOf('month').startOf('day'), moment().endOf('month').endOf('day')],
            nextMonth: [moment().add(1, 'months').startOf('month'), moment().add(1, 'months').endOf('month')],

            lastYear: [moment().subtract(1, 'years').startOf('year'), moment().subtract(1, 'years').endOf('year')],
            thisYear: [moment().startOf('year').startOf('day'), moment().endOf('year').endOf('day')],
            nextYear: [moment().add(1, 'years').startOf('year'), moment().add(1, 'years').endOf('year')],

            lastSevenDays: [moment().subtract(6, 'days').startOf('day'), moment()],
            lastThirtyDays: [moment().subtract(29, 'days').startOf('day'), moment()],

            threeMinutes: [moment(), moment().add(3, 'minutes')],
            fiveMinutes: [moment(), moment().add(5, 'minutes')],
            tenMinutes: [moment(), moment().add(10, 'minutes')],

            oneYear: [moment(), moment().add(1, 'years')],
            endYear: [moment(), moment().endOf('year')],
        };
        let predefinedRanges = {
            default: {
                'Aujourd\'hui': allRanges.today,
                'Hier': allRanges.yesterday,
                'Demain': allRanges.tomorow,
                'Cette semaine': allRanges.thisWeek,
                'Ce mois': allRanges.thisMonth,
            },
            single: {
                'Aujourd\'hui': allRanges.today,
                'Hier': allRanges.yesterday,
                'Demain': allRanges.tomorow,
            },
            short: {
                '3 minutes': allRanges.threeMinutes,
                '5 minutes': allRanges.fiveMinutes,
                '10 minutes': allRanges.tenMinutes,
            },
            search: {
                'Hier': allRanges.yesterday,
                'Aujourd\'hui': allRanges.today,
                'Demain': allRanges.tomorow,
                'Semaine dernière': allRanges.lastWeek,
                'Cette semaine': allRanges.thisWeek,
                'Semaine prochaine': allRanges.nextWeek,
                'Mois dernier': allRanges.lastMonth,
                'Ce mois': allRanges.thisMonth,
                'Mois prochain': allRanges.nextMonth,
                'L\'année dernière': allRanges.lastYear,
                'Cette année': allRanges.thisYear,
                'L\'année prochaine': allRanges.nextYear,
            },
            search_past: {
                'Hier': allRanges.yesterday,
                'Semaine dernière': allRanges.lastWeek,
                'Mois dernier': allRanges.lastMonth,
                'L\'année dernière': allRanges.lastYear,
                'Aujourd\'hui': allRanges.today,
                'Cette semaine': allRanges.thisWeek,
                'Ce mois': allRanges.thisMonth,
                'Cette année': allRanges.thisYear,
            },
            search_future: {
                'Demain': allRanges.tomorow,
                'Semaine prochaine': allRanges.nextWeek,
                'Mois prochain': allRanges.nextMonth,
                'L\'année prochaine': allRanges.nextYear,
            },
            activity: {
                'Aujourd\'hui': allRanges.today,
                'Hier': allRanges.yesterday,
                'Demain': allRanges.tomorow,
            },
            parc: {
                'Cette année': allRanges.thisYear,
                'L\'année prochaine': allRanges.nextYear,
                '1 an': allRanges.oneYear,
                'Fin année': allRanges.endYear,
            },
        };

        let options = {
            startDate: start,
            endDate: end,
            singleDatePicker: dataset.single != null,
            autoUpdateInput: withStartIn == true,
            showDropdowns: true,
            showISOWeekNumbers: true,
            timePicker: dataset.time != null,
            timePicker24Hour: true,
            locale: {
                format: dataset.time != null ? 'DD/MM/YYYY HH:mm' : 'DD/MM/YYYY',
                applyLabel: 'Valider',
                cancelLabel: 'Effacer',
                customRangeLabel: 'Voir calendrier',
                weekLabel: 'S',
                daysOfWeek: moment.weekdaysShort(),
                monthNames: moment.months(),
            },
            ranges: false
        };

        if (withRange) {
            let ranges = {};
            if (dataset.range === 'data-range') {
                ranges = predefinedRanges.default;
            } else if (dataset.range === 'single') {
                ranges = predefinedRanges.single;
            } else if (dataset.range === 'short') {
                ranges = predefinedRanges.short;
            } else if (dataset.range === 'search') {
                ranges = predefinedRanges.search;
            } else if (dataset.range === 'search_past') {
                ranges = predefinedRanges.search_past;
            } else if (dataset.range === 'search_future') {
                ranges = predefinedRanges.search_future;
            } else if (dataset.range === 'activity') {
                ranges = predefinedRanges.activity;
            } else if (dataset.range === 'parc') {
                ranges = predefinedRanges.parc;
            }
            options = Object.assign(options, { ranges: ranges });
        }

        let formatLabel = 'DD/MM/Y';
        if (options.singleDatePicker === false) {
            formatLabel = formatLabel + ' HH:mm';
        }

        // console.log(this.element.dataset, options, start.fromNow());

        $(this.element).daterangepicker(options);

        $(this.element).on('apply.daterangepicker', function (ev, picker) {
            let label = picker.startDate.format(formatLabel);
            if (picker.singleDatePicker === false) {
                label = label + ' - ' + picker.endDate.format(formatLabel);
            }
            $(this).val(label);
        });

        $(this.element).on('cancel.daterangepicker', function (ev, picker) {
            $(this).val('');
        });
    }
}