'use strict';

const Level = {};

$(function() {
    Level.initDatatable();
});

Level.initDatatable = function() {
    //this.$table.DataTable().destroy();
    $('#levelList').DataTable({
        stateSave: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/French.json'
        },
        dom: "<'row'<'col-sm-8 col-md-4'l><'col-sm-8 col-md-4'p><'col-sm-8 col-md-4'f>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        processing: true,
        serverSide: true,
        responsive: true,
        bFilter: true,
        aoColumnDefs: [{
            'bSortable': false,
            'aTargets': ['nosort']
        },
            {
                'bSearchable': false,
                'aTargets': ['nosort']
            }
        ],
        lengthMenu: [
            [5, 10, 25, 50, 100, 200],
            [5, 10, 25, 50, 100, 200]
        ],
        pageLength: 10,
        order: [[0, 'asc']],
        columns: [
            {
                data: 'dofusLevel',
                width: '10px'
            },
            {
                data: 'cards',
                width: '200px',
                orderable: false,
                render: function (data, type, row) {
                    let $cardRow = '<div class="row">';
                    row.cards.forEach(card => {
                        $cardRow += `<div class="col"><img style="margin: auto; display:block;" data-name="${card.name}" src="${card.image}"></img><div class="text-center">${card.name}</div></div>`;
                    });
                    $cardRow += '</div>';
                    return $cardRow;
                }
            },
            {
                data: 'actions',
                width: '200px',
                orderable: false,
                render: function (data, type, row) {
                    return `<a href="/level/id/${row.dofusLevel}"><button type="button" class="details btn btn-info">Détails</button></a>`;
                }
            }
        ],
        ajax: {
            url: '/ajax/level',
            method : 'POST',
            cache: false,
            data: function (data) {
                data.search = $('#levelList_filter input').val();
            },
            complete: function (data) {
                Level.list = data.responseJSON.data;          
            }
        }
    });
};

Level.refreshDatatable = function() {
    this.$table.DataTable().ajax.reload();
};