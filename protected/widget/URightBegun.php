<?php

/**
 * виджет "лента объявлений", показывает переданные объявления в виде ленты
 * Принимает следующие параметры:
 * items - массив элементов, каждый элемент состоит из:
 *      img, price, description, href, class, id
 * count - количество напрямую отображаемых элементов
 * class - класс блока отображения
 * id - ид всего блока
 * descriptionLength - количество символов описания
 */
class URightBegun extends CWidget {

    var $items = array();
    var $id = "";
    var $class = "";
    var $descriptionLength = 30;

    public function init() {
        Yii::app()->clientScript->registerCssFile('css/URightBegun.css');
        ?>
        <div 
        <?php if (!empty($this->id)) echo 'id="' . $this->id . '" '; ?> 
            class="<?= $this->class ?>"
            >
                <?php
                foreach ($this->items as $item) {
                    ?>
                <div 
                    class="rbBlock <?= (!empty($item['class'])) ? $item['class'] : "" ?>"
                    <?php if (!empty($item['id'])) { ?> id="<?= $item["id"] ?>" <?php } ?>
                    >
                    <a href="<?= $item['href'] ?>">
                        <div class="rbImage">
                            <img src="<?= $item['img'] ?>" class="radiused10">
                            <?php if (isset($item['price']) && $item['price'] != -1) { ?>
                            <div class="rbPrice radiused10-bottom"><?= Helpers::price($item['price']) ?></div>
                            <?php } ?>
                        </div>
                    </a>
                    <?php if (!is_null($item['description'])) { ?>
                        <div class="rbDescription">
                            <a href="<?= $item['href'] ?>" class="onmain">
                                <?= Helpers::cutString($item['description'], $this->descriptionLength) ?>
                            </a>
                        </div>
                    <?php } ?>
                </div>
                <?php
            }
            ?>
        </div> <?php
    }

}

;