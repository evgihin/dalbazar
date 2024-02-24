<?php

class UCategoryChanger extends CWidget {
    
   var $category_id = false; //подсвечивать ли категорию по умолчанию
   var $parent_id = 0; //ИД родителя, детей которого отображать
   var $autocomplete = false; //разрешать браузеру запоминать последнее заданное значение (не рекомендуется ставить)
   var $class = ''; //класс элемента управления
   var $params = array(); //параметры элемента select
   var $name = 'city'; //имя переменной
   var $id = '';
   var $emptyText = '(Не выбрано)';

    public function init() {
        $cCategory = new Category();
        if ($this->parent_id){
            $categories = $cCategory->get($this->parent_id, NULL, true);
        } else {
            $categories = $cCategory->get(NULL,NULL,true);
        }

        $this->render('categories', array(
            'categories'=>$categories
        ));
    }

}

;
