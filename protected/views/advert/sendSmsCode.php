<?php
/* @var $this AdvertController */
/* @var $phone string */
?>
На Ваш телефон <span style="font-weight: bold" id="primaryPhone"><?= $phone ?></span>
отправлено СМС с кодом подтверждения. Введите полученный код в поле ниже:<br>
Код подтверждения: <input type="text" size="<?= Yii::app()->params['smsCodeLength'] ?>" id="confirmationCode">
<div id="confirmationField">
    <button id="confirmCode">Подтвердить</button>
</div>
<a class="inactive" href="#" id="reAskSms">Перезапросить код подтверждения</a>
<script>
    //кнопка "повторить отпраку СМС
    $("#reAskSms").click(function() {
        $.get("<?= $this->createUrl('advert/checkPhone') ?>", {'phone':$("#phone1").val()});
        return false;
    });
    
    //кнопка "проверить код"
    $("#confirmationField").on("click", "#confirmCode", function() {
        $("#confirmationField").load('<?= $this->createUrl('advert/checkPhoneCode') ?>', {
            'phone': $("#phone1").val(),
            'code':$("#confirmCode").val()
        });
    });
</script>