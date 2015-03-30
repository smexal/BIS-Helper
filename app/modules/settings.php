<?php

class Settings {
  public $id = "settings";
  public $name = "Configuratin & Settings";
  public $icon = "fa fa-lock";
  public $position = "bottom";
  private $versions = array(
    "v0.9.0" => "all drops in general droplist, added some improvements to the database structure, switch to 'mysqli'.",
    "v0.0.0" => "initial version with basic features"
  );
  private $db = null;

  // global settings not
  public $ranks = null;
  public $password = null;

  public function __construct() {
    if(is_null($this->db))
      $this->db = DB::instance();
    $settings_result = $this->db->query("SELECT * FROM config");
    while($setting = $this->db->row($settings_result)) {
      switch ($setting['key']) {
        case 'password':
          $this->password = $setting['value'];
          break;
        default:
          break;
      }
    }
    $rankresult = $this->db->query("SELECT * FROM ranks");
    $this->ranks = array();
    while($row = $this->db->row($rankresult)) {
      $this->ranks[$row['id']]['name'] = $row['rank'];
      $this->ranks[$row['id']]['display'] = $row['display'];
    }
  }

  public function content() {
    echo '<h1>Preferences & Configuration</h1>';    
  	if(isset($_POST['Login'])) {
        if($_POST['password'] == $this->password) {
            if(isset($_POST['camefrom'])) {
              echo '<meta http-equiv="refresh" content="0; url=?module='.$_POST['camefrom'].'">';
            }
            $_SESSION['auth'] = true;
  		}
  	}
    if(isset($_GET['logout'])) {
        $_SESSION['auth'] = false;
    }

  	if(self::loggedIn()) {
      if(isset($_GET['lock'])) {
        $this->db->query("UPDATE members SET locked = 1");
      } elseif(isset($_GET['unlock'])) {
        $this->db->query("UPDATE members SET locked = 0");
      }


      if(!isset($_GET['configure'])) {
        echo '<div class="half">';
          echo '<ul>';
          echo '<li><a href="?module='.$this->id.'&configure=general">General Settings</a></li>';
          echo '<li><a href="?module='.$this->id.'&configure=member">Member Level Configuration</li>';
          echo '<li><a href="?module='.$this->id.'&logout">Logout</a></li>';
          echo '</ul>';
        echo '</div>';
        echo '<div class="half">';
        echo '<h3>Quick Tools</h3>';
        $this->unlockLockAllPlayersView();
        echo '</div>';
      } else {
          switch ($_GET['configure']) {
              case 'general':
                  $this->configureGeneral();
                  break;
              case 'member':
                  $this->configureMember();
              default:
                  break;
          }
      }
  	} else {
        echo self::loginForm();
  	}
  }

  public function configureMember() {
    if(isset($_POST['update'])) {
      foreach($_POST as $key => $value) {
        if($key == 'update')
          continue;

        $rank = explode("-", $key);
        $type = $rank[0];
        $rank = $rank[1];

        if($type == 'rank') {
          $query = "SELECT * FROM ranks WHERE id=".$rank;
          if($this->db->count($this->db->query($query)) > 0) {
            $this->db->query("UPDATE ranks SET rank = '".$value."', display=0 WHERE id=".$rank);
          } else {
            $this->db->query("INSERT INTO ranks (id, rank) VALUES (".$rank.", '".$value."')");
          }
          $this->ranks[$rank]['name'] = $value;
          $this->ranks[$rank]['display'] = 0;
        } else if($type=='display') {
          $query = "SELECT * FROM ranks WHERE id=".$rank;
          $display = "0";
          if(isset($_POST['display-'.$rank])) {
            $display = "1";
          }
          if($this->db->count($this->db->query($query)) > 0) {
            $this->db->query("UPDATE ranks SET display = '".$display."' WHERE id=".$rank);
            $this->ranks[$rank]['display'] = 1;
          } else {
            $this->db->query("INSERT INTO ranks (id, rank) VALUES (".$rank.", '".$value."')");
          }
        }
      }
    }
    echo '<form method="post">';
    echo '<label><span>&nbsp;</span><input type="submit" name="update" value="Save changes" /></label>';
    $guild = Armory::getMembers();
    $ranks = array();
    foreach($guild->members as $member) {
      if(!in_array($member->rank, $ranks)) {
        array_push($ranks, $member->rank);
      }
    }
    asort($ranks);
    foreach ($ranks as $rank) {
      echo '<label><span>Rank '.$rank.'</span><input type="text" value="'.$this->ranks[$rank]['name'].'" name="rank-'.$rank.'" /></label>';
      $checked = '';
      if($this->ranks[$rank]['display'] == 1) {
        $checked = "checked='checked'";
      }
      echo '<label><span>Display:</span><input type="checkbox" '.$checked.' name="display-'.$rank.'" /></label>';
      echo '<hr />';
    }


    echo '</form>';
  }

