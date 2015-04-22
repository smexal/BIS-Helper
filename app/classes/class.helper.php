<?php

class Helper {

    public static function flyout($name, $action, $settings) {
        $content = '';
        $content.='<div class="flyout '.$name.'" data-content="'.$action.'" data-settings="'.htmlentities(json_encode($settings, JSON_HEX_QUOT | JSON_HEX_TAG)).'">';
        $content.='<a class="close"><i class="fa fa-times"></i></a>';
        $content.='<h3 class="title"></h3>';
        $content.='<div class="content"><div class="loading"><img src="images/loading.gif"></div></div>';
        $content.='</div>';
        return $content;
    }

    public static function updateSpecName($id, $newName, $player=null) {
        $db = DB::instance();
        if(! is_null($player) && $id == 0) {
            // we need to update special
            // insert new spec with name into spec table
            $insert = "INSERT INTO specs (player, name) VALUES (".$player.", '".$newName."')";
            $db->query($insert);
            $id = $db->lastId();
            $update = "UPDATE bis SET spec = ".$id." WHERE player =".$player;
            $db->query($update);
            // update all items from player to the new inserted spec.
        } else {
            // update name
            $update = "UPDATE specs SET name = '".$newName."' WHERE id =".$id;
            $db->query($update);
        }

    }

}


?>