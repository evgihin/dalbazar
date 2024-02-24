<?php
/* @var $this Controller */
//Yii::app()->clientScript->registerScriptFile('js/jquery-1.8.0.min.js');
Yii::app()->clientScript->registerCssFile('css/main.css');
$rightBlock = !is_null($this->right);
$rightBlock = false;
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="language" content="ru" />
        <base href="<?= Yii::app()->request->getBaseUrl(true) ?>">
        <link rel="yandex-tableau-widget" href="/site/yandexTableau/" />
        <link rel="icon" type="image/vnd.microsoft.icon" href="favicon.ico" />
        <script src="js/jquery-1.8.2.min.js"></script>
        <script src="js/jquery-userplugins.js"></script>
        <title><?php echo CHtml::encode($this->pageTitle); ?></title>
        <script src="js/jquery.cookie.js"></script>
        <?php if (!YII_DEBUG) { ?>
            <script>
                var msg = "Внимание!\n" +
                        "Вы открываете тестовую версию сайта Dalbazar.ru.\n" +
                        "Продолжить использование тестовой версии?\n" +
                        "нажимая \"да\" вы соглашаетесь с тем, что администрация сайта не несет ответственности за корректность исполнения платных услуг.\n" +
                        "Так же, мы не гарантируем безопасности хранения ваших паролей в этой версии сайта и не несем ответственности за сохранность объявлений.\n" +
                        "Все зарегистрированные учетные записи и объявления могут в любой момент быть безвозвратно удалены без предупреждения.\n" +
                        "Для улучшения качества предоставляемых услуг просим Вас сообщать администрации о всех найденных ошибках.\n" +
                        "Ссылка находится в верхнем меню и выделена красным цветом.\n" +
                        "Нажимая \"нет\" вы попадете на старую версию сайта.";
                if ($.cookie('showAlert') != "true")
                    if (confirm(msg)) {
                        $.cookie('showAlert', 'true', {path: '/'});
                    } else {
                        window.location.href = "http://dalbazar.ru";
                    }
            </script>
        <?php } ?>
    </head>
    <body>
        <div id="floatingTop">
            <div id="topmenu" <?= ($rightBlock) ? 'class="with_right"' : "" ?>>
                <div id="go_up" title="Наверх страницы"></div>
                <div id="mainmenu">
                    <?php $this->widget('application.widget.UDbMenu'); ?>
                </div>

                <?php //Кнопка открытия панели пользователя ?>
                <div class="userpanel_btn">
                    <span class="onmain inactive">Войти</span>
                    <div class="userpanel_cogwheel"></div>
                </div>

                <?php //Кнопка добавления объявления ?>
                <div class="addBtn">
                    <a href="<?= $this->createUrl('advert/add') ?>" class="inactive onmain">Добавить объявление</a>
                    <a href="<?= $this->createUrl('advert/add') ?>"><div class="addBtn_plus"></div></a>
                </div>
                <script>$(".userpanel_btn").dropdown("#userpanel");</script>
            </div>
        </div>

        <?php if ($rightBlock) {  //правый блок страницы ?>
            <div id="floatingRight">
                <div id="rightBegunContainer">
                    <div>
                        <div id="rightBegun">
                            <?php
                            $this->widget("application.widget.URightBegun", array("items" => $this->right, "class" => 'my', "descriptionLength" => 100));
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>

        <div id="main" <?= ($rightBlock) ? 'class="with_right"' : "" ?>>
            <div id="header">
                <div id="logo"><a href="/"><img src="images/theme/logo2.png" width="219" height="69"></a></div>
                <div class="city"><?php $this->widget("application.widget.UCityChanger") ?></div>
                <div id="searchbar">
                    <form action="<?= $this->createUrl("search/result") ?>" method="GET">
                        <input type="text" autocomplete="off" value="Быстрый поиск..." id="searchfield" class="shadowed radiused10" name="q">
                        <div id="searchbtn"><input type="submit" value="&nbsp;"></div>
                    </form>
                    <script>
                        var initVal = $("#searchfield").val();
                        $("#searchfield").focusin(function(){
                            if ($(this).val()==initVal)
                                $(this).val("");
                        }).focusout(function(){
                            if ($(this).val()=="")
                                $(this).val(initVal);
                        });
                    </script>
                    <div class="searchAdvise">
                        Пример: <a href="#" class="onmain inactive">Тушенка президентская</a>, <a href="#" class="onmain inactive">audi A6 со студенческой скидкой</a>
                    </div>
                </div>

                <?php $this->widget('application.widget.ULoginPanel'); ?>
            </div><!-- header -->

            <?php if (isset($this->left)): ?>
                <div id="sidebar">
                    <?php echo $this->left; ?>
                </div>
            <?php endif; ?>

            <div id="article" <?php if (isset($this->left)) echo 'class="withLeft"'; ?> >
                <?php
                if (isset($this->articleRight) && $this->articleRight != ""):
                    ?>
                    <div class="right"><?= $this->articleRight ?></div>
                    <?php
                endif;

                if (isset($this->breadcrumbs) && count($this->breadcrumbs) > 0):
                    $this->widget('zii.widgets.CBreadcrumbs', array(
                        'links' => $this->breadcrumbs,
                    ));
                // breadcrumbs
                endif;
                ?>
            </div>
            <div id="section" <?php if (isset($this->left)) echo 'class="withLeft"'; ?> >

                <?php echo $content; ?>

            </div>
            <?php if (isset($this->left)): ?>
                <div class="clear"></div>
            <?php endif; ?>


            <div id="footer">
                &copy; <?php echo date('Y'); ?> Дальбазар.<br/>
                Все права защищены.<br/>
            </div><!-- footer -->
        </div><!-- page -->
        <div id="loader"><div><img src="images/theme/loader.gif" width="64px" height="64px"></div></div>
        <script>
            $('#loader').ajaxSend(function() {
                $(this).show();
            }).ajaxStop(function() {
                $(this).hide();
            });
        </script>
        <?php if (YII_DEBUG && false) { ?>
            <script type="text/javascript" src="_xprecise/xprecise.min.js"></script>
        <?php } ?>
        <?php if (!YII_DEBUG) { ?>
            <!-- Yandex.Metrika counter -->
            <script type="text/javascript">
            (function (d, w, c) {
                (w[c] = w[c] || []).push(function() {
                    try {
                        w.yaCounter24495746 = new Ya.Metrika({id:24495746,
                                webvisor:true,
                                clickmap:true,
                                trackLinks:true,
                                accurateTrackBounce:true});
                    } catch(e) { }
                });

                var n = d.getElementsByTagName("script")[0],
                    s = d.createElement("script"),
                    f = function () { n.parentNode.insertBefore(s, n); };
                s.type = "text/javascript";
                s.async = true;
                s.src = (d.location.protocol == "https:" ? "https:" : "http:") + "//mc.yandex.ru/metrika/watch.js";

                if (w.opera == "[object Opera]") {
                    d.addEventListener("DOMContentLoaded", f, false);
                } else { f(); }
            })(document, window, "yandex_metrika_callbacks");
            </script>
            <noscript><div><img src="//mc.yandex.ru/watch/24495746" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
            <!-- /Yandex.Metrika counter -->
        <?php } ?>
    </body>
</html>
