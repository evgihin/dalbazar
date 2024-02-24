<?php

class Helpers {

    /**
     * группирует ассоциативный массив по определенному полю.
     * Например задан массив:
     * $arr = array(array('id'=>1,'b'=>2,'c'=>3),array('id'=>4,'b'=>5,'c'=>6))
     * Функция toAssoc($arr,'id') вернет упорядоченный по ИД-у массив:
     * array('1'=>array('id'=>1,'b'=>2,'c'=>3),'4'=>array('id'=>4,'b'=>5,'c'=>6))
     * Если всетретится несколько элементов с одинаковым ИД-ом,
     * то одной ассоциации будет соответствовать не значение а массив значений.
     * @param array $array массив для группировки
     * @param string $fieldId имя поля, по значениям которого группировать массив
     * @param string $fieldId2 группировка значений второго уровня
     * @return array сгруппированный массив
     */
    public static function toAssoc($array, $fieldId, $fieldId2 = '') {

        $res = array(); //результат операции
        $first = array(); //запоминает, первый раз записал или нет
        foreach ($array as $val) {
            $key = $val[$fieldId]; //ключ массива
            //если раньше значения с таким ключем не существовало - создадим его
            if (!$fieldId2 && !isset($res[$key])) {
                $res[$key] = $val;
                $first[$key] = false;
            }
            //иначе проверяем, если там уже много значений, то просто добавляем. если строка - делаем массив
            else {
                if (isset($first[$key]) && $first[$key] == false) {
                    $res[$key] = array($res[$key]);
                    $first[$key] = true;
                }
                $res[$key][] = $val;
            }
        }

        if ($fieldId2) {
            foreach ($res as &$val) {
                $val = Helpers::toAssoc($val, $fieldId2);
            }
        }

        return $res;
    }
    
    /**
     * Преобразовывает массив в форму, пригодную для использования в UCHtml::DropDownList
     * @param array $array Исходный массив
     * @param string $key Имя (или номер) столбца вмассиве, использующегося в качестве ключа. Внимание! Если ключ не будет уникальным, то запишется только последнее по порядку значение
     * @param string $values Имя (или номер) столбца масива, использующегося в качестве значения.
     * @return array
     */
    public static function simplify($array,$key,$values){
        $res = array();
        foreach ($array as $v) {
            $res[$v[$key]] = $v[$values];
        }
        return $res;
    }
    
    /**
     * группирует ассоциативный массив по определенному полю.
     * Делает то же самое что и toAssoc, только в любом случае результатом группировки будет двууровневый массив
     * Работает надежнее и правильнее
     * @return array сгруппированный массив
     */
    public static function groupBy($array, $key1, $key2 = null) {
        $result = array(); //результат операции
        foreach ($array as $value) {
            $key = $value[$key1];
            if (empty($result[$key]))
                $result[$key] = array($value);
            else
                $result[$key][] = $value;
        }
        if (!is_null($key2))
            foreach ($result as &$val) {
                $val = self::toAssoc($val, $key2);
            }
        return $result;
    }
    
    public static function groupAndSimplify($array,$key,$values){
        $result = array(); //результат операции
        foreach ($array as $value) {
            $k = $value[$key];
            if (empty($result[$k]))
                $result[$k] = array($value[$values]);
            else
                $result[$k][] = $value[$values];
        }
        return $result;
    }
    
    public static function setId($array,$key){
        $result = array(); //результат операции
        foreach ($array as $value) {
            $result[$value[$key]] = $value;
        }
        return $result;
    }

    /**
     * Получить один столбец двумерного массива
     * @param array $array массив из которого брать значения
     * @param string $fieldId имя поля, для котрого получить ИД-ы
     * @return array один столбец из исходного двумерного массива
     */
    public static function getIdArray($array, $fieldId) {
        $ids = array();
        foreach ($array as $c) {
            if (isset($c[$fieldId]))
                $ids[] = $c[$fieldId];
        }
        return $ids;
    }

    public static function hash($pass) {
        return md5('ssalt1' . md5($pass) . 'ssalt2');
    }

