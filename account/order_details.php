<?php
include_once("session.php");
include_once("class/pages/AccountPage.php");
include_once("class/components/renderers/cells/OrderDeliveryCellRenderer.php");
include_once("class/components/renderers/cells/OrderInvoiceCellRenderer.php");
include_once("class/components/renderers/cells/OrderItemsCellRenderer.php");
include_once("components/TableView.php");
include_once("components/renderers/cells/DateCellRenderer.php");
include_once("iterators/SQLQuery.php");
include_once("beans/UsersBean.php");
include_once("class/beans/ClientAddressesBean.php");
include_once("class/beans/EkontAddressesBean.php");
include_once("class/beans/InvoiceDetailsBean.php");
include_once("class/beans/OrderItemsBean.php");
include_once("class/beans/OrdersBean.php");
include_once("class/utils/Cart.php");

$page = new AccountPage();

$ekont_addresses = new EkontAddressesBean();
$client_addresses = new ClientAddressesBean();
$invoices = new InvoiceDetailsBean();
$items = new OrderItemsBean();
$orders = new OrdersBean();

$clients = new UsersBean();

$sel = new SQLSelect();

$userID = $page->getUserID();
$orderID = -1;

$order = NULL;

if (isset($_GET["orderID"])) {
    $orderID = (int)$_GET["orderID"];
}
$qry = $orders->query();
$qry->select->where()->add("orderID", $orderID)->add("userID", $userID);

$qry->select->limit = " 1 ";
$num = $qry->exec();

if ($num < 1) {
    Session::set("alert", "Няма достъп до тази поръчка");
    header("Location: orders.php");
    exit;
}

$order = $qry->next();

$page->startRender();

$page->setTitle(tr("Детайли за поръчка"));

echo "<div class='caption'>" . $page->getTitle() . "</div>";

echo "<div class='group'>";

echo "<div class='item order_num'>";
echo "<label>" . tr("Номер на поръчка") . "</label>";
echo "<span>" . $orderID . "</span>";
echo "</div>";

echo "<div class='item order_date'>";
echo "<label>" . tr("Дата") . "</label>";
echo "<span>" . $order["order_date"] . "</span>";
echo "</div>";

echo "<div class='item delivery_type'>";
echo "<label>" . tr("Начин на доставка") . "</label>";
echo "<span>" . Cart::getDeliveryTypeText($order["delivery_type"]) . "</span>";
echo "</div>";

echo "<div class='item delivery_type'>";
echo "<label>" . tr("Адрес за доставка") . "</label>";
echo "<span>" . $order["delivery_address"] . "</span>";
echo "</div>";

echo "<div class='item require_invoice'>";
echo "<label>" . tr("Фактуриране") . "</label>";
echo "<span>" . (($order["require_invoice"] > 0) ? tr("Да") : tr("Не")) . "</span>";
echo "</div>";

echo "<div class='item status'>";
echo "<label>" . tr("Статус") . "</label>";
echo "<span>" . tr($order["status"]) . "</span>";
echo "</div>";

echo "</div>"; //group

echo "<div class='order_items'>";

echo "<div class='line'>";
echo "<span>" . tr("Поз.") . "</span>";
echo "<span></span>";
echo "<span>" . tr("Продукт") . "</span>";
echo "<span>" . tr("Количество") . "</span>";
echo "<span>" . tr("Ед.цена") . "</span>";
echo "<span>" . tr("Сума") . "</span>";
echo "</div>";

$qry = $items->queryField("orderID", $orderID);
$qry->select->fields()->set("piID", "prodID", "itemID", "price", "qty", "product");
$numItems = $qry->exec();

$pos = 0;
while ($item = $qry->next()) {
    $pos++;
    echo "<div class='line'>";
    echo "<div class='item pos'>$pos</div>";

    $piID = $item["piID"];
    $prodID = $item["prodID"];

    echo "<a class='item photo' href='" . LOCAL . "/details.php?prodID=$prodID&piID=$piID'>";
    echo StorageItem::Image($item["itemID"], get_class($items), 100, 100);
    echo "</a>";

    echo "<div class='item product'>";

    $details = explode("//", $item["product"]);
    foreach ($details as $index => $value) {
        $data = explode("||", $value);
        echo $data[0] . ": " . $data[1] . "<BR>";
    }
    echo "</div>";
    echo "<div class='item qty'>" . $item["qty"] . "</div>";
    echo "<div class='item price'>" . sprintf("%0.2f лв.", $item["price"]) . "</div>";
    echo "<div class='item amount'>" . sprintf("%0.2f лв.", ($item["qty"] * $item["price"])) . "</div>";
    echo "</div>";
}
echo "</div>";

echo "<div class='group'>";
echo "<div class='item products_total'>";
echo "<label>" . tr("Продукти общо") . "</label>";
echo "<span>" . formatPrice($order["total"] - $order["delivery_price"]) . "</span>";
echo "</div>";
echo "<div class='item delivey_price'>";
echo "<label>" . tr("Доставка") . "</label>";
echo "<span>" . formatPrice($order["delivery_price"]) . "</span>";
echo "</div>";
echo "<div class='item order_total'>";
echo "<label>" . tr("Поръчка общо") . "</label>";
echo "<span>" . formatPrice($order["total"]) . "</span>";
echo "</div>";
echo "</div>";

$page->finishRender();
?>
