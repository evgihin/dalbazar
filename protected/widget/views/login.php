<?php
/* @var $this CWidget */
/* @var $model LoginForm */
/* @var $form CActiveForm  */
?>
<div id="userpanel" class="hidden">
    <div class="loginform">
        <form id="login-form" action="<?= $this->controller->createUrl('login/index') ?>" method="post">
            <div class="row">
                <label for="login1">Логин/телефон/e-mail:</label>
                <input type="text" id="login1" name="LoginForm[username]">
            </div>
            <div id="loginError"></div>

            <div class="row">
                <label for="pass1">Пароль:</label>
                <input type="password" id="pass1" name="LoginForm[password]">
            </div>
            <div id="passError"></div>

            <div class="row buttons">
                <input type="submit" value="войти">
                <a href="#" class="dynamic" id="getPwd">получить пароль</a>
            </div>

        </form>
    </div><!-- form -->
    <div id="UPerror"></div>
    <script>
        function getType(str) {
            if (<?= Email::regExp(true); ?>.test(str))
                return "email";
            if (<?= Phone::regExp(true) ?>.test(str))
                return "phone";
            if (<?= User::loginRegExp(true) ?>.test(str))
                return "login";
            return false;
        }

        var eventAdded = false;
        function check() {
            if (!eventAdded) {
                $("#login1").on("keyup", check);
                eventAdded = true;
            }
            ttype = getType($('#login1').val())
            if (!ttype) {
                $("#loginError").text("Поле не распознано");
            } else
                $("#loginError").text("");
            return ttype;
        }

        function tryIn(addr) {
            //проверяем пароль
            if (!/^.{4,31}$/.test($("#pass1").val())) {
                $("#passError").text("Пароль не указан либо указан неверно");
                return false;
            } else {
                $("#passError").text("");
            }

            $.post(addr, {
                "login": $("#login1").val(),
                "password": $("#pass1").val()
            }, function(d) {
                if (!(d = $.parseJSON(d))) {
                    $('#UPerror').html("Ошибка сервера. Повторите позже.");
                } else
                if (d.error) {
                    $('#UPerror').html(d.error);
                } else {
                    $('#UPerror').html("");
                    document.location.href = d.redirect;
                }
            });
        }

        function getPwd(addr) {
            $.post(addr, {
                "login": $("#login1").val()
            }, function(d) {
                if (!(d = $.parseJSON(d))) {
                    $('#UPerror').html("Ошибка сервера. Повторите позже.");
                } else
                if (d.error) {
                    $('#UPerror').html(d.error);
                } else
                    $('#UPerror').html(d.text);
            });
        }

        $("#getPwd").click(function() {
            switch (check()) {
                case "email":
                    getPwd("<?= $this->controller->createUrl("login/recoveryEmail") ?>");
                    break;
                case "phone":
                    getPwd("<?= $this->controller->createUrl("login/recoveryPhone") ?>");
                    break;
                case "login":
                    $('#UPerror').html("К сожалению, восстановить пароль по логину нельзя. Укажите <b>телефон</b> либо <b>e-mail</b>.")
                    break;

            }
            return false;
        });

        $("#login-form").submit(function() {
            switch (check()) {
                case "email":
                    tryIn("<?= $this->controller->createUrl("login/email") ?>");
                    break;
                case "phone":
                    tryIn("<?= $this->controller->createUrl("login/phone") ?>");
                    break;
                case "login":
                    tryIn("<?= $this->controller->createUrl("login/login") ?>");
                    break;

            }
            return false;
        });
        /*
         $('#register-form, #login-form').ajaxForm(function(d) {
         d = $.parseJSON(d);
         if (d.error) {
         $('#UPerror').html(d.error);
         } else
         document.location.href = d.redirect;
         })*/
    </script>
</div>