<?php
include_once("components/renderers/items/DataIteratorItem.php");
include_once("storage/StorageItem.php");
include_once("class/beans/ProductColorPhotosBean.php");

class ProductListItem extends DataIteratorItem implements IHeadContents
{

    protected $colors = NULL;

    protected $photo = NULL;

    protected $sel = NULL;

    public function __construct()
    {
        parent::__construct();

        $sel = new SQLSelect();

        $sel->from = " product_colors pc JOIN store_colors sc ON sc.color=pc.color  LEFT JOIN product_inventory pi ON pi.prodID=pc.prodID AND pi.color=pc.color";

        $sel->fields()->set("pi.piID", "pc.pclrID", "pc.color", "pc.prodID", "sc.color_code");
        $sel->fields()->setExpression("(SELECT pclrpID FROM product_color_photos pcp WHERE pcp.pclrID=pc.pclrID ORDER BY position ASC LIMIT 1)", "pclrpID");
        $sel->fields()->setExpression("(SELECT ppID FROM product_photos pp WHERE pp.prodID=pc.prodID ORDER BY position ASC LIMIT 1)", "ppID");
        $sel->fields()->setExpression("(color_photo IS NOT NULL)", "have_chip");

        $this->sel = $sel;

        $this->photo = new StorageItem();
    }

    public function requiredStyle() : array
    {
        $arr = parent::requiredStyle();
        $arr[] = LOCAL . "/css/ProductListItem.css";
        return $arr;
    }

    public function setData(array &$item)
    {
        parent::setData($item);
        $this->setAttribute("prodID", $this->data["prodID"]);
        $this->setAttribute("piID", $this->data["piID"]);

        if ($this->data["color_ids"]) {
            $colors = explode("|", $this->data["color_ids"]);
            if (count($colors) > 0) {

                $this->colors = $colors;
            }

        }
        //         var_dump($this->colors);

        if (isset($item["pclrpID"]) && $item["pclrpID"] > 0) {

            $this->photo->id = (int)$item["pclrpID"];
            $this->photo->className = "ProductColorPhotosBean";//ProductColorPhotosBean::class;
        }
        else if (isset($item["ppID"]) && $item["ppID"] > 0) {

            $this->photo->id = (int)$item["ppID"];
            $this->photo->className = "ProductPhotosBean";//ProductPhotosBean::class;
        }

        //$this->sel->where = " pc.prodID = {$item["prodID"]} ";
    }

    protected function renderImpl()
    {
        //      var_dump($this->item);
        // 	print_r(array_keys($this->item));
        // 	echo "<HR>";
        echo "<div class='wrap'>";

        // 	cho $this->sel->getSQL();

        $product_href = LOCAL . "/details.php?prodID={$this->data["prodID"]}";
        $item_href = LOCAL . "/details.php?prodID={$this->data["prodID"]}&piID=";

        $item_href_main = $item_href . $this->data["piID"];
        echo "<a href='$item_href_main' class='product_link'>";
        if ($this->photo) {
            $img_href = $this->photo->hrefThumb(275, 275);
            echo "<img src='$img_href'>";
        }
        echo "</a>";

        echo "<div class='product_detail'>";

        echo "<div class='colors_container'>";

        $num_colors = is_array($this->colors) ? count($this->colors) : 0;
        if ($num_colors > 0) {

            echo "<div class='colors'>" . $num_colors . " " . ($num_colors > 1 ? tr("цвята") : tr("цвят")) . "</div>";

            echo "<div class='color_chips'>";

            $db = DBConnections::get();

            foreach ($this->colors as $idx => $pclrID) {

                $this->sel->where()->add("pc.prodID", $this->data["prodID"])->add("pc.pclrID", $pclrID);

                //echo $this->sel->getSQL();

                $res = $db->query($this->sel->getSQL());
                if (!$res) throw new Exception($db->getError());

                $chip_class = "";
                $chip_id = -1;
                $use_color_code = FALSE;

                if ($prow = $db->fetch($res)) {

                    //use color chip if any
                    if ($prow["have_chip"] > 0) {
                        $chip_class = "ProductColorsBean&field=color_photo";
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
                            $use_color_code = TRUE;
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
                        $href = StorageItem::Image($chip_id, $chip_class, 48, 48);

                        echo "<img src='$href' >";
                    }

                    echo "</a>";
                }//fetch

            } //foreach color
            echo "</div>"; //color_chips

        }
        echo "</div>"; //colors_container

        echo "<a class='product_name' href='$item_href_main' >" . $this->data["product_name"] . "</a>";
        //echo "<div class='stock_amount'><label>".tr("Наличност").": </label>".$this->item["stock_amount"]."</div>";

        echo "<div class='sell_price'>";

        echo "<div class='item_price'>" . sprintf("%1.2f", $this->data["sell_price"]) . " " . tr("лв.") . "</div>";

        if ($this->data["price_min"] != $this->data["sell_price"] || $this->data["price_max"] != $this->data["sell_price"]) {
            echo "<div class='series_price'>" . sprintf("%1.2f", $this->data["price_min"]) . " " . tr("лв.") . " - " . sprintf("%1.2f", $this->data["price_max"]) . " " . tr("лв.") . "</div>";
        }

        echo "</div>";

        echo "</div>"; //product_details

        echo "</div>"; //wrap

    }

    public function renderSeparator($idx_curr, $items_total)
    {

    }
}

?>
