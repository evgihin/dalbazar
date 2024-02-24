<?php

class Actions extends CWidget {

    /**
     *
     * @var array принимает список действий.<br>
     * Все они делятся на 2 категории: <b>ссылки (link)</b> и подтверждение <b>формы (submit)</b><br>
     * Если действие одно из стандартных, то ключ массива обозначает имя действия,
     * а зачение: <b>URI</b> для <i>ссылок</i>, или <b>ИД формы</b>, которую подтвердить для <i>форм</i>.<br>
     * Стандартные значения ссылок: add, close.<br>
     * Стандартные значения форм: save, apply, delete.<br>
     * Если действие нестандартное то ключ массива именует действие, а значение должно быть в виде массива:<br>
     * array(<br>
     *  'type'=>'<b>link</b> OR <b>submit</b>' , <br>
     *  'action'=>'<b>URI</b> or <b>FORM_ID</b>' , <br>
     *  'icon'=>'../path/to/icon.jpg' , <br>
     *  'label'=>'text label of action' , <br>
     *  'actionParams'=>array('params_of'=>'URI)
     * );
     */
    public $actions = array();

    /**
     * $this->actions принимает параметры отображения приборной панели. Возможные значения:
     *      type - тип кнопки. Возможно одно из трех значений: link, form, js.
     * 	type => link - тип кнопки, которая работает как обычная ссылка.
     * 	Если задан этот тип, то необходимо указать дополнительный параметр href.
     * 	href задается как URI сайта и может быть либо строкой либо массивом. Задается аналогично this->redirect().
     * 	type => form - отправляет определенную форму на сервер, передавая в теле запроса дополнительный параметр.
     * 	Если задан тип формы, необходимо указать ид формы, которую отправлять на сервер. задается параметром id.
     * 	на сервер дополнительно передается поле action, которое принимает значение name кнопки
     * 	type => js - блок js, срабатывающий при нажатии. Дополнительно нужно указать action. это javascript, который выполнится при клике.
     *      label - подпись под кнопкой
     *      icon - адрес до картинки с иконкой относительно корневого катклога сайта
     *
     * В параметрах есть готовые пресеты.
     * add,close,save,apply,delete,undelete
     */
    public function init() {
        $actions = array_reverse($this->actions->getActs());
        foreach ($actions as $action => $value)
            $this->_showBtn($action, $value);
    }

    private function _showBtn($name, $params) {
        ?>
        <div id="a<?= $name ?>">
            <?php
            switch ($params['type']) {
                case "link":
                    if (is_string($params['href'])) {
                        $route = $params['href'];
                        $attr = array();
                    } else {
                        $route = isset($params['href'][0]) ? $params['href'][0] : '';
                        $attr = array_splice($params['href'], 1);
                    }
                    echo CHtml::openTag("a", array('href' => $this->controller->createUrl($route, $attr)));
                    break;
                case "submit": case "form" :
                    echo CHtml::openTag("a", array(
                        'href' => '#',
                        'class' => "aActSubmit",
                        'data-id' => $params['id'],
                        'data-name' => $name
                    ));
                    $this->_showJs();
                    break;
                case "js":
                    echo CHtml::openTag("a", array(
                        'href' => '#',
                        'onclick' => $params['action'],
                        'class' => "aActJs",
                    ));
                    $this->_showJs2();
                    break;
            }
            ?>

            <div class = "aIcon" style = "background-image: url(<?= $params['icon'] ?>);"></div>
            <?= $params['label'] ?>
            <?= CHtml::closeTag('a'); ?>
        </div>
        <?php
    }

    private function _showJs() {
        Yii::app()->clientScript->registerScript('adminActions', "
			$('.aActSubmit').click(function(){
				$('<input>').attr('name','action').attr('type','hidden').val($(this).data('name')).appendTo( '#' + $(this).data('id') );
				$( '#' + $(this).data('id') ).submit();
				return false;
			});
", CClientScript::POS_READY);
    }

    private function _showJs2() {
        Yii::app()->clientScript->registerScript('adminActions2', "
			$('.aActJs').click(function(){
				return false;
			});
", CClientScript::POS_READY);
    }

}
