<?php
error_reporting(E_ALL); ini_set('display_errors', 'on');
require_once("config.php");
if(DEBUG) {
  error_reporting(E_ALL); ini_set('display_errors', 'on');
}
require_once("app/app.php");


$app = App::instance();
$app->start();
?>

<html>
<head>
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-60834166-1', 'auto');
  ga('send', 'pageview');

</script>
<title>World of Warcraft - BiS Helper - Going H.A.M</title>
<?= $app->header(); ?>

</head>
<body>
<?php
echo $app->navigation_panel();

echo '<div class="content">';
echo $app->content();
echo '</div>';



$app->shutdown();
?>

</body>
</html>