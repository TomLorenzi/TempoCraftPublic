'use strict';

const Items = {};

$(function() {
    Items.initDatatable();
});

Items.initDatatable = function() {
    //this.$table.DataTable().destroy();
    $('#itemList').DataTable({
        stateSave: true,
        dom: "<'row'<'col-sm-8 col-md-4'l><'col-sm-8 col-md-4'p><'col-sm-8 col-md-4'f>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        language: {
            url: '//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/French.json'
        },
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
            [5, 10, 25, 50],
            [5, 10, 25, 50]
        ],
        pageLength: 10,
        order: [[0, 'asc']],
        columns: [
            {
                data: 'id',
                width: '10px'
            },
            {
                data: 'image',
                width: '10px',
                orderable: false,
                render: function (data, type, row) {
                    return `<img src="${row.imageUrl}" style="width:50px;"></img>`
                }
            },
            {
                data: 'name',
                width: '150px',
            },
            {
                data: 'level',
                width: '20px'
            },
            {
                data: 'actions',
                width: '100px',
                orderable: false,
                render: function (data, type, row) {
                    return `<a href="/item/${row.id}"><button type="button" class="details btn btn-info">Détails</button></a>` +
                        `<a href="${row.wikiUrl}" target="_blank"><button type="button" class="dofus btn btn-success ml-2">Page Dofus</button></a>`;
                }
            }
        ],
        ajax: {
            url: '/ajax/item',
            method : 'POST',
            cache: false,
            data: function (data) {
                data.search = $('#itemList_filter input').val();
            },
            complete: function (data) {
                Items.list = data.responseJSON.data;          
            }
        }
    });
};

Items.refreshDatatable = function() {
    this.$table.DataTable().ajax.reload();
};