<?php

class vCategory extends CValidator {
    
    public $isRoot=NULL; //Является ли корневой, или это родительская

    protected function validateAttribute($object, $attribute) {
        $value = $object->$attribute;
        
        $error = false;
        
        $category = new Category();
        if (!$category->checkAvailable((int) $value)) {
            $error = true;
        } else {
            if ($this->isRoot!==NULL){
                $parent = $category->getByCategory((int) $value)["category_parent_id"];
                if ($this->isRoot!=$parent)
                    $error = true;
            }
        }
        
        if ($error){
            $message=$this->message!==null?$this->message:'Указанной категории не существует, либо указана неверная категория';
            $this->addError($object, $attribute, $message);
        }
    }

}

?>
