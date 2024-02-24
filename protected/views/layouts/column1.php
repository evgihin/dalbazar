<?php
/* Фиксер на случай если система решит подгружать этот файл вместо main */
$this->beginContent('//layouts/main'); echo $content; $this->endContent();