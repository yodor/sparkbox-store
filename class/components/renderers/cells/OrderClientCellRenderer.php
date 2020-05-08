<?php
include_once("components/renderers/cells/TableCellRenderer.php");

class OrderClientCellRenderer extends TableCellRenderer
{
    protected $userID = -1;

    protected function renderImpl()
    {
        global $clients;

        $client = $clients->getByID($this->userID, false, "fullname , email, phone");

        echo "<div class='group client_data'>";
        echo "<div class='item fullname'>";
        echo "<label>" . tr("Име") . "</label>";
        echo "<span>" . $client["fullname"] . "</span>";
        echo "</div>";
        echo "<div class='item email'>";
        echo "<label>" . tr("E-Mail") . "</label>";
        echo "<span>" . $client["email"] . "</span>";
        echo "</div>";
        echo "<div class='item phone'>";
        echo "<label>" . tr("Телефон") . "</label>";
        echo "<span>" . $client["phone"] . "</span>";
        echo "</div>";
        echo "</div>";
    }

    public function setData(array &$row)
    {

        parent::setData($row);

        $this->userID = (int)$row["userID"];

    }

}

?>
