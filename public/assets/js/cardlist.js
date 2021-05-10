'use strict';

const Cards = {
    isReset: false
};

$(function() {
    Cards.initDatatable();

    $('#selectCat').select2();
    $('#selectType').select2();
    $('#resetBtn').on('click', function() {
        Cards.isReset = true;
        $('#golden').prop('checked', false);
        $('#craftAvailable').prop('checked', false);
        $('#selectCat').val('all');
        $('#selectCat').trigger('change');
        $('#selectType').val('all');
        $('#fromLvl').val('1');
        $('#toLvl').val('200');
        Cards.isReset = false;
        $('#selectType').trigger('change');
    });

    $('#showFilters').on('click', function() {
        $(this).addClass('d-none');
        $('#filters').removeClass('d-none');
    });

    $('#hideFilters').on('click', function() {
        $('#filters').addClass('d-none');
        $('#showFilters').removeClass('d-none');
    });

    $('#selectCat, #selectType, #golden, #craftAvailable, #fromLvl, #toLvl').on('change', function() {
        if (!Cards.isReset) {
            Cards.refreshDatatable();
        } 
    });
});

Cards.initDatatable = function() {
    //$('#cardList').DataTable().destroy();
    $('#cardList').DataTable({
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
                width: '50px',
                render: function (data, type, row) {
                    return '<img src="' + row.image + '">';
                }
            },
            {
                data: 'name',
                width: '100px',
                render: function (data, type, row) {
                    return `<b>${row.name}</b>`;
                }
            },
            {
                data: 'type',
                width: '100px',
            },
            {
                data: 'lvl',
                width: '100px',
            },
            {
                data: 'category',
                width: '100px',
            },
            {
                data: 'actions',
                width: '200px',
                orderable: false,
                render: function (data, type, row) {
                    return `<a href="/card/${row.id}"><button type="button" class="details btn btn-info">Détails</button></a>`;
                }
            }
        ],
        ajax: {
            url: '/ajax/card',
            method : 'POST',
            cache: false,
            data: function (data) {
                data.cat =  $('#selectCat').val();
                data.type = $('#selectType').val();
                data.onlyGold = $('#golden:checkbox:checked').length;
                data.craftAvailable = $('#craftAvailable:checkbox:checked').length;
                data.search = $('#cardList_filter input').val();
                data.fromLvl = $('#fromLvl').val();
                data.toLvl = $('#toLvl').val();
            },
            complete: function (data) {
                Cards.list = data.responseJSON.data;          
            }
        }
    });
};

Cards.refreshDatatable = function() {
    $('#cardList').DataTable().ajax.reload();
};