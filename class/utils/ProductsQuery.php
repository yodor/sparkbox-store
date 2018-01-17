<?php
include_once("lib/utils/SelectQuery.php");

class ProductsQuery extends SelectQuery
{
    public function __construct()
    {
        parent::__construct();
        
        // 		(SELECT GROUP_CONCAT(DISTINCT(CONCAT(ca.attribute_name,':', cast(iav.value as char))) SEPARATOR '|') FROM product_inventory pi4 JOIN inventory_attribute_values iav ON iav.piID=pi4.piID JOIN class_attributes ca ON ca.caID = iav.caID WHERE pi4.prodID=pi.prodID AND pi4.pclrID=pi.pclrID) as inventory_attributes_all, 
        
        $this->fields = "
		iav.value as ia_value, ca.attribute_name as ia_name,
		(pclrs.color_photo IS NOT NULL) as have_chip, pc.catID, pc.category_name, 
(SELECT GROUP_CONCAT(DISTINCT(pi1.size_value) SEPARATOR '|') FROM product_inventory pi1 WHERE pi1.prodID=pi.prodID AND (pi1.pclrID = pi.pclrID OR pi.pclrID IS NULL) GROUP BY pi.pclrID ) as size_values, 
(SELECT GROUP_CONCAT(DISTINCT(pi2.color) SEPARATOR '|') FROM product_inventory pi2 WHERE pi2.prodID=pi.prodID ORDER BY pclrID ASC ) as colors, 

(SELECT GROUP_CONCAT(DISTINCT(pi3.pclrID) SEPARATOR '|') FROM product_inventory pi3 WHERE pi3.prodID=pi.prodID ORDER BY pclrID ASC ) as color_ids, 


(SELECT sc.color_code FROM store_colors sc WHERE sc.color = pi.color) as color_code,

(SELECT GROUP_CONCAT(DISTINCT(CONCAT(ca.attribute_name,':', cast(iav.value as char))) SEPARATOR '|') FROM inventory_attribute_values iav JOIN class_attributes ca ON ca.caID = iav.caID WHERE iav.piID = pi.piID) as inventory_attributes, 

(SELECT ppID FROM product_photos pp WHERE pp.prodID=pi.prodID ORDER BY position ASC LIMIT 1) as ppID,

(SELECT pclrpID FROM product_color_photos pcp WHERE pcp.pclrID=pi.pclrID ORDER BY position ASC LIMIT 1) as pclrpID,

pi.price - (pi.price * (coalesce(sp.discount_percent,0)) / 100.0) AS sell_price, 

coalesce(sp.discount_percent,0) as discount_percent,

pi.piID, pi.size_value, pi.color, pi.pclrID, pi.prodID, pi.stock_amount,

p.product_code, p.product_name, p.brand_name, p.product_summary, p.product_description, p.keywords,
p.promotion, p.visible, p.class_name, p.section, p.old_price, p.insert_date, p.update_date
";
		$this->from = " product_inventory pi 

JOIN products p ON (p.prodID = pi.prodID AND p.visible=1) 
JOIN product_categories pc ON pc.catID=p.catID 
LEFT JOIN store_promos sp ON (sp.targetID = p.catID AND sp.target='Category' AND sp.start_date <= NOW() AND sp.end_date >= NOW()) 
LEFT JOIN product_colors pclrs ON pclrs.pclrID = pi.pclrID
LEFT JOIN inventory_attribute_values iav ON iav.piID=pi.piID 
LEFT JOIN class_attributes ca ON ca.caID=iav.caID

";
		$this->where = "";
    }
}
?>