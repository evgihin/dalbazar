<?php

/**
 * виджет "лента объявлений", показывает переданные объявления в виде ленты
 * Принимает следующие параметры:
 * items - массив элементов, каждый элемент состоит из:
 *      img, sdescription, description, href, class, id
 * count - количество напрямую отображаемых элементов
 * class - класс блока отображения
 * id - ид всего блока
 * descriptionLength - количество символов описания
 */
class UAdvertBegun extends CWidget {

    var $items = array();
    var $count = 5;
    var $id = "";
    var $class = "";
    var $descriptionLength = 30;

    public function init() {
        Yii::app()->clientScript->registerCssFile('css/UAdvertBegun.css');
        Yii::app()->clientScript->registerScriptFile('js/UAdvertBegun.js');
        ?>
        <div 
        <?php if (!empty($this->id)) echo 'id="' . $this->id . '" '; ?> 
            class="abMain radiused <?= $this->class ?>"
            >
            <div class="abLeft"><div class="abLeftArrow"></div></div> 
            <div class="abContainer">
                <div class="abScroll">
                    <?php
                    foreach ($this->items as $item) {
                        ?>
                        <div 
                            class="abBlock <?= (!empty($item['class'])) ? $item['class'] : "" ?>"
                            <?php if (!empty($item['id'])) { ?> id="<?= $item["id"] ?>" <?php } ?>
                            >
                            <div class="atImage">
                                <a href="<?= $item['href'] ?>">
                                    <img src="<?= $item['img'] ?>" class="radiused" style="max-width: 120px; max-height: 120px;">
                                </a>
                            </div>
                            <?php if (!is_null($item['description'])) { ?>
                                <div class="atDescription">
                                    <a href="<?= $item['href'] ?>">
                                        <?= Helpers::cutString($item['description'], $this->descriptionLength) ?>
                                    </a>
                                </div>
                            <?php } ?>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div> 
            <div class="abRight"><div class="abRightArrow"></div></div> 
        </div> <?php
    }

}

;