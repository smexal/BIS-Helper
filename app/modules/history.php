<?php

class LootHistory {
  public $id = 'history';
  public $name = 'Loot history';
  public $icon = 'fa fa-legal';
  private $db = null;

  public function content() {
    if(is_null($this->db))
      $this->db = DB::instance();

    $app = App::instance();

    $raids = $this->db->query("SELECT DISTINCT DATE(date) as 'raid' FROM drops order by date desc");
    while($row = $this->db->row($raids)) {
      echo '<div class="raid">';
      echo '<h1>'.$row['raid'].'</h1>';
      echo '<div class="">';
      $drops = false;
      $items = $this->db->query("SELECT * FROM drops WHERE DATE(date) = '".$row['raid']."' order by type desc");
      echo '<ul>';
      while($item = $this->db->row($items)) {
        $drops = true;
        echo '<li>';
        echo '<span class="drop-type">';
        echo Armory::getDropType($item['type']);
        echo '</span>';
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