  public function configureGeneral() {
    if(isset($_POST['save'])) {
      foreach($_POST as $key => $value) {
        if($key != "save") {
          if($value != '') {
            $query = "SELECT * FROM config WHERE config.key='".$key."'";
            $result = $this->db->query($query);
            if($this->db->count($result) > 0) {
              $query = "UPDATE config SET value='".$value."' WHERE config.key = '".$key."'";
              $this->db->query($query);
            } else {
              $query = "INSERT INTO config (config.key, value) VALUES ('".$key."', '".$value."')";
              $this->db->query($query);
            }
            $this->$key = $value;
          }
        }
      }
    }
    echo '<h2>Password Change</h2>';
    echo '<form method="post">';
    echo '<label><span>&nbsp;</span><input type="submit" name="save" value="Save changes" /></label>';
    echo '<label><span>Admin Password</span><input type="text" value="'.$this->password.'" name="password" />';
    echo '</form>';

    echo '<a class="back-link" href="?module='.$this->id.'">back</a>';
    echo '<hr style="margin-top: 80px" />';
    echo '<h2>Version Overview</h2>';
    echo '<table class="finder">';
    foreach($this->versions as $version => $changes) {
      echo '<tr>';
      echo '<th>'.$version.'</th>';
      echo '<td>'.$changes.'</td>';
      echo '</tr>';
    }
    echo '</table>';
  }

  public function unlockLockAllPlayersView() {
    echo '<a class="btn half quicktool" href="?module='.$this->id.'&lock"><i class="fa fa-lock"></i> Lock all Players</a>';
    echo '<a class="btn half quicktool" href="?module='.$this->id.'&unlock"><i class="fa fa-unlock"></i> Unlock all Players</a>';
  }

  public static function isLocked($playerID) {
    $db = DB::instance();
    $query = "SELECT locked FROM members WHERE id=".$playerID;
    $result = $db->query($query);
    while($row = $db->row($result)) {
      return $row['locked'];
    }
  }
  public static function unlockPlayer($playerID) {
    $db = DB::instance();
    $db->query("UPDATE members SET locked = 0 WHERE id=".$playerID);
  }
  public static function lockPlayer($playerID) {
    $db = DB::instance();
    $db->query("UPDATE members SET locked = 1 WHERE id=".$playerID);
  }  

  public static function loggedIn() {
    if(isset($_SESSION['auth']) && $_SESSION['auth'] === true)
        return true;
    return false;
  }  

  public static function loginForm($camefrom=false) {
    $self = new self;
    $return ='<h2>Plattform Login</h2>';
    $return.='<form method="post" action="?module='.$self->id.'">';
    if($camefrom) {
        $return.='<input type="hidden" name="camefrom" value="'.$camefrom.'">';
    }
    $return.='<label><span>Password:</span><input type="password" name="password" /></label>';
    $return.='<label><span>&nbsp;</span><input type="submit" name="Login" value="Login" /></label>';
    $return.='</form>';
    return $return;
  }
}

?>