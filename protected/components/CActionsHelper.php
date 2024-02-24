<?php

class CActionsHelper {

    private $_acts = array();

    /**
     * Задает расширенную иконку, работающую как ссылка
     * @param string $name Имя параметра
     * @param mixed $link Если строка - то YII путь до контроллера, если массив то сначала путь потом параметры
     * Пример: new CActionHelper("index",array("site/index","do"=>"delete"),"На главную",'path/to/icon.png') Создаст ссылку на site/index?do=delete 
     * @param string $label Имя иконки
     * @param string $icon Путь к иконке (картинка)
     * @return CActionsHelper
     */
    public function extendLink($name, $link, $label, $icon) {
        $this->_acts[$name] = array(
            'type' => 'link',
            'href' => $link,
            'label' => $label,
            'icon' => $icon
        );
        return $this;
    }

    /**
     * Задает расширенную иконку, работающую как submit формы
     * @param string $name Имя параметра. Оно эе передается на сервер в виде параметра формы "action"
     * @param string $formId ИД формы, для которой надо вызвать submit()
     * @param string $label надпись кнопки
     * @param string $icon путь до иконки
     * @return \CActionsHelper
     */
    public function extendSubmit($name, $formId, $label, $icon) {
        $this->_acts[$name] = array(
            'type' => 'form',
            'id' => $formId,
            'label' => $label,
            'icon' => $icon
        );
        return $this;
    }

    public function extendJS($name, $onClick, $label, $icon) {
        $this->_acts[$name] = array(
            'type' => 'js',
            'action' => $onClick,
            'icon' => $icon,
            'label' => $label
        );
        return $this;
    }

    /**
     * Кнопка "добавить"
     * @param string $link путь на добавление
     * @return \CActionsHelper
     */
    public function add($link) {
        $this->_acts['add'] = array(
            'type' => 'link',
            'label' => 'добавить',
            'icon' => 'images/theme/icon-32-new.png',
            'href' => $link
        );
        return $this;
    }

    public function save($formId) {
        $this->_acts['save'] = array(
            'type' => 'form',
            'label' => 'сохранить',
            'icon' => 'images/theme/icon-32-save.png',
            'id' => $formId
        );
        return $this;
    }

    public function apply($formId) {
        $this->_acts['apply'] = array(
            'type' => 'form',
            'label' => 'применить',
            'icon' => 'images/theme/icon-32-apply.png',
            'id' => $formId
        );
        return $this;
    }

    public function delete($formId) {
        $this->_acts['delete'] = array(
            'type' => 'form',
            'label' => 'удалить',
            'icon' => 'images/theme/icon-32-trash.png',
            'id' => $formId
        );
        return $this;
    }

    public function undelete($formId) {
        $this->_acts['undelete'] = array(
            'type' => 'form',
            'label' => 'восстановить',
            'icon' => 'images/theme/icon-32-delete.png',
            'id' => $formId
        );
        return $this;
    }

    public function close($link) {
        $this->_acts['close'] = array(
            'type' => 'link',
            'label' => 'закрыть',
            'icon' => 'images/theme/icon-32-cancel.png',
            'href' => $link
        );
        return $this;
    }
    
    /**
     * Удаляет ранее добавленое действие
     * @param string $name имя действия
     * @return \CActionsHelper
     */
    public function removeAction($name){
        if (isset($this->_acts[$name]))
            unset ($this->_acts[$name]);
        return $this;
    }

    public function getActs() {
        return $this->_acts;
    }
    
    public function isEmpty(){
        return empty($this->_acts);
    }

}
