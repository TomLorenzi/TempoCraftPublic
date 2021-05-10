'use strict';

const Craft = {};

$(function() {
    $('#craft_submitBtn').prop('disabled', true);
    $('#craft_cards').select2({
        maximumSelectionLength: 5,
        templateResult: formatData,
        templateSelection: formatData
    });
    $('#craft_item').select2();

    $('#craft_cards, #craft_item').on('change', function() {
        const countItems = $('#craft_cards').select2('data').length;
        if (5 === countItems && '' !== $('#craft_item').val()) {
            $('#craft_submitBtn').prop('disabled', false);
        } else {
            $('#craft_submitBtn').prop('disabled', true);
        }
    });
});

function formatData (data) {
    if (!data.id) { 
        return data.text;
    }
    //Ugly method to get img url and pass it to select2
    let img = data.element.attributes['data-marker-text'].nodeValue;
    var $result = $(
      `<span>${img} ${data.text}</span>`
    );
    return $result;
};
  