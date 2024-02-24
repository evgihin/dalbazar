<?php 

?>
<h1>История обновлений сайта:</h1>
<?php foreach ($log as $commit) { 
    $commit['message'] = explode("\n", $commit['message']);
    //вытягиваем первую строку
    $firstLine = array_shift($commit['message']);
    $commit['message'] = implode("<br>", $commit['message']);
    ?>
    <hr>
    <?= $commit['author'] . ". " . date('d.m.Y h:m', strtotime($commit['date'])) ?><br>
    <span class="bold"><?= $firstLine ?></span><br>
    <?= $commit['message'] ?>
    <?php
}
?>

