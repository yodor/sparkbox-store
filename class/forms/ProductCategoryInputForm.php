<?php
include_once("forms/InputForm.php");
include_once("class/beans/ProductCategoriesBean.php");
include_once("class/beans/AttributesBean.php");
include_once("class/beans/ClassAttributesBean.php");
include_once("input/ArrayDataInput.php");

class ProductCategoryInputForm extends InputForm
{


    public function __construct()
    {
        $field = new DataInput("category_name", "Име на категория", 1);
        $field->setRenderer(new TextField());
        $this->addInput($field);

        $field = new DataInput("parentID", "Родителска категория", 1);
        $pcats = new ProductCategoriesBean();

        $rend = new NestedSelectField();
        $rend->setIterator($pcats->query());
        $rend->list_key = "catID";
        $rend->list_label = "category_name";
        $rend->na_str = '--- TOP ---';
        $rend->na_val = "0";

        $field->setRenderer($rend);
        $this->addInput($field);

        $field = DataInputFactory::Create(DataInputFactory::SESSION_IMAGE, "photo", "Снимка", 0);
        $this->addInput($field);

        // 	  $field1 = new ArrayInputField("maID", "Attribute", 0);
        // 	  $field1->allow_dynamic_addition=true;
        // 	  $field1->setSource(new ClassAttributesBean());
        //
        // 	  $attribs = new AttributesBean();
        //
        // 	  $rend = new SelectField();
        // 	  $rend->setSource($attribs);
        // 	  $rend->list_key="maID";
        // 	  $rend->list_label="name";
        //
        // 	  $field1->setValidator(new EmptyValueValidator());
        //
        // 	  $field1->setRenderer($rend);
        // 	  $this->addField($field1);


        $this->getInput("category_name")->enableTranslator(true);
    }

}

?>
