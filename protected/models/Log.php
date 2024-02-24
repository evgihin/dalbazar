<?php

/**
 * Логи
 */
class Log extends CExtFormModel {

    public static function add($path, $params = array(), $description = "", $userId = NULL) {

        $path = Log::path($path);
        $model = $path['model'];
        $action = $path['action'];
        $path = $path['path'];

        if (is_null($userId))
            $userId = Yii::app()->user->id;

        $params = serialize($params);

        Yii::app()->db->createCommand()
                ->insert("log", array(
                    "user_id" => $userId,
                    "model" => $model,
                    "action" => $action,
                    "description" => $description,
                    "path" => $path,
                    "params" => $params,
                    "time" => time()
        ));
        return Yii::app()->db->lastInsertID;
    }
    
    /**
     * Парсит путь и взвращает массив с полями path, action, model
     * @param type $path
     */
    public static function path($path){
        $path = array_reverse(explode("/", $path));
        $action = $model = array();
        if (isset($path[0]))
            $action = $path[0];
        if (isset($path[1]))
            $model = $path[1];
        if (count($path) > 2)
            $path = implode("/", array_slice($path, 2));
        else
            $path = "site";
        return array(
            "path" => $path,
            "model" => $model,
            "action" => $action
        );
    }
    
    public static function admin($path, $params = array(), $description = "", $userId = NULL){
        return Log::add("admin/".$path, $params, $description, $userId);
    }
    
    function get($path=NULL, $model=NULL, $action=NULL, $userId=NULL, $from=NULL, $to=NULL){
        $comm = $this->createCommand()
                ->select('*')
                ->where("1=1")
                ->leftJoin("user", "user.user_id=log.user_id")
                ->from("log");
        
        if ($path)
            $comm->where_and ("path=:p",array(":p"=>$path));
        if ($model)
            $comm->where_and ("model=:m",array(":m"=>$model));
        if ($action)
            $comm->where_and ("action=:a",array(":a"=>$action));
        if ($userId)
            $comm->where_and ("log.user_id=:u",array(":u"=>$userId));
        if ($from)
            $comm->where_and ("log.time>=:t_f",array(":t_f"=>$from));
        if ($to)
            $comm->where_and ("log.time<=:t_t",array(":t_t"=>$to));
        
        $comm->order("time DESC");
        
        $this->calculateCount($comm);
        
        return $comm->query();
    }
    
    public function getPathVariants(){
        return Yii::app()->db->createCommand()
                ->select("path")
                ->from("log")
                ->group("path")
                ->queryColumn();
    }
    
    public function getModelVariants(){
        return Yii::app()->db->createCommand()
                ->select("model")
                ->from("log")
                ->group("model")
                ->queryColumn();
    }
    
    public function getActionVariants(){
        return Yii::app()->db->createCommand()
                ->select("action")
                ->from("log")
                ->group("action")
                ->queryColumn();
    }
    
    public function getStatisticInterval($from, $to){
        $actions = Yii::app()->db->createCommand(
                "SELECT stat.*, log_description.description FROM (SELECT path,model,action,COUNT(*) AS count FROM log WHERE time>=:f AND time<=:t GROUP BY path,model,action) AS stat
LEFT JOIN log_description USING (path,model,action)")
                ->query(array(":f"=>$from,":t"=>$to));
        $res = array();
        foreach ($actions as $action){
            $res[implode("/", array($action["path"], $action["model"], $action["action"]))] = array(
                "description"=>$action['description'],
                "count"=>$action['count']);
        }
        return $res;
    }
    
    public function getGitCommits($count=0) {
        $dir = Yii::getPathOfAlias("webroot");
        $output = array();
        chdir($dir);
        if ($count)
            exec("git log -n".$count, $output);
        else
            exec("git log", $output);
        
        $history = array();
        foreach ($output as $line) {
            if (strpos($line, 'commit') === 0) {
                if (!empty($commit)) {
                    $commit['message'] = trim($commit['message']);
                    array_push($history, $commit);
                    unset($commit);
                }
                $commit['hash'] = substr($line, strlen('commit'));
            } else if (strpos($line, 'Author') === 0) {
                $commit['author'] = substr($line, strlen('Author:'));
            } else if (strpos($line, 'Date') === 0) {
                $commit['date'] = substr($line, strlen('Date:'));
            } else {
                if (isset($commit['message']))
                    $commit['message'] .= "\n". $line;
                else
                    $commit['message'] = $line;
            }
        }
        if (!empty($commit)) {
            array_push($history, $commit);
        }
        return $history;
    }

}
