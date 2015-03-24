<?

class Armory {
  public static $base = "https://eu.battle.net/api/wow/";
  public static $base_www = "https://eu.battle.net/wow/de/";
  public static $thumbnail_www = "https://eu.battle.net/static-render/eu/";
  public static $wowhead_heroic = 566;

  public static function getItem($itemId) {
    return json_decode(self::getUrlContent(self::$base."item/".$itemId."/raid-heroic"));
  }

  public static function formatItem($name="", $id=null) {
    $return = '';
    if(!is_null($id)) {
      $return.='<a href="http://wod.wowhead.com/item='.$id.'" rel="bonus='.self::$wowhead_heroic.'" target="_blank">';
    }
    $return .= '<span class="item epic">'.$name.'</span>';
    if(!is_null($id)) {
      $return.= '</a>';
    }
    return $return;
  }

  public static function addItemToDatabase($id) {
    $result = mysql_query("SELECT * FROM items WHERE id=".$id);
    $found = false;
    if($result) {
      if(mysql_num_rows($result) > 0) {
        echo '<p>Item already in database</p>';
        $found = true;
      }

      if(!$found) {
        $item = Armory::getItem($id);
        if($item) {
          $query = "INSERT INTO items (id, name) VALUES (".$id.", '".urlencode($item->name)."')";
          $result = mysql_query($query) or die(mysql_error());
          echo '<p>'.self::formatItem($item->name, $id)." added to the database.</p>";
        } else {
          echo '<p>Item not found.</p>';
        }
      }
    }
  }

  public static function getItemSelection($name, $item=null) {
    $result = mysql_query("SELECT * FROM items order by name asc");
    if($result) {
      $return = '<select name="'.$name.'">';
      $return.= "<option value='0'>no selection</option>";
      if(! is_null($item) && $item == 1) {
        $return.= "<option selected='selected' value='1'>not needed</option>";
      } else {
        $return.= "<option value='1'>not needed</option>";
      }
      while($row = mysql_fetch_assoc($result)) {
        if(!is_null($item) && $item == $row['id']) {
          $selected = ' selected="selected" ';
        } else {
          $selected = '';
        }
        $return.= "<option ".$selected." value='".$row['id']."'>".urldecode($row['name'])."</option>";
      }
      $return.= '</select>';
      return $return;

    } else {
      echo 'Error while quiering database.';
    }
  }

  public static function assignItemToPlayerView($item_id) {
    $return = '<div class="assign">';
    $return.='<h4 style="margin-bottom: 10px;">Assign to other Player</h4>';
    $return.='<select name="manual-selection" data-id="'.$item_id.'">';
    $return.='<option value="none">select a member</option>';
    foreach(self::getLocalMembers() as $id => $name) {
      $return.='<option value="'.$id.'">'.$name.'</option>';
    }
    $return.='</select>';
    $return.='<a href="javascript://" class="item-save-prev" style="margin-left: 4px; line-height: 20px; margin-right:10px">Save</a><span></span>';
    $return.='</div>';
    return $return;
  }

  public static function displayPlayerThumbnail($member) {
    echo '<img src="'.self::$thumbnail_www.$member->character->thumbnail.'" class="character-thumbnail" />';
  }

  public static function addMemberToDatabase($member) {
    $result = mysql_query("SELECT * FROM members WHERE name = '".utf8_decode($member->character->name)."'");
    $found = false;
    if($result) {
      if(mysql_num_rows($result) > 0) {
        $found = true;
      }
    }
    if(!$found) {
      $query = "INSERT INTO members (id, name, realm) VALUES (NULL, '".utf8_decode($member->character->name)."', '".mysql_real_escape_string(utf8_decode($member->character->realm))."')";
      mysql_query($query) or die(mysql_error());
    }
  }

  public static function getMemberId($character) {
    $result = mysql_query("SELECT id FROM members where name='".utf8_decode($character->name)."'");
    while($row = mysql_fetch_assoc($result)) {
      return $row['id'];
    }
  }

  public static function toDropList($item, $player) {
    mysql_query("INSERT INTO drops (member, item) VALUES ($player, $item)");
  }

  public static function getPlayerNameById($id) {
    $result = mysql_query("SELECT name, realm FROM members where id=".$id);
    while($row = mysql_fetch_assoc($result)) {
      return '<a href="'.self::$base_www.'character/'.strtolower($row['realm']).'/'.$row['name'].'/simple" target="_blank">'.$row['name'].' <i class="fa fa-external-link"></i></a>';
    }
  }

  public static function getItemIdByName($name) {
    $result = mysql_query("SELECT id FROM items where name='".urlencode($name)."'");
    while($row = mysql_fetch_assoc($result)) {
      return $row['id'];
    }  
  }

public static function getItemNameById($id) {
    $result = mysql_query("SELECT name FROM items where id=".$id);
    if($result) {
      while($row = mysql_fetch_assoc($result)) {
        return $row['name'];
      }  
    }
  }

  public static function displayPlayerRank($member) {
    $settings = new Settings();
    return $settings->ranks[$member->rank]['name'];
  } 

  public static function displayPlayerName($character) {
    return '<span class="class-'.$character->class.'">'.utf8_decode($character->name).'</span>';
  }
  public static function getLocalMembers() {
    $result = mysql_query("SELECT * FROM members");
    $members = array();
    while ($row = mysql_fetch_assoc($result)) {
      $members[$row['id']] = urldecode($row['name']);
    }
    return $members;
  }
  public static function getMembers() {
    return json_decode(
      self::getUrlContent(
        self::$base."guild/".REALM."/".str_replace(" ", "%20", GUILD)."?fields=members"
      )
    );
  }

  public static function getBisStatus($playerId) {
    $query = "SELECT COUNT(*) as count FROM bis where player = ".$playerId;
    $result = mysql_query($query);
    while($row = mysql_fetch_assoc($result)) {
      $return = $row['count'];
    }
    return (intval($return)/2)."/16";
  }


  private static function getUrlContent($url){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $data = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ($httpcode>=200 && $httpcode<300) ? $data : false;
  }

}




?>