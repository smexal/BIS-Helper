<?php

class MemberConfiguration {
  public $id = "member";
  public $name = "Member Configuration";
  public $icon = "fa fa-group";
  public $path = array(GUILD => "?module=member");
  public $actions = array();
  public $slots = array("Weapon 1", "Weapon 2", "Head", "Neck", "Shoulder", "Back", "Chest", "Wrist", "Hands", "Waist", "Legs", "Feet", "Ring 1", "Ring 2", "Trinket 1", "Trinket 2");
  private $db = null;

  public function content() {
    $content = "";
    if(is_null($this->db)) {
      $this->db = DB::instance();
    }
    $guild = Armory::getMembers();

    if(isset($_POST['Weapon-1#1'])) {
      $this->updateBisList();
    }

    if(is_null($guild)) {
      // try again:
      $guild = Armory::getMembers();
      if(is_null($guild)) {
        $content.= "Guild not found";
      }
      return;
    }

    if(!isset($_GET['entry'])) {
      $content.= $this->memberList($guild);
    } else {
      // update specname
      if(isset($_POST['newName'])) {
        Helper::updateSpecName($_POST['id'], $_POST['name'], $_POST['player']);
      }


      $content.= $this->memberDetail($_GET['entry']);
    }
    return $content;
  }

  public function memberList($guild) {
    $content = '';
    $settings = new Settings();
    $content.= '<table class="members">';
    $content.= '<thead>';
    $content.= '<tr>';
    $content.= '<th></th><th>Name</th><th>Rank</th><th class="right">Status</th>';
    $content.= '</tr>';
    $content.= '</thead>';
    $content.= '<tbody>';
    foreach($guild->members as $member) { 
      if($settings->ranks[$member->rank]['display'] != 1)
        continue;

      Armory::addMemberToDatabase($member);
      $content.= '<tr>';
      $content.= '<td class="member-image">';
      $content.= Armory::displayPlayerThumbnail($member);
      $content.= '</td>';
      $content.= '<td>';      
      $content.= '<a href="?module='.$this->id.'&entry='.Armory::getMemberId($member->character).'">';
      $content.= Armory::displayPlayerName($member->character);
      $content.= '</a>';
      $content.= '</td>';
      $content.= '<td>'.Armory::displayPlayerRank($member).'</td>';
      $content.= '<td class="right">';
      $content.= Armory::getBisStatus(Armory::getMemberId($member->character));
      $content.= '</td>';
      $content.= '</tr>';
    }
    $content.= '</tbody>';
    $content.= '</table>';
    return $content;
  }

