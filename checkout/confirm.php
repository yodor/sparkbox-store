<?php
include_once("session.php");

include_once("class/pages/CheckoutPage.php");
include_once("class/forms/ClientAddressInputForm.php");
include_once("class/beans/ClientAddressesBean.php");
include_once("class/forms/InvoiceDetailsInputForm.php");
include_once("class/beans/InvoiceDetailsBean.php");
include_once("class/forms/EkontOfficeInputForm.php");
include_once("class/beans/EkontAddressesBean.php");

include_once("class/utils/OrderProcessor.php");
include_once("class/mailers/OrderConfirmationMailer.php");
include_once("class/mailers/OrderConfirmationAdminMailer.php");

class RequireInvoiceInputForm extends InputForm {
    public function __construct()
    {
        parent::__construct();
        $field = InputFactory::CreateField(InputFactory::CHECKBOX, "require_invoice", "Да се издаде фактура", 0);

        $field->getRenderer()->setFieldAttribute("onClick", "javascript:this.form.submit()");
        $this->addField($field);
    }
}

class RequireInvoiceFormProcessor extends FormProcessor {

    protected $bean = null;

    public function setBean(DBTableBean $bean)
    {
        $this->bean=$bean;
    }

    public function processImpl(InputForm $form)
    {

        parent::processImpl($form);
        
        if ($this->getStatus() != FormProcessor::STATUS_OK) return;
        
        $page = SitePage::getInstance();
        $cart = $page->getCart();

        $cabrow = $this->bean->findFieldValue("userID", $page->getUserID());
        if (!$cabrow) {
            header("Location: invoice_details.php");
            exit;
        }
        
        $cart->setRequireInvoice($form->getField("require_invoice")->getValue());
    
    }

}

class OrderNoteInputForm extends InputForm
{
    public function __construct()
    {
        parent::__construct();
        $field = InputFactory::CreateField(InputFactory::TEXTAREA, "note", "Забележка", 0);
        $field->getRenderer()->setFieldAttribute("maxlength", "200"); 
        $this->addField($field);
    }
}

class OrderNoteFormProcessor extends FormProcessor
{
    public function processImpl(InputForm $form)
    {
        parent::processImpl($form);
        
        if ($this->getStatus() != FormProcessor::STATUS_OK) return;
        
        
        $page = SitePage::getInstance();
        $cart = $page->getCart();
        
        
        
        $cart->setNote($form->getField("note")->getValue());
        
        header("Location: complete.php");
        exit;
    }
}

$page = new CheckoutPage();

$page->ensureCartItems();
$page->ensureClient();

$cart = $page->getCart();

//request invoice
$reqform = new RequireInvoiceInputForm();

$idb = new InvoiceDetailsBean();
$idbrow = $idb->findFieldValue("userID", $page->getUserID());
if (!$idbrow) {
    $reqform->getField("require_invoice")->setValue(false);
}
else {
    $reqform->getField("require_invoice")->setValue($cart->getRequireInvoice());
}

$reqproc = new RequireInvoiceFormProcessor();
$reqproc->setBean($idb);

$frend = new FormRenderer();
$frend->setName("RequestInvoice");
$frend->setForm($reqform);

$reqproc->processForm($reqform, "require_invoice");


//order note 
$noteform = new OrderNoteInputForm();
$noteform->getField("note")->setValue($cart->getNote());

$nfrend = new FormRenderer();
$nfrend->setName("OrderNote");
$nfrend->setForm($noteform);

$noteproc = new OrderNoteFormProcessor();
$noteproc->processForm($noteform, "note");



$page->beginPage();

$page->setPreferredTitle(tr("Потвърди поръчка"));


$page->drawCartItems();




echo "<div class='item delivery_type'>";

    echo "<div class='caption'>".tr("Начин на доставка")."</div>";
    
    echo "<div class='value'>";
    echo tr(Cart::getDeliveryTypeText($cart->getDeliveryType()));
    echo "</div>";
    
    echo "<a class='DefaultButton' href='delivery.php'>";
    echo tr("Промени");
    echo "</a>";
    
echo "</div>"; //item delivery_type


echo "<div class='item address'>";
    
    echo "<div class='caption'>".tr("Адрес за доставка")."</div>";
    
    echo "<div class='value'>";
        if (strcmp($cart->getDeliveryType(),Cart::DELIVERY_USERADDRESS)==0) {
            $form = new ClientAddressInputForm();
            $bean = new ClientAddressesBean();
            $row = $bean->findFieldValue("userID", $page->getUserID());
            if (!$row) {
                header("Location: delivery_address.php");
                exit;
            }

            $form->loadBeanData($row[$bean->getPrKey()], $bean);
            $form->renderPlain();
            
            echo "<a class='DefaultButton' href='delivery_address.php'>";
            echo tr("Промени");
            echo "</a>";
            
        }
        else if (strcmp($cart->getDeliveryType(),Cart::DELIVERY_EKONTOFFICE)==0) {
            $form = new EkontOfficeInputForm();
            $bean = new EkontAddressesBean();
            $row = $bean->findFieldValue("userID", $page->getUserID());
            if (!$row) {
                header("Location: delivery_ekont.php");
                exit;
            }
            
            $form->loadBeanData($row[$bean->getPrKey()], $bean);
            $form->renderPlain();
            
            echo "<a class='DefaultButton' href='delivery_ekont.php'>";
            echo tr("Промени");
            echo "</a>";
        }
    echo "</div>";//value
    
echo "</div>";// item address



echo "<div class='item invoicing'>";

    echo "<div class='caption'>".tr("Фактуриране")."</div>";
        
    $frend->render();
    
    echo "<div class='value'>";
    if ($idbrow && $cart->getRequireInvoice()) {
        $idform = new InvoiceDetailsInputForm();
        $idform->loadBeanData($idbrow[$idb->getPrKey()], $idb);
        $idform->renderPlain();
        
        echo "<a class='DefaultButton' href='invoice_details.php'>";
        echo tr("Промени");
        echo "</a>";
    }
    echo "</div>";
    
echo "</div>";



echo "<div class='item note'>";
    echo "<div class='caption'>".tr("Забележка")."</div>";
    $nfrend->render();
echo "</div>";

echo "<div class='navigation'>";

    echo "<div class='slot left'>";
        echo "<a href='cart.php'>";
        echo "<img src='".SITE_ROOT."images/cart_edit.png'>";
        echo "<div class='checkout_button' >".tr("Назад")."</div>";
        echo "</a>";
    echo "</div>";

    echo "<div class='slot center'>";
        echo "<div class='note'>";
            echo "<i>".tr("Натискайки бутона 'Потвърди поръчка' Вие се съгласявате с нашите")."&nbsp;"."<a  href='".SITE_ROOT."terms_usage.php'>".tr("Условия за ползване")."</a>&nbsp; ".tr("и")."&nbsp<a  href='".SITE_ROOT."terms_delivery.php'>".tr("Условия за доставка")."</a></i>";
        echo "</div>";
    echo "</div>";
  
    echo "<div class='slot right'>";
        echo "<a href='javascript:document.forms.OrderNote.submit()'>";
        echo "<img src='".SITE_ROOT."images/cart_checkout.png'>";
        echo "<div class='checkout_button'  >".tr("Потвърди поръчка")."</div>";
        echo "</a>";
    echo "</div>";


echo "</div>";
$page->finishPage();
?>