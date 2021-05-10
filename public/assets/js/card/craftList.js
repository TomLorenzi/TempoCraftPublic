'use strict';

$(function() {
    Craft.initDatatable();
});

Craft.initDatatable = function() {
    //this.$table.DataTable().destroy();
    $('#craftList').DataTable({
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
                data: 'item',
                width: '100px',
                orderable: false,
                render: function (data, type, row) {
                    return `<div class="row"><div class="col">${row.item.name}</div><div class="col"><img src="${row.item.imageUrl}" style="width:50px;"></img></div></div>`;
                }
            },
            {
                data: 'cards',
                width: '300px',
                orderable: false,
                render: function (data, type, row) {
                    let $cardRow = '';
                    row.cards.forEach(card => {
                        $cardRow += `<img src="${card.image}"></img>`;
                    });
                    return $cardRow;
                }
            },
            {
                data: 'creator',
                width: '20px',
                render: function (data, type, row) {
                    return `${row.creator.pseudo} <small><i>#${row.creator.id}</i></small>`;
                }
            },
            {
                data: 'upvote',
                width: '20px',
                render: function (data, type, row) {
                    return `<span style="color:lightgreen;">${row.upvote}</span>`;
                }
            },
            {
                data: 'report',
                width: '20px',
                render: function (data, type, row) {
                    return `<span style="color:red;">${row.report}</span>`;
                }
            },
            {
                data: 'isVerified',
                width: '20px',
                render: function (data, type, row) {
                    if (row.isVerified) {
                        return `<i class="far fa-check-circle fa-2x" style="color:#00BFFF"></i>`;
                    } else {
                        return '';
                    }
                }
            },
            {
                data: 'actions',
                width: '20px',
                render: function (data, type, row) {
                    return `<a href="/craft/id/${row.id}"><button type="button" class="details btn btn-info">Détails</button></a>`;
                }
            }
        ],
        ajax: {
            url: '/ajax/craft',
            method : 'POST',
            cache: false,
            data: function (data) {
                data.search = $('#craftList_filter input').val();
                data.card = Craft.cardId;
            },
            complete: function (data) {
                Craft.list = data.responseJSON.data;          
            }
        }
    });
};

Craft.refreshDatatable = function() {
    this.$table.DataTable().ajax.reload();
};