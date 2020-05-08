<?php
include_once("forms/processors/FormProcessor.php");
include_once("beans/DBTableBean.php");
include_once("db/DBTransactor.php");

class ClientAddressFormProcessor extends FormProcessor
{
    protected $bean = NULL;
    protected $editID = -1;
    protected $userID = -1;



    public function setUserID(int $userID) : void
    {
        $this->userID = $userID;
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
