<?php
include_once("session.php");
include_once("class/pages/StorePage.php");

$page = new StorePage();
$page->startRender();
$page->setPreferredTitle("Условия за ползване");
echo "<div class='caption'>" . tr($page->getPreferredTitle()) . "</div>";
$page->finishRender();
?>