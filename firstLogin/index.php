<?php namespace login;

use Manager\DatabaseManager;
use Manager\UtilitiesManager;

session_start();

if(isset($_SESSION['locale']) && !empty($_SESSION['locale']))
{
    $_SESSION['locale'] = 'fr';
}

include_once '../translations/label_'.$_SESSION['locale'].'.php'; //entry const file translation

if(isset($_SESSION['customer_email']) && !empty($_SESSION['customer_email'])) //check email only
{
    header('Location: ../order');
}

if(count($_POST) > 0)
{
    require_once '../Model/InitConsts.php';
    require_once '../Manager/UtilitiesManager.php';

    $a_cleaned_values = UtilitiesManager::checkEmptyDatasPost($_POST);

    if(is_array($a_cleaned_values))
    {
        $firstLoginRequirements = UtilitiesManager::checkUserFirstLoginRequirement($a_cleaned_values); //especially that the passwd != PSK otherwise failure on affected_rows sql

        if(is_bool($firstLoginRequirements))
        {
            require_once '../Manager/DatabaseManager.php';
            $dm = new DatabaseManager;
            $output = $dm->fetchUser($a_cleaned_values['email'], $a_cleaned_values['new_password']); //careful: new_password

            if(is_string($output))
            {
                $output2 = $dm->updatePasswdAndlogin($a_cleaned_values);

                if(is_array($output2))
                {
                    $_SESSION['customer_email'] = $output2['email'];
                    $_SESSION['customer_id']    = $output2['id'];
                    header('Location: ../order');

                }else $errorMsg = $output2;

            }else $errorMsg = WRONG_LOGIN_PAGE.' <a href="../login">login</a>'; //array: if user already exists in db with another password than PSK need to redirect

        }else $errorMsg = $firstLoginRequirements;

    }else $errorMsg = INPUTS_MANDATORIES;
}
include_once '../inc/header_login.php';
?>
<div id="main">
    <h1><?php echo FIRST_CONNECTION ?></h1>
    <form method="post" name="the_form" id="the_form">
        <input type="email" name="email" placeholder="Email" value="<?php echo !empty($_POST['email']) ? $_POST['email'] : '' ?>" autofocus/><br>
        <input type="password" name="psk" value="<?php echo !empty($_POST['psk']) ? $_POST['psk'] : '' ?>" placeholder="<?php echo PSK ?>">
        <br><?php echo DEFINE_NEW_PASSWD ?>
        <br><input type="password" name="new_password" id="new_password" placeholder="<?php echo PASSWD.' 6 '.CHARS ?>">
        &nbsp;<span onmouseover="document.getElementById('new_password').type ='text';" onmouseout="document.getElementById('new_password').type ='password';"><?php echo SHOW ?></span>
        <br><a href="#" onclick="document.getElementById('the_form').submit();"><?php echo CONNECTION ?></a>
        <input type="submit" style="display: none;" />
        <p id="return_from_handleSession">
            <?php echo isset($errorMsg) ? '<font color="red">'.$errorMsg.'</font>' : '' ?>
        </p>
    </form>
</div>
</body>
</html>