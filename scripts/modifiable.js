(function($) {
    $.fn.modifiable = function(args) {
        var args = args || {};

        var doBind = function ($obj) {
            var type = $obj.data('type');
            var text = $obj.data('text');
            var select_options = args.select_options || {};
            var $text = $('<span class="cell-text"></span>');
            var $pencil = $('<a href="#" class="modify-button"></a>');
            $text.text(text);
            $pencil.append(' <span class="glyphicon glyphicon-pencil"></span>');
            $obj.append($text).append($pencil);

            if (true == $obj.data('toggle')) {
                $pencil.hide();
                $obj.hover(function() {
                    $pencil.show();
                }, function() {
                    $pencil.hide();
                });
            }

            $obj.delegate('a.modify-button', 'click', function(e) {
                e.preventDefault();
                var $button = $(this);
                var $span = $button.siblings('span.cell-text');
                var oriText = $span.text();
                var oriHtml = $obj.html();
                var inputName = $obj.data('name');
                var $form = $('<form>').on('submit', function(e) {
                    e.preventDefault();
                    var url = $obj.data('url');
                    var data = $(this).serializeArray();
                    var new_value = $(this).find(':input[name=' + inputName + ']').val();
                    console.log(new_value);
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
                if ('number' === type || 'text' === type) {
                    $('<input>').prop({
                        type: type,
                        name: inputName,
                        value: oriText,
                        style: "width: " + $obj.width() + "px"
                    }).appendTo($form);
                } else if ('select' === type) {
                    var $select = $('<select>').prop({
                        name: inputName,
                        style: "width: " + $obj.width() + "px"
                    });
                    $.each(select_options, function(key, item) {
                        var selected = false;
                        if (oriText === item.text) {
                            selected = true;
                        }
                        $('<option>').prop({
                            value: item.value,
                            selected: selected
                        }).text(item.text).appendTo($select);
                    });
                    $select.on('change', function(){
                        var new_text = $(this).find('option:selected').text();
                        $obj.data('new-text', new_text)
                        $form.submit();
                    });
                    $select.appendTo($form);
                }
                $.each($obj[0].attributes, function (index, attribute) {
                    var nodeName = attribute.nodeName;
                    var nodeValue = attribute.nodeValue;
                    if (0 === nodeName.indexOf('data-hidden-')) {
                        $('<input>').prop({
                            type: 'hidden',
                            name: nodeName.replace('data-hidden-', ''),
                            value: nodeValue
                        }).appendTo($form);
                    }
                });
                $obj.html($form);
            });
        };

        $(this).each(function (index, ele) {
            doBind($(ele));
        });
    };
})(jQuery);
