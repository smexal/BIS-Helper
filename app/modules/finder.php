<?php

class ItemFinder {
  public $id = "finder";
  public $name = "Item Finder";
  public $icon = "fa fa-search";
  public $path = Array("Finder" => "?module=finder");
  public $actions = array();

  public function content() {
    $content = '';
    $content.= '<p>Type the name, of the item you want to assign to a person.</p>';

    $content.= '<form method="post">';
    if(isset($_POST['term'])) {
      $term = $_POST['term'];
    } else {
      if(isset($_GET['term'])) {
        $term = $_GET['term'];
      } else {
        $term = '';
      }
    }
    $content.= '<input type="text" name="term" value="'.$term.'" />';
    $content.= '<input type="submit" name="go" value="Search" />';
    $content.= '</form>';
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
      $content.= $this->displayResults($term);
    }
    return $content;
  }

  public function displayResults($term) {
    $content = '';
    $db = DB::instance();
    $query = "SELECT * FROM items WHERE name LIKE '%".urlencode($term)."%'";
    $result = $db->query($query);

    if($db->count($result) > 0) {
      $count = 0;
      while($row = $db->row($result)) {
        if($count % 3 == 0 && $count > 0) {
          $last = " last";
        } else {
          $last = "";
        }
        $count++;
        $found = false;
        $content.= '<div class="bis-block '.$last.'">';
        $content.= '<h2>'.Armory::formatItem(urldecode($row['name']), $row['id']).'</h2>';
        $query = "SELECT *, SUBSTRING_INDEX(SUBSTRING_INDEX(slot,'#', 2), '#',-1) as priority FROM bis WHERE item = ".$row['id']." order by priority asc";
        $bis_items = $db->query($query);

        $content.= '<table class="finder">';
        while($item = $db->row($bis_items)) {
          $found = true;
          if(Armory::itemReceived($item['player'], $item['item'])) {
            $class = "class='obtained'";
          } else {
            $class="";
          }
          $content.= '<tr '.$class.'>';
          $content.= '<td width="20">';
          if(! Armory::itemReceived($item['player'], $item['item'])) {
            if(Settings::loggedIn()) {
              $content.= '<a href="?module='.$this->id.'&term='.$term.'&obtained='.$row['id'].'&player='.$item['player'].'">';
            }
            $content.= '<i class="fa fa-close"></i>';
            if(Settings::loggedIn()) {
              $content.= '</a>';
            }
          } else {
            if(Settings::loggedIn()) {
              $content.= '<a href="?module='.$this->id.'&term='.$term.'&obtained='.$row['id'].'&player='.$item['player'].'&reset=true">';
            }
            $content.= '<i class="fa fa-check"></i>';
            if(Settings::loggedIn()) {
              $content.= '</a>';
            }
          }
          $content.= '</td>';
          $content.= '<td>';
          $content.= Armory::getPlayerNameById($item['player']);
          $content.= '</td><td class="right">';
          $content.= '#'.$item['priority'];
          $content.= '</td>';
          $content.= '</tr>';
        }
        $content.= '</table>';
        if(!$found) {
          $content.= '<p>No one wants this item!</p>';
        }
        if(Settings::loggedIn()) {
          $content.= Armory::assignItemToPlayerView($row['id']);
        }
        $content.= '</div>';
      }

    } else {
      $content.= '<h2>No items found for "<i>'.$term.'</i>".</h2>';
    }
    return $content;
  }
}

?>