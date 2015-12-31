<?php namespace ajax;

header('Content-type: text/html; charset="UTF-8";');

use Manager\DatabaseManager;
use Manager\UtilitiesManager;

if(isset($_POST) && count($_POST) > 0)
{
    session_start();
    include_once '../translations/label_'.(isset($_SESSION['locale']) ? $_SESSION['locale'] : 'fr').'.php';
    require_once '../Model/InitConsts.php';
    require_once '../Manager/UtilitiesManager.php';

    $a_cleaned_values = UtilitiesManager::checkEmptyDatasPost($_POST);

    if(is_array($a_cleaned_values))
    {
        if(FALSE !== stripos($a_cleaned_values['email'], '@') && FALSE !== stripos($a_cleaned_values['email'], '.'))
        {
            require_once '../Model/InitConsts.php';
            require_once '../Manager/DatabaseManager.php';

            $mm = new DatabaseManager;

            $output = $mm->fetchUser($a_cleaned_values['email']);

            if(is_bool($output))
            {
                if($output)
                {
                    echo '<br><input type="password" name="password" placeholder="'.PASSWD.' 6 '.CHARS.'">';
                    echo '<br><br><a href="#" onclick="document.getElementById(\'the_form\').submit();">'.CONNECTION.'</a>';

                }else
                {
                    echo '<input type="hidden" name="first_login" value="true">';
                    echo '<br><input type="password" name="psk" placeholder="'.PSK.'">';
                    echo '<br><input type="password" name="new_password" placeholder="'.PASSWD.' 6 '.CHARS.'">';
                    echo '<br><br><a href="#" onclick="document.getElementById(\'the_form\').submit();">'.CONNECTION.'</a>';
                }

            }else echo 'e<font color="red">'.$output.'</font>';
        }

    }else echo INPUTS_MANDATORIES;
}