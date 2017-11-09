(function($) {
    $.entwine('ss', function($) {
        $('.ss-link-item-input button.edit').entwine({
            onclick: function() {
                var parent = $(this).closest('.ss-link-item-input');

                var id = parent.find('input.link-item-hidden').val();
                var name = parent.find('input.link-item-hidden').attr('name');
                var path = parent.closest('form').attr('action');

                jQuery.ajax({
                    type: "POST",
                    url: '/' + path + 'field/' + name + '/LinkItemFormHTML?format=json',
                    data: { 
                        LinkID: id,
                        Name: name
                    },
                    success: function(data){
                        $('body').append(data);
                    }
                 });
            }
        });
        $('.ss-link-item-input button.remove').entwine({
            onclick: function() {
                var parent = $(this).closest('.ss-link-item-input');
                parent.find('input.link-item-hidden').val(0);
                parent.find('a').attr('href', '').text('');
                parent.find('button.edit').text('Add Link');
                $(this).hide();
            }
        });
        $('.ss-link-item-modal .link-item-switcher').entwine({
            onmatch: function() {
                this.switch();
            },
            onchange: function() {
                this.switch();
            },
            switch: function() {
                var active = $('.link-item-switcher option:selected').val();
                var parent = $(this).closest('form');
                parent.find('div.link-hidden').css('display','none');
                parent.find('div.link-' + active).css('display','block');
            }
        });
        $('.ss-link-item-modal button.close').entwine({
            onclick: function() {
                $(this).closest('.ss-link-item-modal').remove();
            }
        });
        $('.ss-link-item-modal form.link-item-form').entwine({
            onsubmit: function() {
                var path = $(this).attr('action');
                var form = $(this).serializeArray();
                var button = $(this).find('input.action');
                form.push({ 
                    name: button.attr('name'), 
                    value: button.attr('value')
                });
                jQuery.ajax({
                    type: "post",
                    url: '/' + path + '?format=json',
                    data: form,
                    dataType: 'json',
                    success: function(data) {
                        if(data.hasOwnProperty('success')) {
                            $('.ss-link-item-modal').remove();
                            var input = $('input[name="' + data.name + '"]');
                            var parent = input.closest('div');
                            input.val(data.id);
                            parent.find('button.edit').text('Update Link');
                            parent.find('button.remove').show();
                            parent.find('a').attr('href', data.url).text(data.url);
                        } else {
                            $('.ss-link-item-modal').replaceWith(data);
                        }
                    }
                });
                return false;
            }
        });
    });
})(jQuery);