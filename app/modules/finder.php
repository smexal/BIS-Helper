<?php

class ItemFinder {
  public $id = "finder";
  public $name = "Item Finder";
  public $icon = "fa fa-search";

  public function content() {
    echo '<h1>Item Finder</h1>';
    echo '<p>Type the name, of the item you want to assign<br /> to a person.</p>';

    echo '<form method="post">';
    if(isset($_POST['term'])) {
      $term = $_POST['term'];
    } else {
      if(isset($_GET['term'])) {
        $term = $_GET['term'];
      } else {
        $term = '';
      }
    }
    echo '<input type="text" name="term" value="'.$term.'" />';
    echo '<input type="submit" name="go" value="Search" />';
    echo '</form>';
    if(Settings::loggedIn()) {
       $db = DB::instance();
      if(isset($_GET['obtained'])) {
        if(!isset($_GET['reset'])) {
          // add new item
          $query = "INSERT INTO drops (member, item, type) VALUES (".$_GET['player'].", ".$_GET['obtained'].", 1)";
          $db->query($query);
        } else {
          // remove existing item
          // only removes from today allowed here
          $query = "DELETE FROM drops WHERE member =".$_GET['player']." AND item=".$_GET['obtained']." AND DATE(date) LIKE '".date('Y-m-d')."'";
          $db->query($query);
        }
      }
    } else {
      if(isset($_GET['obtained']) || isset($_GET['reset'])) {
        App::error("Log In ma' freind or yoo no doin' da vodoo!");
      }
    }

    if(isset($_POST['go']) || isset($_GET['term'])) {
      $this->displayResults($term);
    }
  }

  public function displayResults($term) {
    $db = DB::instance();
    $query = "SELECT * FROM items WHERE name LIKE '%".urlencode($term)."%'";
    $result = $db->query($query);

    if($db->count($result) > 0) {
      while($row = $db->row($result)) {
        $found = false;
        echo '<div class="bis-block">';
        echo '<h2>'.Armory::formatItem(urldecode($row['name']), $row['id']).'</h2>';
        $query = "SELECT *, SUBSTRING_INDEX(SUBSTRING_INDEX(slot,'#', 2), '#',-1) as priority FROM bis WHERE item = ".$row['id']." order by priority asc";
        $bis_items = $db->query($query);

        echo '<table class="finder">';
        while($item = $db->row($bis_items)) {
          $found = true;
          if(Armory::itemReceived($item['player'], $item['item'])) {
            $class = "class='obtained'";
          } else {
            $class="";
          }
          echo '<tr '.$class.'>';
          echo '<td width="20">';
          if(! Armory::itemReceived($item['player'], $item['item'])) {
            echo '<a href="?module='.$this->id.'&term='.$term.'&obtained='.$row['id'].'&player='.$item['player'].'">';
            echo '<i class="fa fa-check"></i>';
            echo '</a>';
          } else {
            echo '<a href="?module='.$this->id.'&term='.$term.'&obtained='.$row['id'].'&player='.$item['player'].'&reset=true">';
            echo '<i class="fa fa-close"></i>';
            echo '</a>';
          }
          echo '</td>';
          echo '<td>';
          echo Armory::getPlayerNameById($item['player']);
          echo '</td><td class="right">';
          echo '#'.$item['priority'];
          echo '</td>';
          echo '</tr>';
        }
        echo '</table>';
        if(!$found) {
          echo '<p>No one wants this item!</p>';
        }
        echo Armory::assignItemToPlayerView($row['id']);

        echo '</div>';
      }

    } else {
      echo '<h2>No items found for "<i>'.$term.'</i>".</h2>';
    }

  }
}

?>