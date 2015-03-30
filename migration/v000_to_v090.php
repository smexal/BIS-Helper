<?php
include("../config.php");
session_start();
include("../app/classes/class.database.php");
include("../app/classes/class.armory.php");
include("../app/modules/history.php");
include("../app/modules/member.php");
include("../app/modules/items.php");
include("../app/modules/finder.php");
include("../app/modules/settings.php");

if(Settings::loggedIn()) {
    $db = DB::instance();
    $obtained = $db->query("SELECT * FROM bis WHERE obtained <> 0");
    while($item = $db->row($obtained)) {
        $year = substr($item['obtained'], 0, 4);
        $month = substr($item['obtained'], 4, 2);
        $day = substr($item['obtained'], 6, 2);
        $datestring = "$year-$month-$day";

        echo "player" + $item['player']. " with item ".$item['item'].' adding to drop list<br />';
        $dropQuery = 'INSERT INTO drops (member, item, date, type)'.
                                " VALUES (".$item['player'].", ".$item['item'].", '".$datestring."', 1)";
        $db->query($dropQuery);
        $updateObtained = "UPDATE bis SET obtained = 0 WHERE player=".$item['player']." AND item=".$item['item'];
        $db->query($updateObtained);
        echo '<hr />';
    }
} else {
    echo 'log in to the tool to run this script.';
}

?>