<?php

class ItemConfiguration {
  public $id = "items";
  public $name = "Item Configuration";
  public $icon = "fa fa-gear";
  public $position = "bottom";
  private $app = null;

  public function content() {
    $this->app = App::instance();

    echo '<h1>Item Configuration</h1>';

    if(Settings::loggedIn()) {
      if(!isset($_POST['search']) || !is_numeric($_POST['from'])) {
        echo '<p>Search for items. Enter the item ID to fill the local database with item names.</p>';
        echo '<form method="post">';
        echo '<label><span>From: </span><input id="from" name="from"></label>';
        echo '<label><span>To: </span><input id="to" name="to"></label>';
        echo '<label><span>&nbsp;</span><input type="submit" name="search"></label>';
        echo '</form>';
      } else {
        $this->search();
      }
    } else {
      echo Settings::loginForm($this->id);
    }
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