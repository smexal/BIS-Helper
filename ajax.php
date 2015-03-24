<?php
error_reporting(E_ALL); ini_set('display_errors', 'on');
require_once("config.php");
if(DEBUG) {
  error_reporting(E_ALL); ini_set('display_errors', 'on');
}
require_once("app/app.php");


$app = App::instance();
$app->start();

if(isset($_POST['action'])) {
    if($_POST['action'] == 'addItemToPlayer') {
        echo AjaxMethods::addItemToPlayer($_POST['item'], $_POST['player']);
    }
}

class AjaxMethods {

    public static function addItemToPlayer($item, $player) {
        Armory::toDropList($item, $player);
        return "done";
    }

}




?>