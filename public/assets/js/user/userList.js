'use strict';

const User = {};

$(function() {
    User.initDatatable();
});

User.initDatatable = function() {
    $('#userList').DataTable({
        stateSave: true,
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
            [25],
            [25]
        ],
        pageLength: 25,
        order: [[1, 'asc']],
        columns: [
            {
                data: 'pseudo',
                width: '50px',
                orderable: false,
            },
            {
                data: 'nbVerified',
                width: '50px',
                orderable: false,
                render: function (data, type, row) {
                    return `<span style="color:lightblue;">${row.nbVerified}</span>`;
                }
            },
            {
                data: 'nbVotes',
                width: '50px',
                orderable: false,
                render: function (data, type, row) {
                    return `<span style="color:lightgreen;">${row.nbVotes}</span>`;
                }
            },
            {
                data: 'nbReports',
                width: '50px',
                orderable: false,
                render: function (data, type, row) {
                    return `<span style="color:red;">${row.nbReports}</span>`;
                }
            },
        ],
        ajax: {
            url: '/ajax/user',
            method : 'POST',
            cache: false,
            data: function (data) {
                data.search = $('#userList_filter input').val();
            },
            complete: function (data) {
                User.list = data.responseJSON.data;          
            }
        }
    });
};

User.refreshDatatable = function() {
    this.$table.DataTable().ajax.reload();
};