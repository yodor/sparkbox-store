<?php
include_once("session.php");
include_once("class/pages/AdminPage.php");
include_once("class/forms/AttributeInputForm.php");
include_once("class/beans/AttributesBean.php");


$page = new AdminPage();


$view = new BeanFormEditor(new AttributesBean(), new AttributeInputForm());

$view->processInput();

$page->startRender();

$view->render();

$page->finishRender();

?>
