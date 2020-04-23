<?php
include_once("session.php");
include_once("class/pages/AccountPage.php");

include_once("lib/beans/UsersBean.php");
include_once("class/mailers/ForgotPasswordMailer.php");
include_once("class/forms/ForgotPasswordInputForm.php");

include_once("lib/auth/Authenticator.php");


class ForgotPasswordProcessor extends FormProcessor
{
    protected function processImpl(InputForm $form)
    {
        parent::processImpl($form);

        if ($this->status != IFormProcessor::STATUS_OK) return;

        $email = $form->getField("email")->getValue();

        global $users;

        if (!$users->emailExists($email)) {
            $form->getField("email")->setError(tr("Този адрес не е регистриран при нас"));
            throw new Exception(tr("Този адрес не е регистриран при нас"));
        }


        $random_pass = Authenticator::RandomToken(8);

        $db = DBDriver::Get();
        try {
            $db->transaction();

            $userID = $users->email2id($email);
            $update_row = array();
            $update_row["password"] = md5($random_pass);
            if (!$users->update($userID, $update_row, $db)) throw new Exception("Невъзможна промяна на запис: " . $db->getError());

            $fpm = new ForgotPasswordMailer($email, $random_pass);
            $fpm->send();

            $db->commit();
            $this->setMessage(tr("Вашата нова парола беше изпратена на адрес") . ": $email");
        }
        catch (Exception $e) {
            $db->rollback();
            throw $e;
        }
    }
}

$page = new AccountPage(false);

$users = new UsersBean();

$form = new ForgotPasswordInputForm();

$frend = new FormRenderer();
$frend->setName("ForgotPassword");
$frend->getSubmitButton()->setText(tr("Изпрати"));
$form->setRenderer($frend);

$proc = new ForgotPasswordProcessor();
$form->setProcessor($proc);

$proc->processForm($form);

if ($proc->getStatus() != IFormProcessor::STATUS_NOT_PROCESSED) {
    Session::Set("alert", $proc->getMessage());
    header("Location: forgot_password.php");
    exit;
}

$page->startRender();

$page->setPreferredTitle("Забравена парола");

echo "<div class='caption'>";
echo $page->getPreferredTitle();
echo "</div>";

echo "<div class='panel'>";

echo tr("Въведете Вашият email aдрес от момента на регистрация в сайта и натиснете бутон 'Изпрати'");
echo "<BR>";
echo tr("Вашата нова парола ще бъде изпратена на този адрес.");

echo "<BR><BR>";

$frend->renderForm($form);

echo "</div>";

$page->finishRender();
?>
