<?php
/* @var $this AdvertController */
/* @var $categoriesLevel1 array */
/* @var $categoriesLevel2 array */
/* @var $advert array */
/* @var $imagesOld array */
/* @var $imagesTemp array */
/* @var $cities array */


Yii::app()->clientScript->registerScriptFile('/js/jquery-ui-1.9.1.custom.min.js');
Yii::app()->clientScript->registerCssFile('/css/smoothness/jquery-ui-1.9.1.custom.min.css');
Yii::app()->clientScript->registerScriptFile('/js/plupload/plupload.full.js');
Yii::app()->clientScript->registerScriptFile('/js/jquery.maskedinput-1.3.min.js');
Yii::app()->clientScript->registerScriptFile('/js/jquery.validate.min.js');
?>
<?php if ($this->_errors) echo CHtml::errorSummary($this->_errors) ?>
<form method="post" action="<?= $this->createUrl('panel/advert/update',array('advert_id'=>$id)) ?>" id="advertForm">
  <input type="hidden" name="update[mainPicture]" id="mainPicture">
  <div class="advAddField">
    <label for="zagoovok">Заголовок:</label>
    <input type="text" name="update[zagolovok]" id="zagolovok" size="45" value="<?= $advert['zagolovok'] ?>"><?= Helpers::htmlTooltip('Кратко о Вашем объявлении.<br>
      Плохой практикой являются заголовки типа: <br>
      <b>Продам</b> или  <b>Продам авто</b><br>
      для объявления, опубликованного в категории <i>авто</i>. <br>
      Максимум - 130 символов.') ?>
  </div>
  <div class="advAddField">
    <label for="price">Цена:</label>
    <input type="text" name="update[price]" id="price" size="8" value="<?= ($advert['price'] != -1) ? $advert['price'] : "" ?>">р.<?= Helpers::htmlTooltip('Цена указывается в рублях без точек, запятых и копеек. Максимум - 99999999р.') ?>
  </div>
  <div class="advAddField">
    <label for="text">Текст:</label>
    <textarea name="update[text]" id="text" cols="50" rows="7" value="<?= $advert['text'] ?>"></textarea>
  </div>
  <div class="advAddField">
    <label for="category">Категория:</label>
    <select name="update[category]" id="category" autocomplete="off">
      <option value="0" selected="" >Выберите категорию</option>
      <?php foreach ($categoriesLevel1 as $category) { ?>
        <optgroup label="<?= $category['name'] ?>">
          <?php
          if (isset($categoriesLevel2[$category['category_id']]))
            foreach ($categoriesLevel2[$category['category_id']] as $subCategory) {
              ?>
              <option value="<?= $subCategory['category_id'] ?>" <?php if ($advert['category_id'] == $subCategory['category_id']) echo "selected"; ?>>---<?= $subCategory['name'] ?></option>
            <?php }
          ?>
        </optgroup>
        <?php
      }
      ?>
    </select>
  </div>
  <div id="filters">
  </div>

  <div class="advAddField">
    <label for="phone1">Телефон 1:</label>
    <input type="text" name="update[phone1]" id="phone1" size="17" value="<?= $advert['phone1'] ?>"><?= Helpers::htmlTooltip("Ваш основной телефон в международном формате, 10 цифр.<br>пример: <b>9141234567</b>") ?>
  </div>

  <div class="advAddField">
    <label for="phone2">Телефон 2:</label>
    <input type="text" name="update[phone2]" id="phone2" size="17" value="<?= $advert['phone2'] ?>"><?= Helpers::htmlTooltip("Ваш дополнительный телефон. Необязателен для указания") ?>
  </div>

  <div class="advAddField">
    <label for="email">E-mail:</label>
    <input type="text" name="update[email]" id="email" value="<?= $advert['email'] ?>"><?= Helpers::htmlTooltip("Адрес почтового ящика будет виден для посетителей") ?>
  </div>

  <div class="advAddField">
    <label for="city">Город:</label>
    <select name="update[city]" id="city" autocomplete="off">
      <option value="0" selected="" >Выберите город</option>
      <?php foreach ($cities as $city) { ?>
        <option value="<?= $city['city_id'] ?>" <?php if ($advert['city_id'] == $city['city_id']) echo 'selected'; ?> ><?= $city['name'] ?></option>
        <?php
      }
      ?>
    </select>
  </div>

  <script>
    $.validator.addMethod('catсheck',function(val){if (val==0) return false; else return true;},"Выберите категорию");
    $.validator.addMethod('cityсheck',function(val){if (val==0) return false; else return true;},"Выберите город");
    $('#advertForm').validate({
      errorPlacement: function(er, el) {
        er.appendTo( el.parent() );
      },
      errorElement: "div",
      errorClass: "invalid",
      validClass: "valid",
      rules:{
        "update[phone1]": "required",
        "update[zagolovok]": {
          required:true,
          maxlength:130
        },
        "update[email]": "email",
        "update[price]": {
          digits:true,
          range : [0,99999999]
        },
        "update[category]": "catсheck",
        "update[city]": "cityсheck",
        "update[text]": {maxlength:3000}
      }
    });

    $('#phone1, #phone2').mask('+7 (999) 999-9999');

    var filterChache={};
    function updateFilters(){
      if (typeof(filterChache[$('#category').val()])!="undefined")
        $('#filters').html(filterChache[$('#category').val()]);
      else
        $.post('<?= $this->createUrl('filter/getByCategory', array('advert_id' => $id)) ?>', {categoryId:$('#category').val(),fieldName:'update'}, function(data){
          $('#filters').html(data);
          filterChache[$('#category').val()]=data;
        });
    }
    $('#category').on('change',updateFilters);
    updateFilters();
  </script>


  <div id="imageContainer">
    <button id="pickfiles">Прикрепить картинку</button><?= Helpers::htmlTooltip('Вы можете указать сразу несколько картинок.
      Размер файла <b>не должен</b> превышать 14 мегабайт.
      Высота и ширина не менее 150 пикселей.') ?>
    <div id="filelist">
      <?php
      if ($imagesOld)
        foreach ($imagesOld as $image) {
          ?>
          <div class="advImageBlock1">
            <input type="hidden" value="<?= $image ?>" name="update[imagesOld][]">
            <div class="advImageDelete" data-temp="false" title="удалить картинку"></div>
            <div class="advImageMain" title="эта картинка основная"></div>
            <div class="advImageBlock2" title="сделать основной картинкой">
              <img src="<?= Helpers::getImageUrl($image, 120, 120) ?>">
            </div>
          </div>
          <?php
        }

      if ($imagesTemp)
        foreach ($imagesTemp as $image) {
          ?>
          <div class="advImageBlock1">
            <input type="hidden" value="<?= $image ?>" name="update[images][]">
            <div class="advImageDelete" title="удалить картинку"></div>
            <div class="advImageMain" title="эта картинка основная"></div>
            <div class="advImageBlock2" title="сделать основной картинкой">
              <img src="<?= Helpers::getImageUrl($image, 120, 120) ?>">
            </div>
          </div>
        <?php }
      ?>
    </div>
  </div>
  <input type="submit" value="Обновить объявление" id="updateAdvert">
  <script>

    $("#filelist").on('click','.advImageDelete',function(){
      if ($(this).data("temp")===false) {
        var imageName = $(this).siblings('input[type=hidden]').val();
        $('<input name="update[deleteImage][]" type="hidden">').val(imageName).appendTo('#advertForm');
        $(this).parent().hide(300,function(){$(this).remove()});
      }
      else {
        if ($(this).parent().hasClass('errored'))
          $(this).parent().hide(300,function(){$(this).remove()});
        else
          $.post('<?= $this->createUrl('advert/deleteTempImage') ?>',{image:$(this).siblings('input[type=hidden]').val()},$.proxy(function(d){
            if (d.success)
              $(this).parent().hide(300,function(){$(this).remove()});
          },this),'json');
      }
    });

    $("#filelist").on('click','.advImageBlock1:not(.errored)>.advImageBlock2',function(){
      $('#mainPicture').val($(this).siblings('input[type=hidden]').val());
      thisitem = $(this).siblings('.advImageMain');
      $('.advImageMain').not(thisitem).hide(300);
      thisitem.show(300);
    });

    $('.advImageMain:first').show(300);
    $('#mainPicture').val($('.advImageBlock1:first input[type=hidden]').val());

    var uploader = new plupload.Uploader({
      runtimes : 'html5,flash,gears,silverlight,browserplus,html4',
      browse_button : 'pickfiles',
      container : 'imageContainer',
      max_file_size : '14mb',
      url : '<?= $this->createUrl('advert/upload') ?>',
      flash_swf_url : '/js/plupload/plupload.flash.swf',
      silverlight_xap_url : '/js/plupload/plupload.silverlight.xap',
      filters : [
        {title : "Image files", extensions : "jpg,gif,png,jpeg,JPG,JPEG,PNG"}
      ]
      //resize : {width : 1280, height : 1024, quality : 80}
    });

    uploader.init();

    uploader.bind('FilesAdded', function(up, files) {
      $.each(files, function(i, file) {
        if (file.status != plupload.FAILED){
          $('#filelist').append(
          '<div id="' + file.id + '"  class="advImageBlock1">' +
            '<input type="hidden" value="" name="update[images][]">'+
            '<div class="advImageDelete" style="display:none;" title="удалить картинку"></div>'+
            '<div class="advImageMain" title="эта картинка основная"></div>'+
            '<div class="advImageError"></div>'+
            '<div class="advImageBlock2" title="сделать основной картинкой">' +
            '<img src="/images/theme/load.jpg">'+
            '</div>' +
            '</div>');
        }
      });
      up.refresh(); // Reposition Flash/Silverlight
      $('#updateAdvert').addClass('disabled');
      uploader.start();
    });

    uploader.bind('UploadComplete',function(up,err){$('#updateAdvert').removeClass('disabled');});

    /*uploader.bind('UploadProgress', function(up, file) {
      $('#' + file.id + " b").html(file.percent + "%");
    });*/

    uploader.bind('Error', function(up, err) {
      alert('Файл '+ err.file.name + " не принят  по одной из причин:\n-файл не является картинкой \n-файл поврежден \n-файл занят другой программой \n-размер файла превышает 15 мегабайт");
      if (err.file.id){
        $('#' + err.file.id +' img').attr('src','/images/theme/errload.jpg');
        $('#' + err.file.id +' .advImageDelete').show(300);
      }
      up.refresh(); // Reposition Flash/Silverlight
    });

    uploader.bind('FileUploaded', function(up, file, r) {
      $('#' + file.id+' .advImageDelete').show(300);

      try { response = $.parseJSON(r.response) }
      catch (er) { response = {error:"Внутренняя ошибка сервера."}; }

      error = response.error;
      if (error!='none') {
        $('#' + file.id+' img').attr('src','/images/theme/errload.jpg');
        $('#' + file.id+' input').remove();
        $('#' + file.id).addClass('errored');
        $('#' + file.id+' .advImageError').html(error+"<br>файл: "+file.name).show(300);
      }
      else {
        $('#' + file.id+' img').attr('src',response.image);
        $('#' + file.id+' input').val(response.name);
      }
    });

    $('#advertForm').submit(function(){
      if (uploader.state!=plupload.STOPPED)
        return false;
    });
  </script>
</form>