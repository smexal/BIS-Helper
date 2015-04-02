<?php

class Dashboard {
  public $id = 'dashboard';
  public $name = 'Dashboard';
  public $icon = 'fa fa-home';
  public $path = array("Dashboard" => "?module=dashboard");
  public $actions = array();
  private $db = null;


  public function content() {
    $return = "";
    $return.= '<div class="half">';
    $return.= '<p>Welcome to the Guild Managing from '.GUILD.'. #saynomore_atm</p>';
    $return.= '</div>';
    return $return;
  } 
}

?>