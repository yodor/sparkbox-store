<?php
include_once("session.php");
include_once("templates/admin/GalleryViewPage.php");

include_once("beans/DynamicPagePhotosBean.php");
include_once("beans/DynamicPagesBean.php");


$rc = new BeanKeyCondition(new DynamicPagesBean(), "../list.php", array("item_title"));

$bean = new DynamicPagePhotosBean();
$bean->select()->where()->addURLParameter($rc->getURLParameter());

$cmp = new GalleryViewPage();
$cmp->setBean($bean);
$cmp->getPage()->setName(tr("Photo Gallery").": " . $rc->getData("item_title"));
$cmp->getPage()->setAccessibleTitle($cmp->getPage()->getName());

$cmp->setBean($bean);

$cmp->render();

?>