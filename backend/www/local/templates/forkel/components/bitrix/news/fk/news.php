<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
// Списка коллекций как отдельной страницы в V4 нет: коллекции выбираются на главной и в панели «Каталог».
LocalRedirect('/catalog/');
