$(document).ready(function() {

    var wowhead_tooltips = { "colorlinks": true, "iconizelinks": true, "renamelinks": true }

    members.init();
    item_save_prev();
    flyout();

    $(".page-loading").fadeOut(500);


});

function item_save_prev() {
    $(".item-save-prev").each(function() {
        $(this).unbind("click").click(function() {
            var clicked = $(this);
            $.ajax({
                method: "POST",
                url: "ajax.php",
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

function flyout() {
    $(".flyout > .close").each(function() {
        $(this).on("click", function() {
            var target = $(this).parent();
            target.fadeOut(400);
            setTimeout(function() {
                target.removeClass("jelly");
            }, 400);
        });
    });
    $(".show-flyout").each(function() {
        $(this).on("click", function() {
            if($("." + $(this).attr('data-target')).hasClass("jelly")) {
                var target = $("." + $(this).attr('data-target'));
                // jelly out
                target.fadeOut(400);
                setTimeout(function() {
                    target.removeClass("jelly");
                }, 400);
            } else {
                // jelly in
                var target = $("." + $(this).attr('data-target'));
                target.addClass("jelly");
                console.log(target.data("settings"));

                $.ajax({
                    method: "POST",
                    url: "ajax.php",
                    data: { 
                        action: target.data("content"),
                        settings: target.data("settings")
                    }
                }).done(function(data) {
                    target.find("> .content").html(data);
                });
                // load the wanted data for the flyout
            }
            
        });
    })
}

function sendForm(id) {
    $(id).submit();
}