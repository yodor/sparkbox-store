<?php
include_once("forms/InputForm.php");


class AttributeInputForm extends InputForm
{

    public function __construct()
    {
        parent::__construct();

        $field = new DataInput("name", "Име на атрибут", 1);
        new TextField($field);
        $this->addInput($field);

        $field = new DataInput("unit", "Мярна единица", 0);
        new TextField($field);
        $this->addInput($field);
    }

}

?>
