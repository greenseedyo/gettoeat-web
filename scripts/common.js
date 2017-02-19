var GTE = GTE || {};
GTE.common = {};

GTE.common.initTableGrid = function($element) {
    $element
        .text(function() {
            return $(this).data("name");
        })
        .width(function() {
            return $(this).data("width");
        })
        .height(function() {
            return $(this).data("height");
        })
        .css({
            "position": "absolute",
        })
        .css("line-height", function() {
            return $(this).data("line-height");
        })
        .css("left", function() {
            return $(this).data("left");
        })
        .css("top", function() {
            return $(this).data("top");
        })
        .css("z-index", function() {
            return $(this).data("z-index");
        });
};

GTE.common.setMapSize = function($map) {
    $map
        .height(function() {
            return $(this).data("height")
        })
        .width(function() {
            return $(this).data("width")
        });
}
