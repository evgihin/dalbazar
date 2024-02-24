<?php
/* $this CWidget */
/* $city array текущий город */
/* $cities array список городов с полями согласно БД */
?>
<a href="#" class="inactive cityBtn onmain"><?= $city['name'] ?></a>
<div class="cityChanger cornered10 shadowed bordered hidden">
    <a href="#" class="inactive cityBtn onmain"><?= $city['name'] ?></a>
    <div class="cityBtnPadding"></div>
    <?php
    foreach ($cities as $id => $val) {
        ?><a href="#" class="cityElem cityElem<?= $val['style'] ?>"><?= $val['name'] ?></a>
        <?php
        if (Helpers::odder() == 'even')
            echo '<br class="clear2" />';
    }
    ?>
</div>
<script>
    $(function() {
        $('.cityBtn').dropdown(".cityChanger");
    })
</script>