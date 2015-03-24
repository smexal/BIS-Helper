$(document).ready(function() {

    var wowhead_tooltips = { "colorlinks": true, "iconizelinks": true, "renamelinks": true }

    members.init();
    item_save_prev();

});

function item_save_prev() {
    $(".item-save-prev").each(function() {
        $(this).unbind("click").click(function() {
            var clicked = $(this);
            $.ajax({
                method: "POST",
                url: "/ajax.php",
                data: { action: "addItemToPlayer", item: clicked.prev().attr('data-id'), player: clicked.prev().val() }
            }).done(function(data) {
              clicked.next().text(data);
              setTimeout(function() {
                clicked.next().text("");
              }, 3000)
            });
        });
    });
}