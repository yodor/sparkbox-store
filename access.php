<?php
include_once("session.php");

include_once("pages/SparkPage.php");

$page = new SparkPage();

$page->startRender();

echo tr("Access to this resource is protected");

$page->finishRender();
//echo "Install Path: " . INSTALL_PATH;
//echo "<BR>";
//echo "Lib Path: " . LIB_PATH;
//echo "<BR>";
//echo "Site Root URL: " . LOCAL;
//echo "<BR>";
//echo "Lib Root URL: " . LIB_ROOT;
//echo "<BR>";
//echo "Cache Root: " . CACHE_PATH;
//echo "<BR>";
//echo ini_get("include_path");
//echo "<BR>";
//$defines->dump();
//
//phpinfo();

?>