  public function memberDetail($player) {
    $content = '';
    $name = Armory::getPlayerNameById($player);
    $this->name = Armory::getPlayerNameById($player, 'raw');
    $this->path = array(GUILD => "?module=member", $this->name => "?module=member&entry=".$player);

    $this->actions['Save current list'] = "javascript:sendForm('#bislist')";
    $locked = Settings::isLocked($player);
    if(Settings::loggedIn()) {
      // unlock player
      if(isset($_GET['unlock'])) {
        Settings::unlockPlayer($player);
      } elseif(isset($_GET['lock'])) {
        Settings::lockPlayer($player);
      }
      $locked = Settings::isLocked($player);

      if($locked) {
        $this->actions['<i class="fa fa-unlock"></i>'] = '?module='.$this->id.'&entry='.$_GET['entry'].'&unlock';
      } else {
        $this->actions['<i class="fa fa-lock"></i>'] = '?module='.$this->id.'&entry='.$_GET['entry'].'&lock';
      }
    }

    // get all specs for this player
    $content.= $this->getPlayerSpecs();
    // end spec changes

    $content.= '<form method="post" id="bislist">';
    $content.= '<table class="bis">';
    $content.= '<tr>';
    $content.= '<th class="slot">Slot</th>';
    $content.= '<th>BiS #1</th>';
    $content.= '<th>BiS #2</th>';
    $content.= '</tr>';

    foreach($this->slots as $slot) {
      $content.= '<tr>';
      $content.= '<th class="slot">'.$slot.'</th>';
      $oneObtained = false;
      for($prio = 1; $prio <= 2; $prio++) {
        $query = "SELECT * FROM bis WHERE player = ".$_GET['entry']." AND slot = '".str_replace(" ", "-", $slot)."#".$prio."'";
        $result = $this->db->query($query);
        $selected = null;
        $obtained = false;
        if($this->db->count($result) > 0) {
          while($row = $this->db->row($result)) {
            $selected = $row['item'];
            $obtained_query = "SELECT * FROM drops WHERE member = ".$_GET['entry']." AND item =".$selected;
            $obtained_result = $this->db->query($obtained_query);
            if($this->db->count($obtained_result) > 0) {
              $obtained = true;
              if($prio == 1) {
                $oneObtained = true;
              }
            }
          }
        }
        if($obtained || ($prio == 2 && $oneObtained) || $locked) {
          if($prio == 2 && $oneObtained) {
            $content.= '<td>-</td>';
          } else {
            $obtained_add = "";
            if($locked && $obtained) {
              $obtained_add = '<i class="left-space light fa fa-check"></i>';
            }
            $content.= '<td>'.urldecode(Armory::formatItem(Armory::getItemNameById($selected), $selected)).$obtained_add.'</td>';
          }
        } else {
          $content.= '<td>'.Armory::getItemSelection(str_replace(" ", "-", $slot)."#".$prio, $selected).'</td>';
        }
        
      }
      $content.= '</tr>';
    }
    $content.= '</table>';
    $content.= '</form>';
    return $content;
  }

  private function getPlayerSpecs() {
    $content = '';
    $specs = $this->db->query("SELECT DISTINCT spec FROM bis WHERE player=".$_GET['entry']);
    if(!isset($_GET['spec'])) {
      $active_spec = "first";
    }
    $content.='<div class="sub-menu">';
    $count = 0;
    while($spec = $this->db->row($specs)) {
      if($active_spec == "first" && $count == 0) {
        $active = "active";
      } else {
        $active = "";
      }
      $id = 0;
      $real_name = "Untitled Spec";
      $thequery = "SELECT id, name FROM specs WHERE player=".$_GET['entry']." AND id=".$spec['spec'];
      $spec_name = $this->db->query($thequery);
      if($this->db->count($spec_name) === 0) {
        $content.='<a href="#" class="'.$active.'">'.$real_name.'</a>';
      } else {
        while($name = $this->db->row($spec_name)) {
          $id = $name['id'];
          $real_name = $name['name'];
          $content.='<a href="#" class="'.$active.'">'.$name['name'].'</a>';
        }
      }
      // edit link
      $content.='<a href="javascript://" class="show-flyout" data-target="editSpec"><i class="fa fa-pencil-square"></i></a>';
      $content.= Helper::flyout("editSpec", "editSpec", array("id" => $id, "player" => $_GET['entry'], "oldname" => $real_name));
      // edit link end
      $count++;
    }
    $content.="</div>";
    return $content;
  }

  public function updateBisList() {
    foreach($this->slots as $slot) {
      $slot = str_replace(" ", "-", $slot);
      for($prio = 1; $prio <= 2; $prio++) {
        $cur_slot = $slot."#".$prio;
        if(isset($_POST[$cur_slot])) {
          $query = "SELECT * FROM bis WHERE slot = '".$cur_slot."' AND player = ".$_GET['entry'];
          $result = $this->db->query($query);
          if($this->db->count($result) > 0) {
            // update
            $query = "UPDATE bis SET item = ".$_POST[$cur_slot]." WHERE player = ".$_GET['entry']." AND slot ='".$cur_slot."'";
            $this->db->query($query);
          } else {
            // insert
            if($_POST[$cur_slot] != 0) {
              $this->db->query("INSERT INTO bis (id, player, slot, item) VALUES (NULL, '".$_GET['entry']."', '".$cur_slot."', ".$_POST[$cur_slot].")");
            }
          }
        }
      }
    }
  }
}

?>