    /**
     * Рекурсивно получить все значения массива (элементы массива, не имеющие тип "массив"
     * @param array $array массив, из которого получать значения
     * @return array массив со всеми значениями
     */
    public static function getVarArray($array) {
        $retVal = array();
        foreach ($array as $r_pieces) {
            if (is_array($r_pieces)) {
                $retVal += Helpers::getVarArray($r_pieces);
            } else {
                $retVal[] = $r_pieces;
            }
        }
        return $retVal;
    }

    /**
     * Получить адресс картинки, либо заглушку, сигнализирующую об отсутствии картинки.
     * Попутно ресайзит картинки и кэширует результат.
     * Если не указана ни ширина ни высота, выдаст адресс начальной картинки,
     * иначе ресайзит сохраняя соотношение сторон (в любом случае соотношение будет сохранено)
     * @param string $image Имя файла с изображением, лежащего в директории images/advert
     * @param int $width ширина картинки. Если 0 то картинка не ресайзится в ширину
     * @param int $height Высота картинки. Если 0 то картинка не ресайзится в высоту
     * @return string адрес картинки относительно корневого каталога сайта
     */
    public static function getImageUrl($image, $width = 0, $height = 0) {

        //начальные настройки
        if (empty($image) || $image == 'no_image.jpg') {
            $path = 'images/theme/no_image/'; //путь к оригиналам
            $scaledPath = 'images/theme/no_image/'; //путь к ресайзнутым картинкам
            $fileName = basename('no_image.jpg'); //основное имя картинки, полученное от пользователя
            $scaledFileName = $width . '_' . $height . '_' . $fileName; // имя файла ресайзнутой картинки
        } else {
            $path = 'images/advert/'; //путь к оригиналам
            $scaledPath = 'images/advert/resize/'; //путь к ресайзнутым картинкам
            $fileName = basename($image); //основное имя картинки, полученное от пользователя
            $scaledFileName = $width . '_' . $height . '_' . $fileName; // имя файла ресайзнутой картинки
        }
        
        if ((new CUrlValidator())->validateValue($image))
            return $image;




        if (file_exists($scaledPath . $scaledFileName)) { //если файл уже ресайзен ранее
            return $scaledPath . $scaledFileName;
        } elseif (file_exists($path . $fileName) && !$width && !$height) { //если есть оригинал, но нет размеров
            return $path . $fileName;
        } elseif (file_exists($path . $fileName)) { //если оригинальный файл существует и указаны размеры (хотя бы width)
            Yii::app()->setComponents(array('imagemod' => array('class' => 'application.extensions.imagemodifier.CImageModifier')));
            //получает имя файла и расширение
            $temp = explode('.', $scaledFileName);
            if (!count($temp))
                return Helpers::getImageUrl('no_image.jpg', $width, $height);

            $scaledFileNameExt = $temp[count($temp) - 1]; //расширение ресайзнутой картинки
            unset($temp[count($temp) - 1]);
            $scaledFileNameBody = implode('.', $temp); //имя файла ресайзнутой картинки без разрешения

            $img = Yii::app()->imagemod->load($path . $fileName, 'ru_RU');
            if ($width || $height)
                $img->image_resize = true;
            if ($width && $height) {
                $img->image_ratio = true;
                $img->image_x = $width;
                $img->image_y = $height;
            } elseif ($width) {
                $img->image_ratio_y = true;
                $img->image_x = $width;
            } elseif ($height) {
                $img->image_ratio_x = true;
                $img->image_y = $height;
            }
            $img->file_new_name_body = $scaledFileNameBody;
            $img->jpeg_quality = 75;
            //$img->image_max_ratio = 1.96;
            $img->process($scaledPath);
            if (!$img->processed) { //если ресайзить не получилось, выдаём "нет картинки"
                return Helpers::getImageUrl('no_image.jpg', $width, $height);
            } else {
                return $scaledPath . $scaledFileName;
            }
        } else {
            return Helpers::getImageUrl('no_image.jpg', $width, $height);
        }
    }

