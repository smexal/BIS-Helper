<?php
session_start();
include("classes/class.database.php");
include("classes/class.armory.php");
include("modules/history.php");
include("modules/member.php");
include("modules/items.php");
include("modules/finder.php");
include("modules/settings.php");

class App {
  private static $instance = null;
  private $modules = array();
  private $current_module = "finder";

  public function start() {

    $this->modules['finder'] = new ItemFinder();
    $this->modules['history'] = new LootHistory();
    $this->modules['member'] = new MemberConfiguration();
    $this->modules['items'] = new ItemConfiguration();
    $this->modules['settings'] = new Settings();

    if(isset($_GET['module'])) {
      $this->current_module = $_GET['module'];
    }
  }


  public function header() {
    /** 
      CSS FILES
    **/
    $css_data = array(
      "reset.css",
      "//fonts.googleapis.com/css?family=Roboto:400,100,100italic,300,300italic,400italic,500,500italic,700,700italic,900,900italic",
      "//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css",
      "main.css" 
    );
    $js_data = array(
      "//code.jquery.com/jquery-1.11.2.min.js",
      "externals/tablesort/jquery.tablesorter.min.js",
      "//static.wowhead.com/widgets/power.js",
      "main.js",
      "members.js"
    );

    $return = "";
    foreach ($css_data as $css) {
      if(strstr($css, "//")) {
        $return.= '<link rel="stylesheet" media="screen" href="'.$css.'">';
      } else {
        $return.= '<link rel="stylesheet" media="screen" href="'.DIR_CSS.$css.'">';
      }
    }
    foreach($js_data as $js) {
      if(strstr($js, "//")) {
        $return.= '<script src="'.$js.'"></script>';
      } else {
        $return.= '<script src="'.DIR_JS.$js.'"></script>';
      }
    }
    return $return;
  }

  public function navigation_panel() {
    $return = '<div class="navigation-panel">';
    $return.= '<div class="top">';
    $bottom_modules = array();
    foreach($this->modules as $module) {
      if(!is_null(@$module->position) && @$module->position = "bottom") {
        array_push($bottom_modules, $module);
      } else {
        $return.=$this->displayMenuItem($module);
      }
    }
    $return.='</div>'; // top
    $return.='<div class="bottom">';
    foreach($bottom_modules as $module) {
      $return.=$this->displayMenuItem($module);
    }
    $return.='</div>'; // bottom
    $return.='</div>';
    return $return;
  }

  private function displayMenuItem($module) {
    $return = '';
    if($this->current_module == $module->id) {
        $active = "active";
    } else {
      $active = "";
    }

    $return.= '<a href="?module='.$module->id.'" class="'.$active.'"><i class="'.$module->icon.'"></i></a>';
    return $return;
  }

  public function content() {
    if(array_key_exists($this->current_module, $this->modules)) {
      echo $this->modules[$this->current_module]->content();
    } else {
      $this->error("Module does not exist.");
    }

  }

  public function shutdown() {
    // TODO: close database connection
  }

  public static function error($message="Undefined error") {
    echo '<div class="error"><i class="fa fa-exclamation-triangle"></i><p>'.$message.'</p></div>';
  }

  public static function instance() {
    if(null === self::$instance) {
      self::$instance = new self;
    }
    return self::$instance;
  }
  private function __construct() {}
  private function __clone() {}

}

?>