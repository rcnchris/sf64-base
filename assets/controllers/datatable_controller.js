import 'datatables.net-bs5/css/dataTables.bootstrap5.min.css';
import 'datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css';
import 'datatables.net-bs5';
import 'datatables.net-responsive-bs5';
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        this.element.classList.add('table', 'table-striped', 'table-hover', 'align-middle');

        const options = {
            retrieve: true,
            responsive: true,
            order: [[0, 'asc']],
            paging: true,
            pagingType: 'full_numbers',
            lengthChange: true,
            lengthMenu: [
                [10, 25, 50, -1],
                ['10', '25', '50', 'Tout'],
            ],
            searching: true,
            stateSave: false,
            ordering: true,
            select: false,
            info: true,
            language: {
                decimal: ',',
                thousands: ' ',
                emptyTable: '<small class="text-danger"><strong>Aucun enregistrement à afficher</strong></small>',
                sInfoEmpty: '',
                sInfo: "<small>Page _PAGE_ sur _PAGES_. Affichage de l'élément _START_ à _END_ sur <strong>_TOTAL_</strong> éléments.</small>",
                sInfoFiltered: "<small><em style='color: #FF0000;'>(filtré de _MAX_ éléments au total)</em></small>",
                sProcessing: 'Traitement en cours...',
                sLoadingRecords: '<div class="spinner-border text-info spinner-border-sm" role="status"><span class="visually-hidden">Traitement en cours...</span></div>',
                lengthMenu: '<span class="me-1">Voir _MENU_</span>',
                search: '',
                sSearchPlaceholder: 'On cherche quoi ?',
                zeroRecords: "<span style='color: #FF0000;'>Rien trouvé !</span>",
            },
        }

        let table = $(this.element).DataTable(options);
    }
}