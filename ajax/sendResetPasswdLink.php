<?php namespace ajax;

use Manager\DatabaseManager;
use Manager\MailManager;
use Model\InitConsts;

header('Content-type: text/html; charset="UTF-8";');

if(count($_POST) > 0)
{
    $rescueEmail = trim($_POST['email_rescue']);

    if(strlen($rescueEmail) > 5)
    {
        session_start();
        include_once '../translations/label_'.(isset($_SESSION['locale']) ? $_SESSION['locale'] : 'fr').'.php';
        require_once '../Model/InitConsts.php';
        require_once '../Manager/DatabaseManager.php';

        $dm = new DatabaseManager;
        $outputdm = $dm->fetchUser($rescueEmail);

        if(TRUE === $outputdm)
        {
            //using redis to store temporary hash and email info
            $hash = sha1(microtime(TRUE));
            $redis = new \Redis;
            $redis->connect('127.0.0.1');
            $redis->set($rescueEmail, $hash, 60*60);

            require_once '../Manager/MailManager.php';

            $resetLink = 'http://'.$_SERVER['HTTP_HOST'].'/resetPassword/?email='.$rescueEmail.'&hash='.$hash;

            $msg = '<html><body><br><a href="'.$resetLink.'">'.CLICK_TO_RESET_PASSWD.'</a>';
            $msg .= '<br>'.COPY_RESET_PASSWD_URL;
            $msg .= '<br>'.$resetLink;
            $msg .= '<br><font color="red>"'.AVAILABLE_24H.'</font>';
            $msg .= '</body></html>';

            $mm = new MailManager($rescueEmail, InitConsts::GMAIL_BOX, RESET_PASSWORD, $msg);
            $outputmm = $mm->send();

            if(is_bool($outputmm))
            {
                echo MAILS_SENT;

            }else $errorMsg = $outputmm;

        }else $errorMsg = (FALSE === $outputdm) ? MUST_DO_A_FIRST_LOGIN : $outputdm;
    }

    if(isset($errorMsg)) echo 'e<font color="red">'.$errorMsg.'</font>';
}