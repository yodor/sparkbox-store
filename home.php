<?php
include_once("session.php");
include_once("class/pages/StorePage.php");
include_once("class/beans/SectionsBean.php");
include_once("class/beans/SectionBannersBean.php");
include_once("class/utils/ProductsQuery.php");
include_once("class/components/renderers/items/ProductListItem.php");

function dumpCSS()
{
    echo '<link rel="stylesheet" href="'.SITE_ROOT.'css/ProductListItem.css" type="text/css" >';
}

$page = new StorePage();



$page->beginPage();

$section_banners = new SectionBannersBean();


$page->sections->startIterator("WHERE 1 ORDER BY position ASC");

$item = new ProductListItem();

$sel = new ProductsQuery();
$sel->order_by = " RAND() ";
$sel->group_by = " pi.prodID, pi.color ";
$sel->limit = "4";


$db = DBDriver::get();
while ($page->sections->fetchNext($section_row)) {
    $section = $section_row["section_title"];
    $secID = $section_row["secID"];
    echo "<div class='section $section'>";
        
        echo "<div class='caption'>$section</div>";
        
        
        $num = $section_banners->startIterator("WHERE secID='$secID' ORDER BY RAND() LIMIT 1", " sbID, caption, link, position ");
        
        if ($section_banners->fetchNext($banner_row)) {
            echo "<a class='banner' href='{$banner_row["link"]}'>";
            $img_href = SITE_ROOT."storage.php?cmd=gallery_photo&id={$banner_row["sbID"]}&class=SectionBannersBean";
            echo "<img width='100%' src='$img_href'>";
            echo "</a>";
        }
        
        
        echo "<div class='products'>";
            $sel->where = " p.section='$section' ";

//             echo $sel->getSQL();
            $res = $db->query($sel->getSQL());
            if (!$res) throw new Exception("Unable to query products from section '$section'. Error: ".$db->getError());
            
            while ($row = $db->fetch($res)) {
                $item->setItem($row);
                $item->render();
            }
            $db->free($res);
            
        echo "</div>";
        
    echo "</div>";
}

Session::set("shopping.list", $page->getPageURL());

$page->finishPage();

?>