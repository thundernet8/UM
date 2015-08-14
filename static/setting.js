jQuery(function($){
    //上传单个图片
    $('.um_upload').on("click",function(e) {

    	var custom_uploader;

        var obj = $(this);
        //console.log(obj);
        e.preventDefault();

        if (custom_uploader) {
            custom_uploader.open();
            return;
        }

        var custom_uploader = wp.media({
            title: '插入选项缩略图',
            button: {
                text: '选择图片'
            },
            multiple: false  // Set this to true to allow multiple files to be selected
        });

        custom_uploader.on('select', function() {
            var attachment = custom_uploader.state().get('selection').first().toJSON();
            //var dataobj = '<img src="'+attachment.url+'"><div class="close">X</div>';
            obj.prev("input").val(attachment.url)
            obj.next("img").attr('src',attachment.url);
            //obj.after(dataobj).hide();
            $('.media-modal-close').trigger('click');
        });

        custom_uploader.open();
    });
    // 添加多个选项
    var item =  '';

    $('body').on('click', 'a.um_mulit_text', function(){
    	var value = $(this).prev().val();
    	var name = $(this).prev().attr("name");
    	var option = '<span><input type="text" name="'+name+'" value="'+value+'" style="width:70%;" /><a href="javascript:;" class="button del_image">删除</a></span>';
    	$(this).parent().before(option);
    	$(this).prev().val('');
		return false;
    });

    //上传多个图片
    var html = '';
    $('body').on('click', '.um_mulit_upload', function(e) {
        var position = $(this).prev("input");
        var key_name = position.attr('name');
        var custom_uploader;
        var obj = $(this);
        var ids = new Array();
        e.preventDefault();
        if (custom_uploader) {
            custom_uploader.open();
            return;
        }
        custom_uploader = wp.media.frames.file_frame = wp.media({
            title: 'Choose Image',
            button: {
                text: 'Choose Image'
            },
            multiple: true
        }).on('select', function() {
            var data = custom_uploader.state().get('selection');
            data.map( function( data ) {
                data = data.toJSON();
                value = data.url;
                // console.log(data);
                html = '<span><input type="text" name="'+key_name+'" value="'+value+'" style="width:70%;"  /><a href="javascript:;" class="button del_image">删除</a></span>';
                position.before(html);
            });
            response = ids.join(",");
            obj.prev().val(response);
            $('.media-modal-close').trigger('click');
        }).open();

        return false;
    });
    //  删除图片
    $('body').on('click', '.del_image', function(){
        $(this).parent().fadeOut(1000, function(){
            $(this).remove();
        });
    });

    return false;
});