<?php
$ddList = array(
    $this->createUrl("admin/farpost/listParseTask",array("show"=>"all")) => "все задачи",
    $this->createUrl("admin/farpost/listParseTask",array("show"=>"active")) => "в работе",
    $this->createUrl("admin/farpost/listParseTask/",array("show"=>"archive")) => "выполнено"
);
$default = $this->createUrl("admin/farpost/listParseTask/",array("show"=>$show));
?>
Статус задачи: <?= CHtml::dropDownList("filter", $default, $ddList) ?>
<script>
    $("#filter").change(function(){
        document.location.href = $(this).val();
    });
</script>
<table>
    <tr>
        <th>#</th>
        <th>парсер</th>
        <th>команда</th>
        <th>добавлено</th>
        <th>завершено</th>
        <th>выполнено</th>
        <th>описание</th>
        <th>код ошибки</th>
    </tr>
    <?php
    foreach ($tasks as $task) {
        ?>
    <tr>
        <td><?= $task['parse_task_id'] ?></td>
        <td><?= $task['parser'] ?></td>
        <td><?= $task['command'] ?></td>
        <td><?= date("H:i d.m", $task['create_time']) ?></td>
        <td><?= date("H:i d.m", $task['end_time']) ?></td>
        <td><?= $task['complete'] ?>%</td>
        <td><?= $task['description'] ?></td>
        <td><?= $task['error'] ?></td>
    </tr>
    <?php
    }
    ?>
</table>
<?php $this->widget('CLinkPager', array(
    'pages' => $pages,
)) ?>