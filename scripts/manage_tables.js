var gridPixel = GTE.gridPixel || 10;

function initTableGrid($element) {
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
        })
        .draggable({
            stack: "div",
            grid: [gridPixel, gridPixel],
            stop: function() {
                var $grid = $(this);
                if (true == $grid.data("removed")) {
                    $grid.remove();
                } else if (false == $grid.data("active")) {
                    console.log('a');
                    shiftInactiveGridsPosition();
                    $grid.appendTo("#tables-inactive").css({"top": "10px", "left": "10px"});
                }
                setInactiveDivHeight();
            }
        })
        .resizable({
            grid: gridPixel,
            resize: function() {
                $(this).css("line-height", $(this).height() + "px");
                setInactiveDivHeight();
                console.log($("#tables-inactive").height());
            }
        })
        .each(function(index, element) {
            var $grid = $(element);
            if ($grid.data("active")) {
                setTableGridActive($grid);
            } else {
                setTableGridInactive($grid);
            }
        });


    return $element;
};

function setTableGridActive($table_grid) {
    $table_grid.data("active", true).css("background-color", "#ffeebb");
};

function setTableGridInactive($table_grid) {
    $table_grid.data("active", false).css("background-color", "#ffffff");
};

function setInactiveDivHeight() {
    $("#tables-inactive").height(function() {
        var max_grid_height = 0;
        $(this).find(".table-grid").each(function(index, element) {
            var height = $(element).height();
            max_grid_height = (height > max_grid_height ? height : max_grid_height);
        });
        return max_grid_height + 20;
    });
};

function setActiveDivSize()
{
    $("#tables-active")
        .height(function() {
            return $(this).data("height")
        })
        .width(function() {
            return $(this).data("width")
        });
}

function shiftInactiveGridsPosition() {
    $("#tables-inactive").find(".table-grid").css("left", "+=" + GTE.new_grid_x);
}

initTableGrid($(".table-grid"));
setInactiveDivHeight();
setActiveDivSize();

$("#tables-active")
    .resizable({grid: gridPixel})
    .droppable({
        acceept: ".table-grid",
        over: function(event, ui) {
            setTableGridActive($(ui.draggable));
        },
        drop: function(event, ui) {
            var $grid = $(ui.draggable);
            if (!$grid.parent().is(this)) {
                var gridOffset = $grid.offset();
                var activeOffset = $(this).offset();
                $grid.appendTo(this).css({
                    "top": gridOffset.top - activeOffset.top - 1,
                    "left": gridOffset.left - activeOffset.left - 1
                });
            }
        },
        out: function(event, ui) {
            setTableGridInactive($(ui.draggable));
        }
    });

$("#trash-can").droppable({
    acceept: ".table-grid",
    drop: function(event, ui) {
        $(ui.draggable).data("removed", true);
    }
});

$("#form-add-table").submit(function(e) {
    e.preventDefault();
    var $form = $(this);
    shiftInactiveGridsPosition();
    $new_div = $("<div>")
        .addClass("table-grid btn btn-default btn-lg")
        .data("name", $form.find(":input[name=name]").val())
        .data("width", GTE.new_grid_width + "px")
        .data("height", GTE.new_grid_height + "px")
        .data("line-height", GTE.new_grid_height + "px")
        .data("left", GTE.new_grid_x + "px")
        .data("top", GTE.new_grid_y + "px")
        .data("z-index", ++ GTE.new_grid_z + 100)
        .appendTo("#tables-inactive");
    initTableGrid($new_div);
});

$("#form-save-table").submit(function(e) {
    e.preventDefault();
    var $div_active = $("#tables-active");
    var $form = $(this);
    $form.find("button[type=submit]").button('loading');
    var tables_info = {
        "totalHeight": $div_active.height(),
        "totalWidth": $div_active.width(),
        "tables": []
    };
    $(".table-grid").each(function(index, element) {
        var $element = $(element);
        tables_info.tables.push({
            name: $element.data("name"),
            width: $element.width(),
            height: $element.height(),
            x: $element.css("left"),
            y: $element.css("top"),
            active: $element.data("active")
        });
    });
    $form.find(":input[name=tables_info]").val(JSON.stringify(tables_info));
    var form_data = $form.serializeArray();
    $.post("/manage_tables.php", form_data, function(rtn) {
        $form.find("button[type=submit]").button('reset');
        if (rtn.error) {
            alert(rtn.message);
            $form.find(".btn-danger").show();
        } else {
            $form.find(".btn-success").show();
        }
    }, 'json');
});
