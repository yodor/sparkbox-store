<?php
// include_once("class/beans/SellableProductsBean.php");
include_once("lib/components/renderers/items/ItemRendererImpl.php");
include_once("lib/utils/StorageItem.php");
include_once("class/beans/ProductColorPhotosBean.php");

class ProductListItem extends ItemRendererImpl
{


    protected $colors = NULL;

    protected $photo = NULL;

    protected $sel = NULL;


    public function __construct()
    {
        parent::__construct();


        // 		$this->addClassName("clearfix");

        $sel = new SelectQuery();

        $sel->from = " product_colors pc JOIN store_colors sc ON sc.color=pc.color  LEFT JOIN product_inventory pi ON pi.prodID=pc.prodID AND pi.color=pc.color";


        $sel->fields = " pi.piID, pc.pclrID, pc.color, pc.prodID, sc.color_code,

    (SELECT pclrpID FROM product_color_photos pcp WHERE pcp.pclrID=pc.pclrID ORDER BY position ASC LIMIT 1) as pclrpID,
    (SELECT ppID FROM product_photos pp WHERE pp.prodID=pc.prodID ORDER BY position ASC LIMIT 1) as ppID,
    (color_photo IS NOT NULL) as have_chip ";

        $this->sel = $sel;


    }

    public function requiredStyle()
    {
        $arr = parent::requiredStyle();
        $arr[] = SITE_ROOT . "css/ProductListItem.css";
        return $arr;
    }

    public function setItem($item)
    {
        parent::setItem($item);
        $this->setAttribute("prodID", $this->item["prodID"]);
        $this->setAttribute("piID", $this->item["piID"]);


        if ($this->item["color_ids"]) {
            $colors = explode("|", $this->item["color_ids"]);
            if (count($colors) > 0) {

                $this->colors = $colors;
            }

        }

        $photo = NULL;
        if (isset($item["pclrpID"]) && $item["pclrpID"] > 0) {
            $photo = new StorageItem();
            $photo->itemID = (int)$item["pclrpID"];
            $photo->itemClass = "ProductColorPhotosBean";//ProductColorPhotosBean::class;
        }
        else if (isset($item["ppID"]) && $item["ppID"] > 0) {
            $photo = new StorageItem();
            $photo->itemID = (int)$item["ppID"];
            $photo->itemClass = "ProductPhotosBean";//ProductPhotosBean::class;
        }
        if ($photo) {
            $this->photo = $photo;
        }


        // 		$this->sel->where = " pc.prodID = {$item["prodID"]} ";
    }

    protected function renderImpl()
    {
        // 		//var_dump($this->item);
        // 		print_r(array_keys($this->item));
        // 		echo "<HR>";
        echo "<div class='wrap'>";

        // 		echo $this->sel->getSQL();

        $product_href = SITE_ROOT . "details.php?prodID={$this->item["prodID"]}";
        $item_href = SITE_ROOT . "details.php?prodID={$this->item["prodID"]}&piID=";

        $item_href_main = $item_href . $this->item["piID"];
        echo "<a href='$item_href_main' class='product_link'>";
        if ($this->photo) {
            $img_href = $this->photo->hrefThumb(275, 275);
            echo "<img src='$img_href'>";
        }
        echo "</a>";

        echo "<div class='product_detail'>";


        echo "<div class='colors_container'>";

        $num_colors = count($this->colors);
        if ($num_colors > 0) {

            echo "<div class='colors'>" . $num_colors . " " . ($num_colors > 1 ? tr("цвята") : tr("цвят")) . "</div>";

            echo "<div class='color_chips'>";

            $db = DBDriver::Get();

            foreach ($this->colors as $idx => $pclrID) {

                $this->sel->where = " pc.prodID='{$this->item["prodID"]}' AND pc.pclrID='$pclrID' ";
                // 				  echo $this->sel->getSQL();

                $res = $db->query($this->sel->getSQL());
                if (!$res) throw new Exception($db->getError());

                $chip_class = "";
                $chip_id = -1;
                $use_color_code = false;

                if ($prow = $db->fetch($res)) {

                    //use color chip if any
                    if ($prow["have_chip"] > 0) {
                        $chip_class = "ProductColorsBean&bean_field=color_photo";
                        $chip_id = $pclrID;
                    }
                    //use the product photo if no color photo is set
                    else if ($prow["pclrpID"] < 1 && $prow["ppID"] > 0) {
                        $chip_class = "ProductPhotosBean";
                        $chip_id = $prow["ppID"];
                    }
                    else {
                        $chip_class = "ProductColorPhotosBean";
                        $chip_id = $prow["pclrpID"];
                        if ((int)$chip_id == 0) {
                            $use_color_code = true;
                        }
                    }

                    $item_href_color = $item_href . $prow["piID"];
                    $color_code = $prow["color_code"];
                    echo "<a href='$item_href_color' class='item' color_code='$color_code' title='{$prow["color"]}'>";
                    if ($use_color_code) {
                        $color_code = $prow["color_code"];
                        echo "<div class='color_code' style='background-color:$color_code;width:48px;height:48px;' title='{$prow["color"]}'></div>";
                    }
                    else {
                        $href = STORAGE_HREF . "?cmd=image_thumb&width=48&height=48&class=$chip_class&id=$chip_id";

                        echo "<img src='$href' >";
                    }

                    echo "</a>";
                }//fetch

            } //foreach color
            echo "</div>"; //color_chips

        }
        echo "</div>"; //colors_container


        echo "<a class='product_name' href='$item_href_main' >" . $this->item["product_name"] . "</a>";
        // 			echo "<div class='stock_amount'><label>".tr("Наличност").": </label>".$this->item["stock_amount"]."</div>";


        echo "<div class='sell_price'>";

        echo "<div class='item_price'>" . sprintf("%1.2f", $this->item["sell_price"]) . " " . tr("лв.") . "</div>";

        if ($this->item["price_min"] != $this->item["sell_price"] || $this->item["price_max"] != $this->item["sell_price"]) {
            echo "<div class='series_price'>" . sprintf("%1.2f", $this->item["price_min"]) . " " . tr("лв.") . " - " . sprintf("%1.2f", $this->item["price_max"]) . " " . tr("лв.") . "</div>";
        }
        echo "</div>";

        echo "</div>"; //product_details


        echo "</div>";

    }

    public function renderSeparator($idx_curr, $items_total)
    {

    }
}

?>
