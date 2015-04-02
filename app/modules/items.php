<?php

class ItemConfiguration {
  public $id = "items";
  public $name = "Item Configuration";
  public $icon = "fa fa-gear";
  public $position = "bottom";
  public $path = array("Items" => "?module=items");
  public $actions = array();
  private $app = null;

  public function content() {
    $content = '';
    $this->app = App::instance();

    if(Settings::loggedIn()) {
      if(!isset($_POST['search']) || !is_numeric($_POST['from'])) {
        $content.= '<p>Search for items. Enter the item ID to fill the local database with item names.</p>';
        $content.= '<form method="post">';
        $content.= '<label><span>From: </span><input id="from" name="from"></label>';
        $content.= '<label><span>To: </span><input id="to" name="to"></label>';
        $content.= '<label><span>&nbsp;</span><input type="submit" name="search"></label>';
        $content.= '</form>';
      } else {
        $this->search();
      }
    } else {
      $content.= Settings::loginForm($this->id);
    }
    return $content;
  }

  public function search() {
    $from = false; 
    $to = false; 


    $from = $_POST['from'];
    if(isset($_POST['to'])) {
      $to = $_POST['to'];
    }
    if(!$to) {
      // single request
      Armory::addItemToDatabase($from);

    } else {
      // multiple requests
      for($index = $from; $index <= $to; $index++) {
        Armory::addItemToDatabase($index);
      }

    }
    echo '<a class="back-link" href="?module='.$this->id.'">back</a>';

  }

}

?>