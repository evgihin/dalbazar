<?php
/* @var $this AdvertController */
/* @var $phones array */

Yii::app()->clientScript->registerScriptFile('/js/jquery.maskedinput-1.3.min.js');
?>
<tr>
    <?php
//для гостей показываем поле ввода основного телефона
    if (Yii::app()->user->isGuest) {
        ?>
        <td><label for="phone1">Основной телефон:</label></td>
        <td>
            <input type="text" name="add[phone1]" id="phone1" size="17" value="" class="mobilePhone">
            <?= Helpers::htmlTooltip('Ваш основной телефон в международном формате, 10 цифр.<br>
            пример: <b>9141234567</b><br>
            Необходимо указывать только мобильный номер телефона.
            На указанный номер Вы получите SMS-подтверждение.
            Дальнейшее редактирование объявления возможно только после авторизации по основному телефону.') ?>
        </td>

    <?php } else { ?>
        <td>
            <label>Телефоны, доступные посетителям:
                <?= Helpers::htmlTooltip('Укажите, какие телефоны будут доступны для просмотра посетителям. Необходимо указать как минимум 1 телефон.') ?>
            </label>
        </td>
        <td>
            <ul class="advAddPhoneList">
                <?php foreach ($phones as $phone) { ?>
                    <li>
                        <?= $phone['phone'] ?>
                        <input type="checkbox" name="turned_phones[]" class="advPhoneChbx phoneSelect" value="<?= $phone['phone'] ?>" checked="checked">
                    </li>
                    <?php
                }
                ?>
            </ul>
        </td>
    <?php } ?>
    <td>
        <div id="DopPhonesForms">
            <div id="DopPhones" class="cornered shadowed"></div>
            <a id="addDopPhone" class="inactive">Добавить дополнительный телефон</a>
        </div>
    </td>
</tr>

<script>
    $('#phone1').mask('+7 (999) 999-9999');

    var dopPhoneCount = 0;
    function changeAddPhoneBtn() {
        if (dopPhoneCount >= 5)
            $("#addDopPhone").addClass("disabled");
        else
            $("#addDopPhone").removeClass("disabled");

        if (dopPhoneCount == 0) {
            $("#DopPhones").hide();
        } else {
            $("#DopPhones").show();
        }
    }
    changeAddPhoneBtn();

    function addPhone(num) {
        if (dopPhoneCount < 5) {

            //создаем корневой элемент
            var elem = $("<div/>", {class: "DopPhone"}).hide().appendTo("#DopPhones");
            //поле ввода и кнопка "удалить"
            $("<span/>", {
                class: "delDopPhone",
                title: "Удалить дополнительный телефон"
            }).appendTo(elem);
            $("<input/>", {
                type: "text",
                name: "phones[]",
                size: "17",
                css: {"width": "120px"},
                class: "dopMobilePhone"
            }).val(num).mask('+7 (999) 999-9999').appendTo(elem);

            //отображаем на экране
            elem.show(300);
            dopPhoneCount++;
        }
        changeAddPhoneBtn();
    }

    $("#addDopPhone").click(addPhone);
    $("#DopPhonesForms").on("click", ".delDopPhone", function() {
        if (dopPhoneCount > 1) { //если телефоны еще остались, удаляем. последнее поле оставляем
            $(this).parent().remove();
            $(this).siblings("input").val("");
        }
        dopPhoneCount--;
        changeAddPhoneBtn();
    });
    $.validator.addMethod('mobilePhone', function(val) {
        return <?= Phone::regExp(true) ?>.test(val);
    }, "Телефон указан неверно");

    $.validator.addClassRules({
        "phoneSelect": {
            required: true,
            messages: {required:"Необходимо указать как минимум 1 телефон"}
        },
        "mobilePhone": {
            required: true,
            mobilePhone: true,
        },
        "dopMobilePhone": {
            mobilePhone: true,
            required: false,
        }
    });
</script>