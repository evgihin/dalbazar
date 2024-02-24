<?php

class CatalogController extends CAdminController {

    var $defaultAction = 'index';

    function actionIndex() {
        $this->render("index");
    }

    function actionTree() {
        $cCatalog = new Catalog();
        $items = Helpers::setId($cCatalog->get("catalog"), "catalog_id");
        $path = $cCatalog->getRecursivePath();
        $this->render("list", array(
            'items' => $items,
            'path' => $path,
        ));
    }

    function actionRemove($catalog_id) {
        $catalogId = (int) $catalog_id;
        $cCatalog = new Catalog;
        $cCatalog->remove($catalogId);
        Log::add("admin/catalog/remove",array("catalog_id"=>$catalogId));
        $this->redirect(array("admin/catalog/tree"));
    }

    function actionRecover($catalog_id) {
        $catalogId = (int) $catalog_id;
        $cCatalog = new Catalog;
        $cCatalog->recover($catalogId);
        Log::add("admin/catalog/recover",array("catalog_id"=>$catalogId));
        $this->redirect(array("admin/catalog/cart"));
    }

    function actionCart() {
        $cCatalog = new Catalog();
        $items = Helpers::setId($cCatalog->get("catalog", ""), "catalog_id");
        $path = $cCatalog->getRecursivePath("");
        $this->render("cart", array(
            'items' => $items,
            'path' => $path,
        ));
    }

    function actionAdd() {
        $cCatalog = new Catalog;
        if (!$this->model)
            $this->model = $cCatalog;

        $this->render("add", array(
            "treepath" => $cCatalog->getDropDownListArray()
        ));
    }

    function actionInsert() {
        $cCatalog = new Catalog;
        Helpers::required($_POST, array('action'));
        $cCatalog->attributes = $_POST;
        if ($cCatalog->validate()) {
            $id = $cCatalog->insert();
            
            Log::add("admin/catalog/insert",array("catalog_id"=>$id), "создан элемент каталога");

            if ($_POST['action'] == 'save') {
                $this->redirect(array("admin/catalog/tree"));
            } elseif($_POST['action']=='saveAndInsert')
                $this->redirect(array("admin/catalog/add"));
        } else {
            $this->model = $cCatalog;
            $this->actionAdd();
        }
    }

    function actionEdit($catalog_id) {
        $catalogId = (int) $catalog_id;
        $cCatalog = new Catalog;
        $catalog = $cCatalog->getById($catalogId);
        if (!$catalog)
            throw new CHttpException(400, "Нет такого элемента");

        if ($catalog['removed'])
            $treepath = $cCatalog->getDropDownListArray("");
        else
            $treepath = $cCatalog->getDropDownListArray();
            
        $this->render('edit', array(
            "catalog" => $catalog,
            "treepath" => $treepath
        ));
    }

    function actionSave($catalog_id) {
        $catalogId = (int) $catalog_id;
        $cCatalog = new Catalog;
        $cCatalog->attributes = $_POST;
        if ($cCatalog->validate()) {
            $cCatalog->update($catalogId);
            Log::add("admin/catalog/save",array("catalog_id"=>$catalogId), "обновлен элемент каталога");
            $this->redirect(array("admin/catalog/tree"));
        } else {
            $this->redirect(array("admin/catalog/edit", "catalog_id" => $catalog_id));
        }
    }

}
