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
<!-- favicon -->
<link rel="icon" type="image/png" sizes="32x32" href="images/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="images/favicon-16x16.png">
<!-- favicon end -->
<meta http-equiv="content-type" content="text/html; charset=utf-8">
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
<div class="page-loading"><img src="images/loading.gif"></div>
<?php
echo $app->navigation_panel();

echo '<div class="content">';
echo $app->content();
echo '</div>';



$app->shutdown();
?>

</body>
</html>