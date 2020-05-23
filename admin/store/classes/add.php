<?php
include_once("session.php");
include_once("class/pages/AdminPage.php");
include_once("class/forms/ProductClassInputForm.php");
include_once("class/beans/ProductClassesBean.php");
include_once("class/beans/ProductsBean.php");

$menu = array();

$page = new AdminPage();


$view = new BeanFormEditor(new ProductClassesBean(), new ProductClassInputForm());

// $view->getTransactor()->assignInsertValue("insert_date", DBConnections::get()->dateTime());
// $view->getTransactor()->appendValue("prodID", $ensure_product->ref_id);

// $view->setCaption("Product Name: ".$ensure_product->ref_row["product_name"]);

$view->processInput();


$page->startRender();

$view->render();

$page->finishRender();

?>
