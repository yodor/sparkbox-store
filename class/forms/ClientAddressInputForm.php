<?php
include_once("lib/forms/InputForm.php");
include_once("lib/input/DataInputFactory.php");


class ClientAddressInputForm extends InputForm
{

    public function __construct()
    {
        parent::__construct();

        $field = DataInputFactory::Create(DataInputFactory::TEXT, "city", "Град", 1);
        $this->addField($field);
        $field = DataInputFactory::Create(DataInputFactory::TEXT, "postcode", "Пощенски код", 1);
        $this->addField($field);
        $field = DataInputFactory::Create(DataInputFactory::TEXT, "address1", "Адрес (ред 1)", 1);
        $this->addField($field);
        $field = DataInputFactory::Create(DataInputFactory::TEXT, "address2", "Адрес (ред 2)", 0);
        $this->addField($field);

    }

    public function renderPlain()
    {
        echo "<div class='ClientAddressList'>";

        foreach ($this->getFields() as $index => $field) {
            echo "<div class='address_item'>";
            echo "<label>" . tr($field->getLabel()) . ": </label>";
            echo "<span>" . strip_tags(stripslashes($field->getValue())) . "</span>";
            echo "</div>";
        }

        echo "</div>";
    }
}

?>
