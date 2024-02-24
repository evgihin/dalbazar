<?php
/* @var $this AdvertController */
/* @var $images array */
Yii::app()->clientScript->registerScriptFile('/js/plupload/plupload.full.js');
?>
<input type="hidden" name="mainPicture" id="mainPicture">

<div id="imageContainer">
    <a id="pickfiles" class="inactive">Прикрепить картинку</a><?= Helpers::htmlTooltip('Вы можете указать сразу несколько картинок.
      Размер файла <b>не должен</b> превышать 14 мегабайт.
      Высота и ширина не менее 150 пикселей.') ?>
    <div id="filelist">
    </div>
</div>
<input type="submit" value="Добавить объявление" id="addAdvert">

<script>
    var imgCount = 0;
    function addTempImage(id) {
        imgCount++;
        $('#filelist').append(
                '<div id="' + id + '"  class="advImageBlock1">' +
                '<input type="hidden" value="" name="images[]">' +
                '<div class="advImageDelete" style="display:none;" title="удалить картинку"></div>' +
                '<div class="advImageMain" title="эта картинка основная"></div>' +
                '<div class="advImageError"></div>' +
                '<div class="advImageBlock2" title="сделать основной картинкой">' +
                '<img src="/images/theme/load.jpg">' +
                '</div>' +
                '</div>');
    }

    function moveTempToNormal(id, image, value) {
        $('#' + id + ' img').attr('src', image);
        $('#' + id + ' input').val(value);
        $('#' + id + ' .advImageDelete').show(300);
        
        if (imgCount == 1)
            setDefaultImage(value);
    }

    function moveTempToError(id, errText) {
        imgCount++;
        $('#' + id + ' img').attr('src', '/images/theme/errload.jpg');
        $('#' + id + ' .advImageDelete').show(300);
        $('#' + id + ' input').remove();
        $('#' + id).addClass('errored');
        if (errText)
            $('#' + file.id + ' .advImageError').html(errText).show(300);
    }

    function addNormalImage(image, value) {
        var id = Math.floor(Math.random() * 100000);
        addTempImage(id);
        moveTempToNormal(id, image, value);
    }

    function setDefaultImage(value) {
        $('#mainPicture').val(value);
        thisitem = $('input[value="' + value + '"]').siblings('.advImageMain');
        $('.advImageMain').not(thisitem).hide(300);
        thisitem.show(300);
    }

    $("#filelist").on('click', '.advImageDelete', function() {
        if ($(this).data("temp") === "false")
            $.post('<?= $this->createUrl('panel/advert/deleteOldImage') ?>', {image: $(this).siblings('input[type=hidden]').val()}, $.proxy(function(d) {
                if (d.success)
                    $(this).parent().hide(300, function() {
                        $(this).remove();
                    });
            }, this), 'json');
        else {
            if ($(this).parent().hasClass('errored'))
                $(this).parent().hide(300, function() {
                    $(this).remove();
                });
            else
                $.post('<?= $this->createUrl('advert/deleteTempImage') ?>', {image: $(this).siblings('input[type=hidden]').val()}, $.proxy(function(d) {
                    if (d.success)
                        $(this).parent().hide(300, function() {
                            $(this).remove()
                        });
                }, this), 'json');
        }
        imgCount--;
    });

    $("#filelist").on('click', '.advImageBlock1:not(.errored)>.advImageBlock2', function() {
        setDefaultImage($(this).siblings('input[type=hidden]').val());
    });

    $('.advImageMain:first').show(300);

    var uploader = new plupload.Uploader({
        runtimes: 'html5,flash,gears,silverlight,browserplus,html4',
        browse_button: 'pickfiles',
        container: 'imageContainer',
        max_file_size: '14mb',
        url: '<?= $this->createUrl('advert/upload') ?>',
        flash_swf_url: '/js/plupload/plupload.flash.swf',
        silverlight_xap_url: '/js/plupload/plupload.silverlight.xap',
        filters: [
            {title: "Image files", extensions: "jpg,gif,png,jpeg,JPG,JPEG,PNG"}
        ]
                //resize : {width : 1280, height : 1024, quality : 80}
    });

    uploader.init();

    uploader.bind('FilesAdded', function(up, files) {
        $.each(files, function(i, file) {
            if (file.status != plupload.FAILED) {
                addTempImage(file.id);
            }
        });
        up.refresh(); // Reposition Flash/Silverlight
        $('#addAdvert').addClass('disabled');
        uploader.start();
    });

    uploader.bind('UploadComplete', function(up, err) {
        $('#addAdvert').removeClass('disabled');
    });

    uploader.bind('Error', function(up, err) {
        alert('Файл ' + err.file.name + " не принят  по одной из причин:\n-файл не является картинкой \n-файл поврежден \n-файл занят другой программой \n-размер файла превышает 15 мегабайт");
        if (err.file.id) {
            moveTempToError(err.file.id);
        }
        up.refresh(); // Reposition Flash/Silverlight
    });

    uploader.bind('FileUploaded', function(up, file, r) {
        try {
            response = $.parseJSON(r.response)
        }
        catch (er) {
            response = {error: "Внутренняя ошибка сервера."};
            moveTempToError(file.id);
        }

        error = response.error;
        if (error != 'none') {
            moveTempToError(file.id, error + "<br>файл: " + file.name);
        }
        else {
            moveTempToNormal(file.id, response.image, response.name);
        }
    });
    
    <?php
        if ($images)
            foreach ($images as $image) {
                ?>
                    addNormalImage("<?= Helpers::getImageUrl($image, 120, 120) ?>" , "<?= $image ?>" );
            <?php }
        ?>
    
</script>