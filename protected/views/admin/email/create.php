<?php
Yii::app()->clientScript->registerScriptFile("js/nicEdit/nicEdit.js");
$email = Yii::app()->user->getState("email");
?>
<script>
    $(nicEditors.allTextAreas);
</script>
Тема: <input type="text" id="emailTitle">
<h3 id="textEdit">
    Текст сообщения: 
    <a href="<?= $this->createUrl("admin/email/delivery") ?>#preView" class="inactive" style="font-size: 0.6em;">посмотреть</a>
</h3>
<textarea id="email" cols="75" rows="15"></textarea>
<?php if ($email) { ?><button id="preSend">Отправить на <?= $email ?></button><?php } ?>

<h3 id="preView">
    Предпросмотр сообщения: 
    <a href="<?= $this->createUrl("admin/email/delivery") ?>#textEdit" class="inactive" style="font-size: 0.6em;">редактировать</a>
</h3>
<div id="preViewBlock" class="cornered bordered" style="padding: 5px;"></div>
<script>
    template = "<?= preg_replace("/[\n\r]/", "", str_replace('"', '\"', $template)) ?>";
    function updatePreview() {
        $("#preViewBlock").html(template.replace("{content}", nicEditors.findEditor("email").getContent()));
    }
    $(document).on("keyup mouseup", updatePreview);
    $(updatePreview);

    $("#preSend").click(function() {
        $.post("<?= $this->createUrl("admin/email/deliveryTest") ?>", 
        {
            text: nicEditors.findEditor("email").getContent(), 
            title:$("#emailTitle").val()
        });
        alert("Ваше сообщение придет Вам в ближайшее время");
    });
</script>