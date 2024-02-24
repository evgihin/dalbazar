<?php

class FarpostController extends CFrontEndController{
    function actionAddpage(){
        if (!Helpers::required($_REQUEST, array('key','data'), false))
            throw new CHttpException(400,"Неверные параметры запроса");
        if ($_REQUEST['key']!='mykey')
            throw new CHttpException(401,"неверный ключ");
        $data = $_REQUEST['data'];
        $data = CJSON::decode($data);
        $cFarpost = new Farpost();
        foreach ($data as $adv){
            $cFarpost->addParseAdvert($adv);
        }
    }
}
?>
