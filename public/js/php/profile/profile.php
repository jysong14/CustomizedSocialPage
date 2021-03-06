<script language="javascript" type="text/javascript">
    /**
     * @author  Seungchul Lee, Jae Yun Song
     * @date    July 24, 2014
     * @last modification   Ausgust 14, 2014
     */
    
    $(window).scroll(function() {
        var lastId = $('.mix').last().attr('id').split("-")[1];
        
        if ($(window).scrollTop() + $(window).height() == $(document).height() && parseInt(lastId) != <?php echo json_encode(Session::get('lastId')); ?>)
        {
            var url = <?php echo json_encode(URL); ?>;
            var post_url = url + 'profile/loadmore/' + lastId + '/' + <?php echo json_encode(Session::get('username'))?>;
            
            $.ajax({
                url: post_url,
                type: 'post',
                data: 'json',
                success: function(jsonData) {
                    var data = JSON.parse(jsonData);
                    var content="";
                    var val = '';
                    data.forEach(function(element, index, array) {
                        if (element.Type == <?php echo json_encode(STATUS)?>) content = '<div class="large-12 columns post-content">' + element.Post + '</div>';
                        else if (element.Type == <?php echo json_encode(IMAGE)?>) content = '<div class="large-12 columns post-content"><img src="' + element.Post + '" alt="picture"></div>';
                        val +=  '<div class="mix" id="post-' + element.id + '"><div class="row">\n\
        <div class="large-2 columns small-3 custom">\n\
            <img class="post-pic" src="' + element.profile_pic_medium + '"/>\n\
        </div>\n\
        <div class="large-10 columns custom">\n\
            <div class="post-nd">\n\
                <a href="' + url + element.Writer + '">\n\
                    <strong>' + element.Writer + '</strong> &nbsp\n\
                </a>\n\
                <i id="tooltip-delete-box-' + element.id + '" class="' + element.Delete + ' right has-tip delete-box" data-tooltip title="delete" onclick="delete_post(\'' + element.Writer + '\',' + element.id + ',\'' + element.Type + '\')"></i>\n\
                <div class="date">'
                    + element.Date + ' &nbsp<i class="' + element.Privacy + '" data-dropdown="drop2-' + element.id + '" data-options="is_hover: true"></i>\n\
                    <div class="f-dropdown content popover-box" id="drop2-' + element.id + '" data-dropdown-content>'
                        + element.Privacy_description +
                    '</div>\n\
                </div>\n\
            </div>\n\
        </div>\n\
        <div class="large-12 columns">\n\
            <div class="row">'
                + content +
            '</div>\n\
            <div class="comment-head">\n\
                <a href="#">comments</a>\n\
                <a href="#"><i class="fi-comment" id="comment-count"> ' + element.Comments.length + '</i></a>\n\
            </div>\n\
            <hr class="comment-hr"/>\n\
            <div class="comment">\n';
                        var comments = '';
                        element.Comments.forEach(function(comment, Cindex, Carray) {
                            comments +=    '\
                <div class="row" id="post-' + comment.CommentId + '">\n\
                    <div class="large-2 columns small-3"><img class="comment-pic" src="' + comment.Profile_pic + '"/></div>\n\
                    <div class="large-10 columns custom comment-content">\n\
                        <i id="tooltip-delete-box-' + comment.CommentId + '" class="' + comment.Delete + ' right has-tip delete-box" data-tooltip title="delete" onclick="delete_post(\'' + comment.Commentor + '\',' + comment.CommentId + ',\'' + <?php echo json_encode(COMMENT); ?> + '\')"></i>\n\
                        <p>\n\
                                <a href="' + <?php echo json_encode(URL); ?> + comment.Commentor +'"><strong>' + comment.Commentor + '</strong></a> &nbsp' + comment.Comment + '\n\
                                <div class="date comment-date">' + comment.Date + '</div>\n\
                        </p>\n\
                    </div>\n\
                </div>\n';
                        });
                        val += comments;
                        val +=          '\
                <div class="row comment-box" id="' + element.id + '">\n\
                    <div class="large-2 columns small">\n\
                        <img class="comment-pic" src="' + element.profile_pic_small + '"/>\n\
                    </div>\n\
                    <form class="large-10 columns custom comment-type-area" id="post-comment-' + element.id + '" method="post">\n\
                        <textarea onkeydown="if (event.keyCode == 13) $(\'#commnet-submit-' + element.id + '\').trigger(\'click\');" id="comment-post" name="comment-post" placeholder="Comment.."></textarea>\n\
                        <input type="hidden" id="contentId" name="contentId" value="' + element.id + '" />\n\
                        <input class="hide" type="submit" id="commnet-submit-' + element.id + '" value="post" onclick=postComment(' + element.id + ') />\n\
                    </form>\n\
                </div>\n\
            </div>\n\
        </div>\n\
    </div></div>\n';
                    });
                    $(val).hide().fadeIn('slow').insertAfter("#" + $('.mix').last().attr('id'));
                }
            })
        }
    });
    
    $('.change-profile-pic, #change-profile-pic-background').hide();

    $('#profile-pic-container').mouseenter(function(){
        $('.change-profile-pic, #change-profile-pic-background').show();
    }).mouseleave(function(){
        $('.change-profile-pic, #change-profile-pic-background').hide();
    });

    var delete_request;
    function delete_post(writer, id, type)
    {
        if (delete_request)
        {
            delete_request.abort();
        }

        request = $.ajax({
            url: <?php echo json_encode(URL . 'profile/delete_ajax/'); ?> + writer + '/' + id + '/' + type,
            type: 'post',
            success: function(html) {
                var data = html;
                if (data == <?php echo json_encode(SUCCESS); ?>) {
                    $("span[data-selector='tooltip-delete-box-" + id + "']").remove();
                    $('#post-' + id).fadeOut(500);
                    if(type == <?php echo json_encode(COMMENT); ?>)
                    {
                        var count = parseInt($('#comment-count').text())
                        count--
                        $('#comment-count').text(' ' + count.toString())
                    }
                }
                else {
                    alert("Sorry, we are having some network error.  Please try again later");
                }
            }
        });
    }

    /**
     * Post submit handler
     */
    var request;

    $('#post-data').submit(function(event) {
        if (request)
        {
            request.abort();
        }

        var $input = $(this).find("input, select, button, textarea, div");
        var serializedData = $(this).serialize();
        $input.prop("disabled", true);

        $('<img id="waiting-wheel" src="<?php echo URL . "public/images/wheel.gif"; ?>" alt="Processing..">').hide().fadeIn('slow').insertAfter("#end-of-postbox");

        request = $.ajax({
            url: <?php echo json_encode(URL . 'profile/post_ajax/' . $this->username . '/' . STATUS); ?>,
            type: 'post',
            data: serializedData,
            success: function(jsonData) {
                $('#waiting-wheel').remove();
                var data = JSON.parse(jsonData);
                var url = <?php echo json_encode(URL); ?>;
                var content="";
                if (data.Type == <?php echo json_encode(STATUS)?>) content = '<div class="large-12 columns post-content">' + data.Post + '</div>'
                else if (data.Type == <?php echo json_encode(IMAGE)?>) content = '<div class="large-12 columns post-content"><img src="' + data.Post + '" alt="picture"></div>'
                $('<div class="mix" id="post-' + data.id + '"><div class="row">\
                        <div class="large-2 columns small-3 custom">\
                            <img class="post-pic" src="' + data.profile_pic_medium + '"/>\
                        </div>\
                        <div class="large-10 columns custom">\
                            <div class="post-nd">\
                                <a href="' + url + data.Writer + '">\
                                    <strong>' + data.Writer + '</strong> &nbsp\
                                </a>\
                                <i id="tooltip-delete-box-' + data.id + '" class="' + data.Delete + ' right has-tip delete-box" data-tooltip title="delete" onclick="delete_post(\'' + data.Writer + '\',' + data.id + ',\'' + data.Type + '\')"></i>\n\
                                <div class="date">'
                                    + data.Date + ' &nbsp<i class="' + data.Privacy + '" data-dropdown="drop2-' + data.id + '" data-options="is_hover: true"></i>\
                                    <div class="f-dropdown content popover-box" id="drop2-' + data.id + '" data-dropdown-content>'
                                        + data.Privacy_description +
                                    '</div>\
                                </div>\
                            </div>\
                        </div>\
                        <div class="large-12 columns">\
                            <div class="row">'
                                + content +
                           '</div>\
                            <div class="comment-head">\
                                <a href="#">comments</a>\
                                <a href="#"><i class="fi-comment" id="comment-count"> 0</i></a>\
                            </div>\
                            <hr class="comment-hr"/>\
                            <div class="comment">\
                                <div class="row comment-box" id="' + data.id + '">\
                                    <div class="large-2 columns small">\
                                    <img class="comment-pic" src="' + data.profile_pic_small + '"/>\
                                </div>\
                                <form class="large-10 columns custom comment-type-area" id="post-comment-' + data.id + '" method="post">\
                                    <textarea onkeydown="if (event.keyCode == 13) $(\'#commnet-submit-' + data.id + '\').trigger(\'click\');" id="comment-post" name="comment-post" placeholder="Comment.."></textarea>\
                                    <input type="hidden" id="contentId" name="contentId" value="' + data.id + '" />\
                                    <input class="hide" type="submit" id="commnet-submit-' + data.id + '" value="post" onclick=postComment(' + data.id + ') />\
                                </form>\
                            </div>\
                        </div>\
                </div>').hide().fadeIn('slow').insertAfter("#end-of-postbox");

                $(document).foundation({
                    Dropdown: {
                        is_hover: true
                    }
                });
            }
        });

        request.always(function() {
            $input.prop("disabled", false);
            $('#post-textarea').val('');
        });

        event.preventDefault();
    });
    
    /*
     * 
     * @param {type} param
     */
    var comment_request;
    
    var postComment = function(contentId) {
        $('#post-comment-' + contentId).submit(function(event) {
        if (comment_request)
        {
            comment_request.abort();
        }

        var $input = $(this).find("input, select, button, textarea, div");
        var serializedData = $(this).serialize();
        $input.prop("disabled", true);

        request = $.ajax({
            url: <?php echo json_encode(URL . 'profile/post_ajax/' . $this->username . '/' . COMMENT); ?>,
            type: 'post',
            data: serializedData,
            success: function(html) {
                var data = JSON.parse(html);
                console.log(data);
                var url = <?php echo json_encode(URL); ?>;
                $('<div class="row" id="post-' + data.CommentId + '">\
                        <div class="large-2 columns small-3"><img src="' + data.Profile_pic + '"/></div>\
                        <div class="large-10 columns custom comment-content">\
                            <i id="tooltip-delete-box-' + data.CommentId + '" class="' + data.Delete + ' right has-tip delete-box" data-tooltip title="delete" onclick="delete_post(\'' + data.Commentor + '\',' + data.CommentId + ',\'' + <?php echo json_encode(COMMENT); ?>+ '\')"></i>\
                            <p>\
                                <a href="' + <?php echo json_encode(URL); ?> + data.Commentor + '"><strong>' + data.Commentor + '</strong></a> &nbsp' + data.Comment + '\
                                <div class="date comment-date">' + data.Date + '</div>\
                            </p>\
                        </div>\
                    </div>').fadeIn('slow').insertBefore($("#" + contentId));
                
                var count = parseInt($('#comment-count').text())
                count++
                $('#comment-count').text(' ' + count.toString())
                                                        
                $(document).foundation({
                    Dropdown: {
                        is_hover: true
                    }
                });
            }
        });

        request.always(function() {
            $input.prop("disabled", false);
            $('#comment-post').val('');
        });

        event.preventDefault();
    });
    }
    

    

    $('.date').hover(function() {
        $(this).stop();
    });

    /**
     * Initial privacy drop-down menu setting
     */
    var privacyTracer = localStorage.getItem("privacyTracer");
    var privacyValueTracer = localStorage.getItem("privacyValueTracer");

    if (privacyTracer == null)
    {
        localStorage.setItem("privacyTracer", 'public-check');
        localStorage.setItem("privacyValueTracer", 'public_only');
        $(document).foundation();
    }

    document.getElementById(privacyTracer.toString()).className = 'fi-check right';
    document.getElementById('privacy-menu-setting').value = privacyValueTracer;

    /**
     * privacy setting drop-down menu button's handler
     */
    $('.privacy-menu').click(function() {
        //select Id
        document.getElementById('privacy-range-dropdown').className = 'custom-tiny radius button';
        document.getElementById('privacy-range').className = 'f-dropdown';
        document.getElementById('privacy-range').style.left = "-99999px";

        switch ($(this).attr('id'))
        {
            case 'privacy-range-public':
                localStorage.setItem("privacyTracer", 'public-check');
                document.getElementById('public-check').className = 'fi-check right';
                document.getElementById('friend-check').className = 'default';
                document.getElementById('personal-check').className = 'default';
                document.getElementById('privacy-menu-setting').value = 0;
                localStorage.setItem("privacyValueTracer", 0);
                break;
            case 'privacy-range-friend':
                localStorage.setItem("privacyTracer", 'friend-check');
                document.getElementById('public-check').className = 'default';
                document.getElementById('friend-check').className = 'fi-check right';
                document.getElementById('personal-check').className = 'default';
                document.getElementById('privacy-menu-setting').value = 1;
                localStorage.setItem("privacyValueTracer", 1);
                break;
            case 'privacy-range-personal':
                localStorage.setItem("privacyTracer", 'personal-check');
                document.getElementById('public-check').className = 'default';
                document.getElementById('friend-check').className = 'default';
                document.getElementById('personal-check').className = 'fi-check right';
                document.getElementById('privacy-menu-setting').value = 2;
                localStorage.setItem("privacyValueTracer", 2);
                break;
        }
    });

    $('.tab-title').click(function() {
        if ($(this).hasClass('active')) {
            var deact_target = $(this).children().attr('href')
            $(this).removeClass('active');
            $(deact_target).removeClass('active');
            return false;
        }
    });
    $('#post-textarea').click(function() {
        $('#post-friends').show();
    });

    // or directly on the modal
    $('a.change-profile-pic').click(function() {
        if($("#crop-container").children().length == 0)
        {
            $("#profile-pic-upload").css("display", "none");
        }
    });

    $('a.change-profile-pic').trigger('click');

    $("#profile-pic-select").click(function(){
        $("input[name='profile-pic-uploading']").click();
    });

    $("input[name='profile-pic-uploading']").on("change", function(evt){
        $("#profile-pic-upload").css("display", "initial");
        var files = evt.target.files[0];
        if(files != null){
            var reader = new FileReader();
            reader.onload = function(files){
                var crop_pic = document.createElement("img");
                crop_pic.setAttribute("id", "crop-pic");
                crop_pic.setAttribute("src", files.target.result);
                var container_width = crop_pic.width;
                var container_height = crop_pic.height;
                var crop_container = document.getElementById("crop-container");
                crop_container.setAttribute("width", container_width);
                crop_container.setAttribute("height", container_height);
                crop_container.appendChild(crop_pic);
                $("#crop-pic").cropper({aspectRatio: 1});
            }
            reader.readAsDataURL(files);
            $("#crop-container").empty();
        }
        else
        {
            $("#crop-container").empty();
            $("#profile-pic-upload").css("display", "none");
        }
    });

    $("#profile-pic-upload").click(function(){
        if($("#crop-pic").length){
            var pic_info = $("#crop-pic").cropper("getData");
            var x_val = pic_info.x1;
            var y_val = pic_info.y1;
            var height = pic_info.height;
            var width = pic_info.width;

            var post_data = new FormData();
            
            post_data.append("file", document.getElementById("profile-pic-uploading").files[0]);
            post_data.append("x_val", x_val);
            post_data.append("y_val", y_val);
            post_data.append("height", height);
            post_data.append("width", width);

            $.ajax({
                type:"POST",
                url: <?php echo json_encode(URL . 'profile/picture_ajax/' . $this->username); ?>,
                data: post_data,
                processData: false,
                contentType: false,
                success: function(data){
                    $("#crop-container").empty();
                    var pic_paths = data.split(',');
                    set_image(pic_paths[0], pic_paths[1], pic_paths[2], pic_paths[3]);
                    $('#myModal').foundation('reveal', 'close');
                },
                error: function(){
                    alert("Failed!");
                }
            });
        }
        else{
            alert("No Image to Upload!");
        }
    });

    function set_image(large, medium, small, xsmall){
        var profile = document.getElementById("profile-pic");
        profile.setAttribute("src", large);

        var post = document.getElementsByClassName("post-pic");
        for(i=0; i<post.length; i++)
        {
            post[i].setAttribute("src", medium);
        }

        var comment = document.getElementsByClassName("comment-pic");
        for(j=0; j<post.length; j++)
        {
            comment[j].setAttribute("src", small);
        }
        
        var profile_header = document.getElementById("header-profile-pic");
        profile_header.setAttribute("src", xsmall);
    }
    
    $("#image-select").click(function(){
        $("input[name='image-uploading']").click();
    });

    $("#image-preview, #image-text-container").hide();

    $("#image-cancel").click(function(){
        $("#image-container").empty();
        $("#image-title").val("");
        $("#image-text").val("");
        $("#image-preview, #image-text-container").hide();
        $("input[name='image-uploading']").val(null);       
    });

    $("input[name='image-uploading']").on("change", function(evt){
        var files = evt.target.files[0];
        if(files != null){
            $("#image-preview, #image-text-container").show();
            var reader = new FileReader();
            reader.onload = function(files){
                var image = document.createElement("img");
                image.setAttribute("id", "preview_image");
                image.setAttribute("src", files.target.result);
                var image_container = document.getElementById("image-container");
                image_container.appendChild(image);
            }
            reader.readAsDataURL(files);
            $("#image-container").empty();
        }
        else
        {
            //$("#image-container").empty();
            //$("#image-preview, #image-text-container").hide();
        }
    });

    $(".image-category").click(function(){
        var text = $(this).html();
        $("#image-category").html(text);
        $("#image-category-list").removeClass("open");
        $("#image-category-list").css("left", "-99999px");

        $(".img-ctrl-check").removeClass("fi-check");
        $(this).find("i").addClass("fi-check");
    });

    $("#image-submit").click(function(){
        var post_data = new FormData();
        
        post_data.append("file", document.getElementById("image-uploading").files[0]);

        $.ajax({
            type:"POST",
            url: <?php echo json_encode(URL . 'profile/upload_image_ajax/' . $this->username); ?>,
            data: post_data,
            processData: false,
            contentType: false,
            success: function(data){
                alert(data);
                $("#image-container").empty();
                $("#image-title").val("");
                $("#image-text").val("");
                $("#image-preview, #image-text-container").hide();
                $("input[name='image-uploading']").val(null); 
                $('#insert-image-modal').foundation('reveal', 'close');
            },
            error: function(){
                alert("Failed!");
            }
        });
    });

</script>