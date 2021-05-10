'use strict';

const Craft = {};

$(function() {
    $('#cardsList').select2({
        maximumSelectionLength: 5,
        templateResult: formatData,
        templateSelection: formatData
    });

    $('#cardsList').on('change', function() {
        const countItems = $(this).select2('data').length;
        if (5 === countItems) {
            $('#searchBtn').prop('disabled', false);
        } else {
            $('#searchBtn').prop('disabled', true);
        }
    });

    $('#searchBtn').on('click', function() {
        $.ajax({
            url: '/ajax/testCraft',
            method: 'POST',
            data: {
                cardsListId: $('#cardsList').val(),
            },
            success: function(data) {
                if (data.craftExist) {
                    window.location.href = `/craft/id/${data.craftId}`;
                } else {
                    window.alert('Aucun craft trouvé pour cette combinaison');
                }
            },
        });
    })
});



function formatData (data) {
    if (!data.id) { 
        return data.text;
    }
    //Ugly method to get img url and pass it to select2
    let img = data.element.attributes['data-image'].nodeValue;
    var $result = $(
      `<span><img src="${img}"></img> ${data.text}</span>`
    );
    return $result;
};
  