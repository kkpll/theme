(function($){

    $(document).on('ready',function(){

        $(".upload-button").each(function() {

            var fieldId = $(this).attr("id");

            $(this).after("<div id='upload-container--" + fieldId + "' class='upload-container'></div>");
            $("#upload-container--" + fieldId).html("<input type='button' value='画像' class='upload-container__btn' id='upload-container__btn--" + fieldId + "' />");
            $("#upload-container--" + fieldId).append("<div class='upload-container__images' id='upload-container__images--" + fieldId + "' ></div>");
            $(this).remove();

        });

    });

    var org_media = wp.media.editor.send.attachment;

    $(".upload-container__btn").click(function(){

        var uploadObject = $(this);

        wp.media.editor.send.attachment = function(props, attachment) {
            $(uploadObject).parent().find(".upload-container__images").append(
                "<img class='upload-container__images__image' style='with:75px;height:75px' src='" + attachment.url + "' />");

            $(uploadObject).parent().find(".upload-container__images").append(
                "<input class='upload-container__images__input' type='hidden' name='upload-container__images__input--"
                + $(uploadObject).attr("id") + "[]' value='"
                + attachment.url +"' />");
        }

        wp.media.editor.open();
        return false;
    });

    $(".add_media").click(function(){
        wp.media.editor.send.attachment = org_media;
    });

    $("body").on("drop", function(){
        wp.media.editor.send.attachment = org_media;
    });

    $("body").on("dblclick", ".wpwa_img_prev" , function() {
        $(this).next(".wpwa_img_prev_hidden").remove();
        $(this).remove();
    });


})(jQuery);