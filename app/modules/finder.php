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

    if(isset($_GET['obtained'])) {
      $query = "UPDATE bis SET obtained = ".(isset($_GET['reset']) ? 0 : date("Ymd"))." WHERE item=".$_GET['obtained']." AND player = ".$_GET['player'];
      mysql_query($query) or die(mysql_error());
    }

    if(isset($_POST['go']) || isset($_GET['term'])) {
      $this->displayResults($term);
    }
  }

  public function displayResults($term) {

    $query = "SELECT * FROM items WHERE name LIKE '%".urlencode($term)."%'";
    $result = mysql_query($query) or die(mysql_errors());

    if(mysql_num_rows($result) > 0) {
      while($row = mysql_fetch_assoc($result)) {
        $found = false;
        echo '<div class="bis-block">';
        echo '<h2>'.Armory::formatItem(urldecode($row['name']), $row['id']).'</h2>';
        $query = "SELECT *, SUBSTRING_INDEX(SUBSTRING_INDEX(slot,'#', 2), '#',-1) as priority FROM bis WHERE item = ".$row['id']." order by priority asc";

        $bis_items = mysql_query($query);

        echo '<table class="finder">';
        while($item = mysql_fetch_assoc($bis_items)) {
          $found = true;
          if($item['obtained'] > 0) {
            $class = "class='obtained'";
          } else {
            $class="";
          }
          echo '<tr '.$class.'>';
          echo '<td width="20">';
          if($item['obtained'] == 0) {
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