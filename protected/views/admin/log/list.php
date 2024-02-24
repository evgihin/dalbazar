<?php
Yii::app()->clientScript->registerScriptFile("js/chosen-1.1.0/chosen.jquery.min.js");
Yii::app()->clientScript->registerCssFile("js/chosen-1.1.0/chosen.min.css");
?>
<h1>Статистика действий по сайту</h1>
<form method="GET" action="<?= $this->createUrl("admin/log") ?>">
    <?php
    $pathVariants = array("0" => "(все варианты)") + $pathVariants;
    $modelVariants = array("0" => "(все варианты)") + $modelVariants;
    $actionVariants = array("0" => "(все варианты)") + $actionVariants;
    $userVariants = array("0" => "(все пользователи)") + $userVariants;

    echo CHtml::label("раздел:", "path");
    echo CHtml::dropDownList("path", $path, $pathVariants);
    echo CHtml::label("модуль:", "model");
    echo CHtml::dropDownList("model", $model, $modelVariants);
    echo CHtml::label("действие:", "action");
    echo CHtml::dropDownList("action", $action, $actionVariants);
    echo CHtml::label("пользователь:", "user_id");
    echo CHtml::dropDownList("user_id", $userId, $userVariants);
    echo CHtml::label("дата (с):", "from");
    echo CHtml::textField("from", ((int) $from) ? date("d.m.Y", $from) : "");
    echo CHtml::label("дата (до):", "to");
    echo CHtml::textField("to", ((int) $to) ? date("d.m.Y", $to) : "");
    ?>
    <br>
    <span class="inactive filterVariant" id="today">сегодня</span>
    <span class="inactive filterVariant" id="yesterday">вчера</span>
    <span class="inactive filterVariant" id="before_yesterday">позавчера</span><br>
    <span class="inactive filterVariant" id="week">неделя</span>
    <span class="inactive filterVariant" id="month">месяц</span>
    <span class="inactive filterVariant" id="year">год</span><br>
    <?php
    echo CHtml::submitButton("применить");
    ?>
</form>
найдено записей: <?= $pages->getItemCount() ?>
<table style="width: 100%">
    <tr>
        <th>#</th>
        <th>пользователь</th>
        <th>путь</th>
        <th>действие</th>
        <th>параметры</th>
        <th>время</th>
    </tr>
<?php foreach ($items as $item) { ?>
        <tr class="<?= Helpers::odder() ?>">
            <td><?= $item['log_id'] ?></td>
            <td><?= $item['login'] . " (" . Helpers::simplifyName($item, "&nbsp;") . ") " ?></td>
            <td><?= implode("/", array($item['path'], $item['model'], $item['action'])) ?></td>
            <td><?= $item['description'] ?></td>
            <td><?php
    $par = unserialize($item['params']);
    if ($par)
        foreach (unserialize($item['params']) as $id => $param) {
            echo $id . ' : ' . $param . "; ";
        }
    ?></td>
            <td><?= date("d.m.Y H:i:s", $item['time']) ?></td>
        </tr>
<?php }
?>
</table>
<div id="catPagination">
<?php
$this->widget('CLinkPager', array(
    'pages' => $pages,
    'prevPageLabel' => '←',
    'nextPageLabel' => '→',
    'cssFile' => FALSE,
    'header' => '',
    'footer' => '',
    'htmlOptions' => array('class' => '')
));
?>
</div>
<script>
    $("select").chosen({
        "search_contains": true
    });
    $("#from, #to").datepicker({dateFormat: "dd.mm.yy"});
    $("form").submit(function() {
        $("#from, #to").each(function() {
            if ($(this).val())
                $(this).val(new Date($(this).val().replace(/(\d+).(\d+).(\d+)/, '$2/$1/$3')).getTime() / 1000 - (new Date().getTimezoneOffset()*60));
        });
    });
    function setTimeFrames(from,to){
        $("#from").datepicker("setDate", new Date(from));
        if (to)
            $("#to").datepicker("setDate", new Date(to));
    }
    nowTimestamp = new Date().getTime();
    $("#today").click(function(){ setTimeFrames(nowTimestamp,nowTimestamp+(1000*86400)); });
    $("#yesterday").click(function(){ setTimeFrames(nowTimestamp-(1000*86400),nowTimestamp); });
    $("#before_yesterday").click(function(){ setTimeFrames(nowTimestamp-(1000*86400*2),nowTimestamp-(1000*86400)); });
    $("#week").click(function(){ setTimeFrames(nowTimestamp-(1000*86400*7),nowTimestamp+-(1000*86400)); });
    $("#month").click(function(){ 
        var d = new Date();
        d.setMonth(d.getMonth()-1);
        setTimeFrames(d.getTime(),nowTimestamp); 
    });
    $("#year").click(function(){ 
        var d = new Date();
        d.setYear(d.getFullYear()-1);
        setTimeFrames(d.getTime(),nowTimestamp); 
    });

</script>
<style>
    label, .filterVariant{margin-left: 15px;}
</style>