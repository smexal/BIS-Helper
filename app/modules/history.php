<?php

class LootHistory {
  public $id = 'history';
  public $name = 'Loot history';
  public $icon = 'fa fa-legal';

  public function content() {
    $app = App::instance();

    $raids = mysql_query("SELECT DISTINCT obtained FROM bis order by obtained desc");
    while($row = mysql_fetch_assoc($raids)) {
      echo '<div class="raid">';
      if($row['obtained'] == 0)
        continue;
      $year = substr($row['obtained'], 0, 4);
      $month = substr($row['obtained'], 4, 2);
      $day = substr($row['obtained'], 6, 2);
      echo '<h1>'.$year."/".$month."/".$day.'</h1>';
      echo '<div class="half">';
      echo '<h2>Best in Slot Items</h2>';
      $items = mysql_query("SELECT * FROM bis WHERE obtained = ".$row['obtained']);
      echo '<ul>';
      while($item = mysql_fetch_assoc($items)) {
        echo '<li>';
        echo urldecode(Armory::formatItem(Armory::getItemNameById($item['item']), $item['item']));
        echo ' <i class="fa fa-angle-double-right"></i> ';
        echo Armory::getPlayerNameById($item['player']);
        echo '</li>';
      }
      echo '</ul>';
      echo '</div>';
      echo '<div class="half">';

      echo '<h2>Other Drops</h2>';
      $drops = false;
      $items = mysql_query("SELECT * FROM drops WHERE DATE(date) = '".$year."-".$month."-".$day."'");
      echo '<ul>';
      while($item = mysql_fetch_assoc($items)) {
        $drops = true;
        echo '<li>';
        echo urldecode(Armory::formatItem(Armory::getItemNameById($item['item']), $item['item']));
        echo ' <i class="fa fa-angle-double-right"></i> ';
        echo Armory::getPlayerNameById($item['member']);
        echo '</li>';
      }
      if(!$drops) {
        echo 'No other drops on this day';
      }
      echo '</ul>';
      echo '</div>';
      echo '</div>';
    }
    
  } 
}

?>