<?php

class MemberConfiguration {
  public $id = "member";
  public $name = "Member Configuration";
  public $icon = "fa fa-group";
  public $slots = array("Weapon 1", "Weapon 2", "Head", "Neck", "Shoulder", "Back", "Chest", "Wrist", "Hands", "Waist", "Legs", "Feet", "Ring 1", "Ring 2", "Trinket 1", "Trinket 2");

  public function content() {
    $guild = Armory::getMembers();

    if(isset($_POST['update'])) {
      $this->updateBisList();
    }

    if(!isset($_GET['entry'])) {
      $this->memberList($guild);
    } else {
      $this->memberDetail($_GET['entry']);
    }
  }

  public function memberList($guild) {
    $settings = new Settings();
    echo '<h1>&lt;'.$guild->name.'&gt; Member Configuration</h1>';

    echo '<table class="members">';
    echo '<thead>';
    echo '<tr>';
    echo '<th></th><th>Name</th><th>Rank</th><th class="right">Status</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    foreach($guild->members as $member) {
      if($settings->ranks[$member->rank]['display'] != 1)
        continue;

      Armory::addMemberToDatabase($member);
      echo '<tr>';
      echo '<td class="member-image">';
      echo Armory::displayPlayerThumbnail($member);
      echo '</td>';
      echo '<td>';      
      echo '<a href="?module='.$this->id.'&entry='.Armory::getMemberId($member->character).'">';
      echo Armory::displayPlayerName($member->character);
      echo '</a>';
      echo '</td>';
      echo '<td>'.Armory::displayPlayerRank($member).'</td>';
      echo '<td class="right">';
      echo Armory::getBisStatus(Armory::getMemberId($member->character));
      echo '</td>';
      echo '</tr>';
    }
    echo '</tbody>';
    echo '</table>';
  }

  public function memberDetail($player) {
    $name = Armory::getPlayerNameById($player);
    echo '<h1>'.$name.' <small>Best in Slot</h1>';
    echo '<form method="post">';
    echo '<input type="submit" name="update" value="Save update" />';
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
        echo '<a class="btn" href="?module='.$this->id.'&entry='.$_GET['entry'].'&unlock"><i class="fa fa-unlock"></i></a>';
      } else {
        echo '<a class="btn"  href="?module='.$this->id.'&entry='.$_GET['entry'].'&lock"><i class="fa fa-lock"></i></a>';
      }
    }
    echo '<table class="bis">';
    echo '<tr>';
    echo '<th class="slot">Slot</th>';
    echo '<th>BiS #1</th>';
    echo '<th>BiS #2</th>';
    echo '</tr>';

    foreach($this->slots as $slot) {
      echo '<tr>';
      echo '<th class="slot">'.$slot.'</th>';
      $oneObtained = false;
      for($prio = 1; $prio <= 2; $prio++) {
        $query = "SELECT * FROM bis WHERE player = ".$_GET['entry']." AND slot = '".str_replace(" ", "-", $slot)."#".$prio."'";
        $result = mysql_query($query);
        $selected = null;
        $obtained = false;
        if(mysql_num_rows($result) > 0) {
          while($row = mysql_fetch_assoc($result)) {
            $selected = $row['item'];
            if($row['obtained'] != 0) {
              $obtained = $row['obtained'];
              if($prio == 1) {
                $oneObtained = true;
              }
            }
          }
        }
        if($obtained || ($prio == 2 && $oneObtained) || $locked) {
          if($prio == 2 && $oneObtained) {
            echo '<td>-</td>';
          } else {
            echo '<td>'.urldecode(Armory::formatItem(Armory::getItemNameById($selected), $selected)).'</td>';
          }
        } else {
          echo '<td>'.Armory::getItemSelection(str_replace(" ", "-", $slot)."#".$prio, $selected).'</td>';
        }
        
      }
      echo '</tr>';
    }
    echo '</table>';
    echo '</form>';


    echo '<a class="back-link" href="?module='.$this->id.'">back</a>';
  }

  public function updateBisList() {
    foreach($this->slots as $slot) {
      $slot = str_replace(" ", "-", $slot);
      for($prio = 1; $prio <= 2; $prio++) {
        $cur_slot = $slot."#".$prio;
        if(isset($_POST[$cur_slot])) {
          $query = "SELECT * FROM bis WHERE slot = '".$cur_slot."' AND player = ".$_GET['entry'];
          $result = mysql_query($query);
          if(mysql_num_rows($result) > 0) {
            // update
            $query = "UPDATE bis SET item = ".$_POST[$cur_slot]." WHERE player = ".$_GET['entry']." AND slot ='".$cur_slot."'";
            mysql_query($query) or die(mysql_error());
          } else {
            // insert
            if($_POST[$cur_slot] != 0) {
              mysql_query("INSERT INTO bis (id, player, slot, item) VALUES (NULL, '".$_GET['entry']."', '".$cur_slot."', ".$_POST[$cur_slot].")");
            }
          }
        }
      }
    }
  }
}

?>