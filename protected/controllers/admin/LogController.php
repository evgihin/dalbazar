<?php

class LogController extends CAdminController{
    
    function actionList($path=NULL, $model=NULL, $action=NULL, $user_id=NULL, $from=NULL, $to=NULL){
        $cLog = new Log();
        $pages = new CPagination(999999);
        $pages->pageSize=50;
        $pages->applyLimit($cLog);
        $items = $cLog->get($path, $model, $action, $user_id, $from, $to);
        $pages->setItemCount($cLog->count());
        
        $cUser = new User();
        $admins = $cUser->get("user", "admin_level>0");
        $userVariants = array();
        foreach ($admins as $admin){
            $userVariants[$admin['user_id']] = $admin['login']." (".Helpers::simplifyName($admin).")";
        }
        
        $pathVariants = $cLog->getPathVariants();
        $modelVariants = $cLog->getModelVariants();
        $actionVariants = $cLog->getActionVariants();
        $pathVariants = array_combine($pathVariants, $pathVariants);
        $modelVariants = array_combine($modelVariants, $modelVariants);
        $actionVariants = array_combine($actionVariants, $actionVariants);
        $this->render("list",array(
            "pages" => $pages,
            "items" => $items,
            "path"=> $path,
            "model" => $model,
            "action" => $action,
            "userId" => $user_id,
            "from" => $from,
            "to" => $to,
            
            "pathVariants" => $pathVariants,
            "modelVariants" => $modelVariants,
            "actionVariants" => $actionVariants,
            "userVariants" => $userVariants
        ));
    }
    
    public function actionGit() {
        $cLog = new Log;
        $this->render("git",array(
            "log" => $cLog->getGitCommits()
        ));
    }
}

