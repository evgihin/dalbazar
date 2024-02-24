<?php

class AdminstatisticCommand extends CConsoleCommand{
    function actionIndex(){
        //получаем ИДы подписанных чуваков
        $emails = Yii::app()->db->createCommand()
                ->select("user.email")
                ->from("statistic_subscription")
                ->leftJoin("user", "user.user_id=statistic_subscription.user_id")
                ->where("user.email IS NOT NULL")
                ->queryColumn();
        
        //получаем статистику
        $cLog = new Log();
        //за день, неделю, месяц
        $day = $cLog->getStatisticInterval(time()-86400, time());
        $week = $cLog->getStatisticInterval(time()-86400*7, time());
        $month = $cLog->getStatisticInterval(time()-86400*30, time());
        
        $cEmail = Yii::app()->email;
        $cEmail->layout='blank';
        $cEmail->to = $emails;
        $cEmail->subject = "Ежедневная рассылка статистики";
        $cEmail->view = "adminstatistic";
        $cEmail->send(array(
            "month"=>$month,
            "week"=>$week,
            "day"=>$day
            ));
    }
}

