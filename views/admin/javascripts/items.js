(function($) {
  $(document).ready(function() {
    $('div[id^="element-"] label').addClass('original-label');
    $('input[name="submit"]').on('click', function() {
      $('input[name^="Elements["][name$="][0][html]"]:checked').each(function() {
        var _eid = jQuery(this).attr('name').split(']')[0].split('[')[1];
        var _text = $('#Elements-'+_eid+'-0-text_ifr').contents().find('#tinymce').text();
        if (_text.length == 0) {
          alert('You need to enter a value for "'+$('#element-' + _eid + ' label').first().text().trim()+'".');
        }
        else {
          $('#Elements-'+_eid+'-0-text').html(_text);
        }
      });
    });
    $('#multicollections-form input[type="checkbox"], #collection-id').on('change', function() {
      var collection_id = $('#multicollections-form input:checked').val();
      if (typeof(collection_id) == 'undefined') {
        collection_id = $('#collection-id').val();
        if (collection_id == '' || typeof(collection_id) == 'undefined') {
          collection_id = 0;
        }
      }
      $('div[id^="element-"]').show();
      $('div[id^="element-"] .alpha label.override').remove();
      $('div[id^="element-"] .alpha label .required').remove();
      $('div[id^="element-"] [required="required"]').removeAttr('required');
      $('div[id^="element-"] input[name^="add_element"]').show();
      $.each(Omeka.element_administration, function(element_id, value) {
        var dom_id = '#element-' + element_id;
        var other_collection_element = null;
        $.each(Omeka.collections, function(index, elements) {
          if (other_collection_element === false) {
            return;
          }
          else if (index == collection_id) {
            if ($.inArray(dom_id, elements) !== -1) {
              other_collection_element = false;
            }
          }
          else if ($.inArray(dom_id, elements) !== -1) {
            other_collection_element = true;
          }
        });

        if (value[collection_id]['hidden_form'] == 1) {
          $(dom_id).hide();
        }

        if (value[collection_id]['multiple'] !== 1 && !other_collection_element) {
          $(dom_id + ' input[name^="add_element"]').hide();
        }

        if (value[collection_id]['html'] === 0 && !other_collection_element) {
          $('input[name="Elements['+element_id+'][0][html]"]').hide();
        }
        else {
          $('input[name="Elements['+element_id+'][0][html]"]').show();
        }

        if (value[collection_id]['form_label'] !== '' && !other_collection_element) {
          var label = value[collection_id]['form_label'];
          $(dom_id + ' .alpha label.original-label').hide();
          $(dom_id + ' .alpha label').parent().prepend('<label class="override">'+label+'</label>');
        }
        else {
          $(dom_id + ' .alpha label.original-label').show();
        }

        if (value[collection_id]['required'] === 1 && value[collection_id]['hidden_form'] !== 1 && !other_collection_element) {
          $(dom_id + ' textarea,' + dom_id + ' select').attr('required', 'required');
          $(dom_id + ' .alpha label').first().append( ' <span class="required"></span>');
        }

        if ($('#Elements-'+element_id+'-0-text').val() == "") {
          if (value[collection_id]['default_value'] !== '' && !other_collection_element) {
            $('#Elements-'+element_id+'-0-text').val(value[collection_id]['default_value']);
            $('#Elements-'+element_id+'-0-text option[value="' + value[collection_id]['default_value'] + '"]').prop('selected', true);
          }
          else {
            $('#Elements-'+element_id+'-0-text option').first().prop('selected', true);
          }
        }

      });
    });
    setTimeout(function() {
      $('#multicollections-form input[type="checkbox"]').last().change()
      $('#collection-id').change();
    }, 1000);
  });
})(jQuery);
