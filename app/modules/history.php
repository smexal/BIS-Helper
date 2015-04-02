<?php

class LootHistory {
  public $id = 'history';
  public $name = 'Loot history';
  public $icon = 'fa fa-legal';
  public $path = array("History" => "?module=history");
  public $actions = array();
  private $db = null;

  public function content() {
    $content = "";
    if(is_null($this->db))
      $this->db = DB::instance();

    $app = App::instance();

    $raids = $this->db->query("SELECT DISTINCT DATE(date) as 'raid' FROM drops order by date desc");
    while($row = $this->db->row($raids)) {
      $content.= '<div class="raid">';
      $content.= '<h2>'.$row['raid'].'</h2>';
      $content.= '<div class="">';
      $drops = false;
      $items = $this->db->query("SELECT * FROM drops WHERE DATE(date) = '".$row['raid']."' order by type desc");
      $content.= '<ul>';
      while($item = $this->db->row($items)) {
        $drops = true;
        $content.= '<li>';
        $content.= '<span class="drop-type">';
        $content.= Armory::getDropType($item['type']);
        $content.= '</span>';
        $content.= '<span class="drop-item">';
        $content.= urldecode(Armory::formatItem(Armory::getItemNameById($item['item']), $item['item']));
        $content.= '</span>';
        $content.= '<span class="drop-arrow">';
        $content.= ' <i class="fa fa-angle-double-right"></i> ';
        $content.= '</span>';
        $content.= '<span class="drop-player">';
        $content.= Armory::getPlayerNameById($item['member']);
        $content.= '</span>';
        $content.= '</li>';
      }
      if(!$drops) {
        $content.= 'No other drops on this day';
      }
      $content.= '</ul>';
      $content.= '</div>';
      $content.= '</div>';
    }
    return $content;
  }
}

?>