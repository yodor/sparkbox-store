<?php
include_once("session.php");
include_once("class/pages/CheckoutPage.php");

include_once("class/forms/RegisterClientInputForm.php");
include_once("class/forms/processors/RegisterClientFormProcessor.php");

include_once("auth/UserAuthenticator.php");
include_once("responders/AuthenticatorResponder.php");
include_once("forms/LoginForm.php");
include_once("forms/renderers/LoginFormRenderer.php");

$page = new CheckoutPage();

if ($page->getUserID() > 0) {
    header("Location:delivery.php");
    exit;
}

$auth = new UserAuthenticator();

$req = new AuthenticatorResponder($auth, "doLogin");
$req->setCancelUrl(LOCAL . "/checkout/customer.php");
$req->setSuccessUrl(LOCAL . "/checkout/delivery.php");


if ($auth->authorize()) {
    header("Location: delivery.php");
    exit;
}

$af = new LoginForm();
$afr = new LoginFormRenderer($af, $req);

$action = $afr->getTextSpace()->get(0);
if ($action instanceof Action) {
    $action->getURLBuilder()->buildFrom(LOCAL . "/account/forgot_password.php");
}

$form = new RegisterClientInputForm();
$form->setName("RegisterClient");

$frender = new FormRenderer($form);

$proc = new RegisterClientFormProcessor();

$proc->process($form);

if ($proc->getStatus() == IFormProcessor::STATUS_ERROR) {
    Session::SetAlert($proc->getMessage());
}
else if ($proc->getStatus() == IFormProcessor::STATUS_OK) {

    header("Location: delivery.php");
    exit;
}

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Expires: 0");

$page->startRender();
$page->setTitle(tr("Клиенти"));

echo "<div class='item login'>";

echo "<div class='caption'>" . tr("Регистрирани Клиенти") . "</div>";

echo "<div class='login_component'>";
echo "<div class='inner'>";
$afr->render();
echo "</div>";
echo "</div>";

echo "</div>";

//
echo "<div class='item register'>";

echo "<div class='caption'>" . tr("Нови Клиенти") . "</div>";

echo "<div class='panel'>";

$frender->render();

echo "</div>";

echo "</div>";

echo "<div class='navigation'>";

echo "<div class='slot left'>";
echo "<a href='cart.php'>";
echo "<img src='" . LOCAL . "/images/cart_edit.png'>";
echo "<div class='ColorButton checkout_button' >" . tr("Назад") . "</div>";
echo "</a>";
echo "</div>";

echo "<div class='slot center'>";
//     echo "<div class='note'>";
//         echo "<i>".tr("Натискайки бутона 'Продължи' Вие се съгласявате с нашите")."&nbsp;"."<a  href='".LOCAL."/terms.php'>".tr("Условия за ползване")."</a></i>";
//     echo "</div>";
echo "</div>";

echo "<div class='slot right'>";
echo "<a href='javascript:document.forms.RegisterClient.submit();'>";
echo "<img src='" . LOCAL . "/images/cart_checkout.png'>";
echo "<div class='ColorButton checkout_button'>" . tr("Продължи") . "</div>";
echo "</a>";
echo "</div>";
//
//

echo "</div>";

$page->finishRender();
?>
