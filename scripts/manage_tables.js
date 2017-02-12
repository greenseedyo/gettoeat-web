$.fn.initTableGrid = function(options) {
    var options = options || {};
    var gridPixel = options.gridPixel || 10;
    var $element = $(this);
    $element
        .css({
            "position": "absolute"
        })
        .draggable({
            stack: "div",
            grid: [gridPixel, gridPixel],
            stop: function() {
                var $grid = $(this);
                if (false == $grid.data("active")) {
                    $grid.appendTo("#tables-inactive").css({"top": "10px", "left": "10px"});
                }
            }
        })
        .resizable({
            grid: gridPixel,
            resize: function() {
                $(this).css("line-height", $(this).height() + "px");
            }
        });

    return $element;
};
