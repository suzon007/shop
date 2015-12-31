<?php namespace resetPassword;
session_start();
include_once '../translations/label_'.(isset($_SESSION['locale']) ? $_SESSION['locale'] : 'fr').'.php';
?>
<div id="main">
<?php
if(count($_GET) > 0)
{
    $redis = new \Redis;
    $redis->connect('127.0.0.1');

    if($redis->get(trim($_GET['email'])) === trim($_GET['hash'])) //means user comes from email + has redis values existing
    {
?>

    <h3><?php echo RESET_PASSWORD ?></h3>
    <form name="the_form" id="the_form" method="post">
        <input type="hidden" name="email" value="<?php echo trim($_GET['email']) ?>">
        <input type="password" name="password" id="password" placeholder="<?php echo PASSWD.' 6 '.CHARS ?>" autofocus />
        &nbsp;<span onmouseover="document.getElementById('password').type ='text';" onmouseout="document.getElementById('password').type ='password';"><?php echo SHOW ?></span>
    </form>
    <p id="return_from_updatePasswd">
        <a href="#" onclick="updatePasswd();"><?php echo UPDATE ?></a>
    </p>

<?php
    }else echo RESET_PASSWD_LINK_EXPIRED;

}else
{
    ?>
    <div id="recover_passwd_content">
        <h3><?php echo FORGOTTEN_PASSWD ?></h3>
        <form name="recover_passwd" id="recover_passwd">
            <input type="email" name="email_rescue" id="email_rescue" placeholder="Email" autofocus />
        </form>
        <br>
        <a href="#" onclick="sendResetPasswdLink(document.getElementById('email_rescue').value);"><?php echo SEND ?></a>
        <p id="return_from_sendResetPasswdLink"></p>
    </div>
<?php
}
?>
</div>
</body>
</html>
