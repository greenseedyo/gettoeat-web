(function($) {
    $.fn.modifiable = function(data) {
        var $obj = $(this);
        var type = $obj.data('type');
        var text = $obj.data('text');
        var $text = $('<span class="cell-text"></span>');
        $text.text(text);
        var $pencil = $('<a href="#" class="modify-button" style="display:none"></a>');
        $pencil.append(' <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>');
        $obj.append($text).append($pencil);

        $obj.hover(function() {
            $(this).find('a.modify-button').show();
        }, function() {
            $(this).find('a.modify-button').hide();
        }).delegate('a.modify-button', 'click', function(e) {
            e.preventDefault();
            var $button = $(this);
            var $span = $button.siblings('span.cell-text');
            var oriText = $span.text();
            var oriHtml = $obj.html();
            var $form = $('<form>').on('submit', function(e) {
                e.preventDefault();
                var url = $obj.data('url');
                var data = $(this).serializeArray();
                var new_value = $(this).find(':input[name=value]').val();
                $.post(url, data, function(rtn) {
                    if (rtn.message) {
                        alert(rtn.message);
                    }
                    if (rtn.error) {
                        var $icon = $('<span class="glyphicon glyphicon-remove">').fadeOut('slow');
                        $obj.html(oriHtml).find('span.cell-text').append($icon);
                    } else {
                        var $icon = $('<span class="glyphicon glyphicon-ok">').fadeOut('slow');
                        var text = $obj.data('new-text') || new_value;
                        $obj.html(oriHtml).find('span.cell-text').text(text).append($icon);
                    }
                });
            });
            if ('number' === type) {
                $('<input>').prop({
                    type: 'number',
                    name: 'value',
                    value: oriText,
                    style: "width: " + $obj.width() + "px"
                }).appendTo($form);
            } else if ('select' === type) {
                var $select = $('<select>').prop({
                    name: 'value',
                    style: "width: " + $obj.width() + "px"
                });
                $.each(data, function(key, item) {
                    var selected = false;
                    if (oriText === item) {
                        selected = true;
                    }
                    $('<option>').prop({
                        value: key,
                        selected: selected
                    }).text(item).appendTo($select);
                });
                $select.on('change', function(){
                    var new_text = $(this).find('option:selected').text();
                    $obj.data('new-text', new_text)
                    $form.submit();
                });
                $select.appendTo($form);
            }
            $('<input>').prop({
                type: 'hidden',
                name: 'column_name',
                value: $obj.data('name')
            }).appendTo($form);
            $('<input>').prop({
                type: 'hidden',
                name: 'id',
                value: $obj.data('id')
            }).appendTo($form);
            $obj.html($form);
        });
    };
})(jQuery);
