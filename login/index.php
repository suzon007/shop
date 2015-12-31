<?php namespace login;

use Manager\DatabaseManager;
use Manager\UtilitiesManager;
use Model\InitConsts;

session_start();
include_once '../translations/label_'.(isset($_SESSION['locale']) ? $_SESSION['locale'] : 'fr').'.php'; //entry const file translation

if(isset($_SESSION['customer_email']) && !empty($_SESSION['customer_email'])) //check email only
{
    header('Location: ../');
}

if(isset($_GET['do']) && trim($_GET['do']) === 'logout'){ //logout

    unset($_SESSION['customer_email']);
    unset($_SESSION['customer_id']);
}

if(count($_POST) > 0)
{
    require_once '../Model/InitConsts.php';
    require_once '../Manager/UtilitiesManager.php';

    $a_cleaned_values = UtilitiesManager::checkEmptyDatasPost($_POST);

    if(is_array($a_cleaned_values))
    {
        require_once '../Manager/DatabaseManager.php';
        $dm = new DatabaseManager;
        $output = $dm->fetchUser($a_cleaned_values['email'], $a_cleaned_values['password']);

        if(is_array($output))
        {
            if($output['password'] !== InitConsts::HASH_PASSWD)
            {
                $_SESSION['customer_email'] = $output['email'];
                $_SESSION['customer_id']    = $output['id'];
                header('Location: ../');

            }else $errorMsg = WRONG_LOGIN_PAGE.' <a href="../firstLogin">login</a>'; //if user try to login with PSK as password in classic login page

        }else $errorMsg = $output;

    }else $errorMsg = INPUTS_MANDATORIES;
}

include_once '../inc/header_login.htm';
?>
<div id="main">
    <h1><?php echo CONNECTION ?></h1>
    <form method="post" name="the_form" id="the_form">
        <input type="email" name="email" placeholder="Email" value="<?php echo !empty($_POST['email']) ? $_POST['email'] : '' ?>" autofocus/><br>
        <p id="return_from_handleSession">
            <br><input type="password" name="password" placeholder="<?php echo PASSWD.' 6 '.CHARS ?>">
            <br><br><a href="#" onclick="document.getElementById('the_form').submit();"><?php echo CONNECTION ?></a>
            <input type="submit" style="display: none;" />
            <br><?php echo isset($errorMsg) ? '<font color="red">'.$errorMsg.'</font>' : '' ?>
        </p>
    </form>
    <p>
        <a href="../resetPassword"><?php echo FORGOTTEN_PASSWD ?></a>
    </p>
</div>
</body>
</html>