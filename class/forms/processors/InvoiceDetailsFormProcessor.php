<?php
include_once("lib/forms/processors/FormProcessor.php");
include_once("lib/beans/DBTableBean.php");
include_once("lib/db/DBTransactor.php");

class InvoiceDetailsFormProcessor extends FormProcessor
{
    protected $bean = NULL;
    protected $editID = -1;
    protected $userID = -1;

    public function setEditID($editID)
    {
        $this->editID = (int)$editID;
    }

    public function setUserID($userID)
    {
        $this->userID = (int)$userID;
    }

    public function setBean(DBTableBean $bean)
    {
        $this->bean = $bean;
    }

    public function processImpl(InputForm $form)
    {

        parent::processImpl($form);

        if ($this->getStatus() != FormProcessor::STATUS_OK) return;

        if ($this->userID < 1) throw new Exception("Тази функция изисква регистрация");

        $dbt = new DBTransactor();
        $dbt->appendValue("userID", $this->userID);

        $dbt->transactValues($form);

        $dbt->processBean($this->bean, $this->editID);

    }
}

?>
