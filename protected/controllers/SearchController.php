<?php

class SearchController extends CFrontEndController{
    
    function actionResult($q){
        Yii::import("application.extensions.Sphinxapi", true);
        
        $q = trim(strip_tags($q));
        
        if (mb_strlen($q)<1)
            return $this->render("noResult",array(
                "query" => $q,
                "text" => "Запрос слишком короткий для обработки"
            ));
        
        // Создадим объект - клиент сфинкса и подключимся к нашей службе
        $cl = new SphinxClient();
        $cl->SetServer( "localhost", 9312 );
        
        //настаиваем постраничную навигацию
        $resultPerPage = 10;
        $maxItems = $resultPerPage*100; //100 страниц максимум
        $cPagination = new CPagination($maxItems);
        
        // Собственно поиск
        $cl->SetLimits($cPagination->getOffset(), $cPagination->getLimit(), $maxItems);
        $cl->SetMatchMode( SPH_MATCH_ANY  ); // ищем хотя бы 1 слово из поисковой фразы
        $result = $cl->Query($q); // поисковый запрос
        
        if ( (!$result || $cl->GetLastWarning() ) && YII_DEBUG ) { 
          echo "Query failed: " . $cl->GetLastError() . ".\n"; // выводим ошибку если произошла
          echo "WARNING: " . $cl->GetLastWarning() . ".\n"; // выводим предупреждение если оно было
        }
        
        if (!$result || empty($result["matches"]))
            return $this->render("noResult",array(
                "query" => $q,
                "text" => "По Вашему запросу ничего не найдено"
            ));
      else {
          $cAdvert = new Advert;
          $cImage = new Image;
          
          $cPagination->setItemCount($result["total_found"]);
          
          $adverts = $cAdvert->getByAdvert(array_keys($result["matches"]));
          $images = Helpers::groupBy($cImage->getByAdvertFull(array_keys($result["matches"])), "advert_id");
          $this->render("result",array(
              "query" => $q,
              "adverts" => $adverts,
              "images" => $images,
              "pagination" => $cPagination,
              "count" => $result["total_found"]
          ));
      }
    }
}
