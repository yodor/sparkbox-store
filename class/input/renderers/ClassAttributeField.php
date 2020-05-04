<?php
include_once("lib/input/renderers/DataSourceField.php");
include_once("lib/input/renderers/DataSourceItem.php");
include_once("class/beans/ClassAttributesBean.php");

class ClassAttributeItem extends DataSourceItem
{

    public function renderImpl()
    {

        echo "<label data='attribute_name'>" . $this->label . "</label>";

        echo "<input data='attribute_value' type='text' value='{$this->value}' name='{$this->name}[]'>";

        echo "<input data='foreign_key' type='hidden' name='fk_{$this->name}[]' value='caID:{$this->id}'>";


        echo "<label data='attribute_unit'>" . $this->data_row["unit"] . "</label>";
    }

}

class ClassAttributeFieldAjaxHandler extends JSONRequestHandler
{
    protected $catID = -1;
    protected $prodID = -1;

    public function __construct()
    {
        parent::__construct("ClassAttributeField");
    }

    public function parseParams()
    {
        parent::parseParams();

        if (isset($_GET["catID"])) {
            $this->catID = (int)$_GET["catID"];
        }
        if (isset($_GET["prodID"])) {
            $this->prodID = (int)$_GET["prodID"];
        }
    }

    public function _render(JSONResponse $req)
    {
        $field = new ArrayDataInput("value", "Category Attributes", 0);
        $field->allow_dynamic_addition = FALSE;
        $field->source_label_visible = TRUE;


        $bean1 = new ClassAttributeValuesBean();
        $field->setSource($bean1);

        $rend = new ClassAttributeField();
        $field->setRenderer($rend);

        $rend->setCategoryID($this->catID);
        $rend->setProductID($this->prodID);

        $rend->setField($field);
        $rend->renderImpl();

    }
}

class ClassAttributeField extends DataSourceField implements IArrayFieldRenderer
{

    protected $catID = -1;
    protected $prodID = -1;

    public function __construct()
    {
        parent::__construct();
        $this->setItemRenderer(new ClassAttributeItem());
        $cab = new ClassAttributesBean();
        $this->setIterator($cab->query());
        $this->list_key = "caID";
        $this->list_label = "attribute_name";
        RequestController::addAjaxHandler(new ClassAttributeFieldAjaxHandler());
    }

    public function requiredStyle()
    {
        $arr = parent::requiredStyle();
        $arr[] = SITE_ROOT . "css/ClassAttributeField.css";
        return $arr;
    }

    public function setCategoryID($catID)
    {
        $this->catID = $catID;
        $this->iterator->select->where = "ca.catID='$catID' AND ma.maID=ca.maID";
        $this->iterator->select->fields = " ca.*, ma.name as attribute_name, ma.unit, ma.type ";
        $this->iterator->select->from = " ca, attributes ma ";
    }

    public function setProductID($prodID)
    {
        $this->prodID = (int)$prodID;
        if ($this->prodID > 0) {
            $this->iterator->select->where = "ma.maID=ca.maID AND ca.catID='{$this->catID}'";
            $this->iterator->select->from .= " ca LEFT JOIN class_attribute_values cav ON ca.caID = cav.caID , attributes ma ";
            $this->iterator->select->group_by = "ca.caID";
            $this->iterator->select->having = "(cav.prodID='{$this->prodID}' OR cav.prodID IS NULL)";
            $this->iterator->select->fields = "ca.*, ma.name as attribute_name, ma.unit, ma.type, cav.value, cav.prodID";
        }
    }

    public function renderControls()
    {

    }

    public function renderElementSource()
    {

    }

    public function renderArrayContents()
    {

    }

    public function renderImpl()
    {

        if ($this->catID < 1) {

            echo tr("Select product category first");
            return;
        }

        $num = $this->iterator->exec();

        if ($num < 1) {
            echo "Selected category does not provide optional attributes";
            return;
        }

        $this->startRenderItems();

        $this->renderItems();

        $this->finishRenderItems();

    }

    public function finishRender()
    {
        parent::finishRender();
        ?>
        <script type='text/javascript'>
            onPageLoad(function () {
                console.log("Adding category handler");

                $("[name='catID']").on("change", function () {
                    console.log("Category Changed");

                    var catID = $(this).val();

                    var req = new JSONRequest();
                    req.setURL("?ajax=1&cmd=ClassAttributeField&type=render&catID=" + catID + "&prodID=<?php echo $this->prodID;?>");

                    req.start(
                        function (request_result) {
                            var result = request_result.json_result;
                            var html = result.contents;
                            $(".ClassAttributeField[field='<?php echo $this->field->getName();?>']").html(html);
                        },
                        function (request_error) {
                            showAlert(request_error.description);
                        }
                    );

                });
            });
        </script>
        <?php
    }


    protected function renderItems()
    {

        $field_values = $this->field->getValue();
        $field_name = $this->field->getName();

        if (!is_array($field_values)) {
            $field_values = array($field_values);
        }

        $prkey = $this->iterator->key();
        $index = 0;


        while ($data_row = $this->iterator->next()) {

            $id = $data_row["caID"];


            $value = isset($data_row[$field_name]) ? $data_row[$field_name] : "";
            $label = $data_row[$this->list_label];


            $item = clone $this->item;
            $item->setID($id);
            $item->setValue($value);
            $item->setLabel($label);
            $item->setName($field_name);
            $item->setIndex($index);

            $item->setDataRow($data_row);

            $item->render();

            $index++;
        }
    }
}

?>