    /**
     * Гарантирует, что строка будет не больше указанного количества символов.
     * В случае, если строка длиннее, подставляет в конец строки многоточие (...)
     * Длина строки в итоге получится length+3
     * @param string $string обрезаемая строка
     * @param int $length максимальная длина строки
     * @return string обрезанная строка, не длиннее length символов
     */
    public static function cutString($string, $length) {
        if (mb_strlen($string) > $length) {
            return mb_substr($string, 0, $length) . "...";
        } else
            return $string;
    }

    /**
     * Возвращает html-код всплывающей подсказки в виде вопросительного знака, наводя на который выдается текст подсказки
     * @param string $string текст подсказки, который показывать
     * @return string html-код подсказки, готовый для вставки на страницу
     */
    public static function htmlTooltip($string) {
        Yii::app()->clientScript->registerScriptFile('js/tooltips.js');
        return '<span class="question tooltip" title="' . $string . '">?</span>';
    }

    /**
     * Проверяет ИД на верность наисания (unsigned integer)
     * @param string $id
     * @return bool true, если верно, false если неверно
     */
    public static function checkId($id) {
        $regInteger = '/^\s*\d+\s*$/';
        return preg_match($regInteger, $id);
    }

    public static function requiredId($id) {
        if (!is_array($id))
            $id = array($id);
        foreach ($id as $val) {
            if (!Helpers::checkId($val))
                throw new CHttpException(400, 'Неверно указан ИД');
        }
    }

    /**
     * Смешивает значения 2-х массивов, гарантируя что все требуемые значения будут установлены.
     * Так можно задавать значения по умолчанию для функций
     * @param array $array1 массив, значения которого проверять на наличие
     * @param array $array2 массив, значения которого гарантированно должны быть и умолчания
     * @return array массив, содержащий гарантированный набор значений
     */
    public static function extend($array1, $array2) {
        foreach ($array2 as $id => $val) {
            if (!isset($array1[$id]))
                $array1[$id] = $val;
        }
        return $array1;
    }

    /**
     * Проверяет наличие всех нужных элементов в ассоциативном массиве
     * @param array $array Массив значений. Например, подойдет $_POST
     * @param array $keys ключи массива, которые должны присутствовать
     * @param array $throw вернуть исключение, если найдена ошибка
     * @return boolean true если все значения присутствуют, иначе false
     */
    public static function required($array, $keys, $throw = true) {
        if (!is_array($keys))
            $keys = array($keys);
        foreach ($keys as $val)
            if (!isset($array[$val])) {
                if ($throw)
                    throw new CHttpException(400, 'Не задан ключ ' . $val . ' в массиве');
                return false;
            }
        return true;
    }

    private static $odder = false;

    /**
     * При каждом запуске возвращает либо even либо odd по очереди
     * @return string even либо odd
     */
    public static function odder() {
        return (Helpers::$odder = !Helpers::$odder) ? 'odd' : 'even';
    }

    public static function price($val) {
        return number_format($val, 0, '', '&nbsp;') . 'р.';
    }

    /**
     * Переводит ошибки, полученные из валидатора в массив
     * @param array $arr
     */
    public static function errorsToText($arr) {
        $result = array();
        foreach ($arr as $error) {
            if (is_array($error)) {
                foreach ($error as $subError) {
                    $result[] = $subError;
                }
            } else
                $result[] = $subError;
        }
        return implode('<br>', $result);
    }
    
    public static function disableWebLogRoute(){
        if (YII_DEBUG && !empty(Yii::app()->log->routes['cweb']))
            Yii::app()->log->routes['cweb']->enabled=false;
    }
    
    public static function enableWebLogRoute(){
        if (YII_DEBUG && !empty(Yii::app()->log->routes['cweb']))
            Yii::app()->log->routes['cweb']->enabled=true;
    }

    /**
     * Вместо "Петров Александр Сергеевич" возвращает "Петров А.С."
     */
    public static function simplifyName($array, $spacer = " ") {
        if (is_string($array))
            $array = explode (" ", $array);
        
        $res = "";
        if ($array['lastname'])
            $res = $array['lastname'];
        else
            return $res;
        if ($array['name'])
            $res.= $spacer . mb_substr($array['name'], 0, 1) . ".";
        else
            return $res;
        if ($array['middlename'])
            $res.= mb_substr($array['middlename'], 0, 1) . ".";
        return $res;
    }

}

;
