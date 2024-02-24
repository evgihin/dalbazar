<?php
/* @var $this AdvertController */
/* @var $images array */
/* @var $cities array */
/* @var $phones array */
/* @var $rootCategories array Корневые категории, в них нельзя добавлять объявления, только для классификации */
/* @var $subCategories array Подкатегории, ключем массива является ИД родителя */
/* @var $shortSubCategories array Подкатегории, ключем массива является ИД родителя, включены только ид, ид родителя и имя */

Yii::app()->clientScript->registerScriptFile('/js/jquery-ui-1.9.1.custom.min.js');
Yii::app()->clientScript->registerCssFile('/css/smoothness/jquery-ui-1.9.1.custom.min.css');
Yii::app()->clientScript->registerScriptFile('/js/jquery.validate.min.js');
Yii::app()->clientScript->registerCssFile("/css/addAdvert.css");
?>
<?php if ($this->_errors) echo CHtml::errorSummary($this->_errors) ?>
<form method="post" action="<?= $this->createUrl('advert/insert') ?>" id="advertForm">
    <table>
        <?php
        $this->renderPartial("addCategory", array(
            "rootCategories" => $rootCategories,
            "shortSubCategories" => $shortSubCategories
        ));
        ?>
        <tr>
            <td><label for="text">Текст:</label></td>
            <td colspan="2"><textarea name="add[text]" id="text" cols="50" rows="7"></textarea></td>
        </tr>
        <tr>
            <td colspan="3"><div id="filters"></div></td>
        </tr>
        <tr>
            <td><label for="zagoovok">Заголовок:</label></td>
            <td colspan="2">
                <input type="text" name="add[zagolovok]" id="zagolovok" size="45"><?= Helpers::htmlTooltip('Кратко о Вашем объявлении.<br>
                Плохой практикой являются заголовки типа: <br>
                <b>Продам</b> или  <b>Продам авто</b><br>
                для объявления, опубликованного в категории <i>авто</i>. <br>
                Максимум - 130 символов.') ?>
            </td>
        </tr>
        <tr>
            <td><label for="price">Цена:</label></td>
            <td colspan="2">
                <input type="text" name="add[price]" id="price" size="8">р.<?= Helpers::htmlTooltip('Цена указывается в рублях без точек, запятых и копеек. Максимум - 99999999р.') ?>
            </td>
        </tr>

        <?php $this->renderPartial("addPhones", array("phones" => $phones)); ?>

        <tr>
            <td><label for="email">E-mail:</label></td>
            <td colspan="2">
                <input type="text" name="add[email]" id="email" value="<?= Yii::app()->user->getState('email', "") ?>"><?= Helpers::htmlTooltip("Адрес почтового ящика будет виден для посетителей") ?>
            </td>
        </tr>
        <tr>
            <td><label for="city">Город:</label></td>
            <td colspan="2">
                <select name="add[city]" id="city" autocomplete="off">
                    <option value="0" selected="" >Выберите город</option>
                    <?php foreach ($cities as $city) { ?>
                        <option value="<?= $city['city_id'] ?>" <?php if (Yii::app()->user->getState('city_id') == $city['city_id']) echo 'selected'; ?> ><?= $city['name'] ?></option>
                        <?php
                    }
                    ?>
                </select>
            </td>
        </tr>
    </table>

    <script>
        $.validator.setDefaults({
            errorPlacement: function(er, el) {
                er.appendTo(el.parents("td:first"));
            },
            errorElement: "div",
            errorClass: "invalid",
            validClass: "valid",
            rules: {
                "add[phone1]": "required",
                "add[email]": "email",
                "add[price]": {
                    digits: true,
                    range: [0, 99999999]
                },
                "add[rootcategory]": {min: 1},
                "add[category]": {min: 1},
                "add[city]": {min: 1},
                "add[text]": {maxlength: 3000},
                "add[zagolovok]": {
                    required: true,
                    maxlength: 130
                }
            },
            messages: {
                "add[rootcategory]": "Выберите категорию",
                "add[category]": "Выберите подкатегорию",
                "add[city]": "Укажите Ваш город",
            }
        });

        var filterChache = {"0": ""};
        function updateFilters() {
            if (typeof(filterChache[$('#subcategory').val()]) != "undefined")
                $('#filters').html(filterChache[$('#subcategory').val()]);
            else
                $.post('<?= $this->createUrl('filter/getByCategory') ?>', {categoryId: $('#subcategory').val(), fieldName: 'add'}, function(data) {
                    $('#filters').html(data);
                    filterChache[$('#subcategory').val()] = data;
                });
        }
        $('#subcategory').on('change', updateFilters);
        updateFilters();</script>

    <br>
    <br>

    <?php $this->renderPartial("addPictures", array("images" => $images)); ?>

</form>
<?php if (Yii::app()->user->isGuest) { ?>
    <div style="display: none;" id="smsBlock">

        <div id="inputCode" style="display: none;">
            <form>
                <div class="advSmsText"></div>
                <div class="advSmsCode">
                    Код подтверждения: <input type="text" size="<?= Yii::app()->params['smsCodeLength'] ?>" id="confirmationCode">
                </div>
                <div class="advSmsErr red"></div>
                <div class="advSmsBtn">
                    <input type="submit" value="Подтвердить">
                </div>
                <div class="advSmsRepeat"><a class="inactive" href="#">повторно отправить SMS</a></div>
            </form>
        </div>

        <div id="showMessage" style="display: none;">
            <div class="advSmsText"></div>
            <div class="advSmsErr"></div>
            <div class="advSmsBtn">
                <button>Закрыть</button>
            </div>
        </div>

        <div id="showSuccess" style="display: none;">
            <div class="advSmsText"></div>
            <div class="advSmsErr"></div>
            <div class="advSmsBtn">
                <button>Продолжить</button>
            </div>
        </div>
    </div>
    <script>
            var logined = false;</script>
<?php } ?>

<script>
    //обрабатывает результаты запросов к серверу
    function processSmsData(d) {
        try {
            var resp = $.parseJSON(d);
        } catch (err) {
            alert("Ошибка обращения к серверу. Попробуйте позже.");
            return;
        }
        $("#smsBlock>div").hide();
        var obj;
        switch (resp.state) {
            case "sended":
            case "rejected|resend":
                obj = $("#inputCode");
                obj.find(".advSmsCode input").val("").focus();
                break;
            case "rejected|end":
            case "notRemainMessage":
            case "error":
                obj = $("#showMessage");
                break;
            case "confirmed":
                obj = $("#showSuccess");
                $("<input>", {
                    type: "hidden",
                    name: "turned_phones[]",
                    value: $("#phone1").val()
                }).appendTo($('#advertForm'));
                logined = true;
                break;
        }
        obj.find(".advSmsText").text(resp.text);
        obj.find(".advSmsErr").text("");
        obj.show();
        $("#smsBlock").dialog({
            title: "Осталось попыток: " + resp.remain,
            modal: true
        });
    }



    $("#inputCode>form").submit(function() {
        //проверка на количество символов в сроке
        if (/^[0-9a-zA-Z]{<?= Yii::app()->params['smsCodeLength'] ?>}$/g.test($(this).find(".advSmsCode input").val())) {
            $.post(
                    "<?= $this->createUrl('advert/checkPhoneCode') ?>",
                    {'phone': $("#phone1").val(), 'code': $("#confirmationCode").val()},
            processSmsData);
        } else {
            $(this).find(".advSmsErr").text("Неверно указан код");
            $(this).find(".advSmsCode input").focus();
        }
        return false;
    });
    $("#showMessage button").click(function() {
        $("#smsBlock").dialog("close");
    });
    $("#showSuccess button").click(function() {
        $("#smsBlock").dialog("close");
        $("#advertForm").submit();
    });
    $("#inputCode .advSmsRepeat a").click(function() {
        $.post(
                "<?= $this->createUrl('advert/checkPhone') ?>",
                {'phone': $("#phone1").val()},
        processSmsData);
        return false;
    })

    $('#advertForm').submit(function() {
        if (uploader.state != plupload.STOPPED)
            return false;
        else if (!$("#advertForm").valid())
            return false;
        else {
            if (!logined) {
                $.post(
                        "<?= $this->createUrl('advert/checkPhone') ?>",
                        {'phone': $("#phone1").val()},
                processSmsData);
                return false;
            }
        }
    });
</script>


