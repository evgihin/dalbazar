<?php
/* @var $this AdvertController */
/* @var $phone string */
/* @var $code string */
/* @var $attempts int */
?>
Указанный код неверен.
<?php if ($attempts > 0) { ?>
    Вам на телефон отправлено еще одно СМС.
    Осталось попыток: <?= $attempts - 1 ?>
    <button id="confirmCode">Подтвердить</button>
    <script>
        $("#confirmationCode").val("").focus();
    </script>
<?php } else { ?>
    Вы исчерпали лимит отправленных СМС на сегодня. Повторите попытку позже либо укажите другой номер телефона.
<?php } ?>