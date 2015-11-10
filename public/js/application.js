var $input = $('<div class="modal-body"><input type="text" class="form-control" placeholder="Message"></div>');
$(document).on("click", ".js-msgGroup", function () {
    $(".js-msgGroup, .js-newMsg").addClass("hide"), $(".js-conversation").removeClass("hide"), $(".modal-title").html('<a href="#" class="js-gotoMsgs">Back</a>'), $input.insertBefore(".js-modalBody")
}), $(function () {
    function o() {
        return $(window).width() - ($('[data-toggle="popover"]').offset().left + $('[data-toggle="popover"]').outerWidth())
    }

    $(window).on("resize", function () {
        var t = $('[data-toggle="popover"]').data("bs.popover");
        t && (t.options.viewport.padding = o())
    }), $('[data-toggle="popover"]').popover({
        template: '<div class="popover" role="tooltip"><div class="arrow"></div><div class="popover-content p-x-0"></div></div>',
        title: "",
        html: !0,
        trigger: "manual",
        placement: "bottom",
        viewport: {selector: "body", padding: o()},
        content: function () {
            var o = $(".app-navbar .navbar-nav:last-child").clone();
            return '<div class="nav nav-stacked" style="width: 200px">' + o.html() + "</div>"
        }
    }), $('[data-toggle="popover"]').on("click", function (o) {
        o.stopPropagation(), $('[data-toggle="popover"]').data("bs.popover").tip().hasClass("in") ? ($('[data-toggle="popover"]').popover("hide"), $(document).off("click.app.popover")) : ($('[data-toggle="popover"]').popover("show"), setTimeout(function () {
            $(document).one("click.app.popover", function () {
                $('[data-toggle="popover"]').popover("hide")
            })
        }, 1))
    })
}), $(document).on("click", ".js-gotoMsgs", function () {
    $input.remove(), $(".js-conversation").addClass("hide"), $(".js-msgGroup, .js-newMsg").removeClass("hide"), $(".modal-title").html("Messages")
}), $(document).on("click", "[data-action=growl]", function (o) {
    o.preventDefault(), $("#app-growl").append('<div class="alert alert-dark alert-dismissible fade in" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button><p>Click the x on the upper right to dismiss this little thing. Or click growl again to show more growls.</p></div>')
}), $(document).on("focus", '[data-action="grow"]', function () {
    $(window).width() > 1e3 && $(this).animate({width: 300})
}), $(document).on("blur", '[data-action="grow"]', function () {
    if ($(window).width() > 1e3) {
        $(this).animate({width: 180})
    }
}), $(function () {
    function o() {
        $(window).scrollTop() > $(window).height() ? $(".docs-top").fadeIn() : $(".docs-top").fadeOut()
    }

    $(".docs-top").length && (o(), $(window).on("scroll", o))
}), $(function () {
    function o() {
        i.width() > 768 ? e() : t()
    }

    function t() {
        i.off("resize.theme.nav"), i.off("scroll.theme.nav"), n.css({position: "", left: "", top: ""})
    }

    function e() {
        function o() {
            e.containerTop = $(".docs-content").offset().top - 40, e.containerRight = $(".docs-content").offset().left + $(".docs-content").width() + 45, t()
        }

        function t() {
            var o = i.scrollTop(), t = Math.max(o - e.containerTop, 0);
            return t ? void n.css({
                position: "fixed",
                left: e.containerRight,
                top: 40
            }) : ($(n.find("li")[1]).addClass("active"), n.css({position: "", left: "", top: ""}))
        }

        var e = {};
        o(), $(window).on("resize.theme.nav", o).on("scroll.theme.nav", t), $("body").scrollspy({
            target: "#markdown-toc",
            selector: "li > a"
        }), setTimeout(function () {
            $("body").scrollspy("refresh")
        }, 1e3)
    }

    var n = $("#markdown-toc"), i = $(window);
    n[0] && (o(), i.on("resize", o))
});


//script for handling image preview and ajax functionality
//for chops post

$('document').ready(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var files = [];
    $(function () {
        $("#file").change(function () {
            if (typeof(FileReader) !== "undefined") {
                var imagePreview = $("#imagePreview");
                imagePreview.html("");
                var regex = /^([a-zA-Z0-9\s_\\.\-:])+(.jpg|.jpeg|.gif|.png|.bmp)$/;
                $($(this)[0].files).each(function () {
                    var file = $(this);
                    if (regex.test(file[0].name.toLowerCase())) {
                        var reader = new FileReader();
                        reader.onload = function (e) {
                            var img = $("<img />");
                            img.attr("style", "height:150px;width: 150px");
                            img.attr("src", e.target.result);
                            img.addClass("imagePreview");
                            imagePreview.append(img);
                            img.imageCrop({
                                overlayOpacity: 0.25,
                                displayPreview : true,
                                displaySize : true,

                                onSelect : updateForm
                            });
                        };
                        reader.readAsDataURL(file[0]);
                    } else {
                        alert(file[0].name + " is not a valid image file.");
                        imagePreview.html("");
                        return false;
                    }
                });
            }

            for (var i = 0; i < $(this).get(0).files.length; ++i) {
                files.push($(this).get(0).files[i].name);
            }
            $("input[name=file]").val(files);
        });
    });

    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    });

    $('.favourite').on('click', function(e){
        e.preventDefault();

        var chopId = $("input[name='chop_id']", this).val();
        var chop_id = $(this).data('message');
        $.ajax({
            type : 'post',
            url  : 'chops/favourite/'+chopId,
            data : {
                chopId: chopId
            },
            success: function(response){

                alert(chop_id);
                $('.favourites-count-'+chop_id).empty().append(response.count, this);
            },

            error: function(response){
               // console.log(response);
            }
        });

    });

});