<?php
namespace Order;
use Manager\DatabaseManager;
use Model\InitConsts as IC;
session_start();
if(!isset($_SESSION['customer_email'])) header('Location: login/');

require_once 'Model/InitConsts.php';

if(isset($_GET['lg']) && in_array(trim($_GET['lg']), IC::LOCALE))
{
  $_SESSION['locale'] = trim($_GET['lg']);

}else $_SESSION['locale'] = 'fr';

$item = trim(strtolower($_GET['item']));

if(isset($item) || !empty($item))
{
  $_SESSION['item'] =  in_array($item, array_keys(IC::SHOP_ITEMS_AND_RATES)) ? $item : key(IC::SHOP_ITEMS_AND_RATES);

}else $_SESSION['item'] = key(IC::SHOP_ITEMS_AND_RATES); //should output first key by default!!!

include_once 'translations/label_'.$_SESSION['locale'].'.php';

?>
<!DOCTYPE html>
<html>
<head lang="en">
  <meta charset="UTF-8">
  <title><?php echo $_SESSION['item'] ?></title>
  <link rel="icon" href="img/favicon.ico"/>
  <meta name="description" content="<?php echo $_SESSION['item'] ?>"/>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.8/jquery.min.js"></script>
  <script type="text/javascript" src="js/responsive-tgl.js"></script>
  <script>
  <?php
  //pass PHP consts to JavaScript

  echo 'var item = "'.$_SESSION['item'].'";'.PHP_EOL;

  if(count(IC::SHOP_ITEMS_AND_RATES[$_SESSION['item']]) > 0){

    $i = 0;
    foreach(IC::SHOP_ITEMS_AND_RATES[$_SESSION['item']] as $rate):

      $i++;
      echo 'var '.$_SESSION['item'].'Rate'.$i.' = ' .$rate. ';' . PHP_EOL;

    endforeach;
  }

  if($_SESSION['item'] === 'markball'){

    echo 'var minimumQuantityOrder = 10;' . PHP_EOL;

  }else   echo 'var minimumQuantityOrder = ' . IC::MINIMUM_Q_ORDER . ';' . PHP_EOL;

  echo 'var currency = "' . IC::CURRENCY[0] . '";' . PHP_EOL;
  echo 'var locale = "' . $_SESSION['locale'] . '";' . PHP_EOL;
  ?>
  </script>
  <script type="text/javascript" src="js/translations.js"></script>
  <script type="text/javascript" src="js/script.js"></script>
  <link rel="stylesheet" type="text/css" href="css/style.css"/>
</head>
<body>
  <div id="checkvalues">
    <div onclick="document.getElementById('checkvalues').style.visibility = 'hidden';" id="btnClose">
      <div style="padding-top: 4px;"><b>X</b></div>
    </div>
    <h1>Confirmation</h1>
    <hr/>
    <p id="return_from_checkvalues"></p>
  </div>
  <div id="top">


    <!--========================================= User acc info div========================================= -->
    <div id = "user">
      <?php
      foreach(IC::SHOP_ITEMS_AND_RATES as $k => $v):

        if($k === $_SESSION['item']){

          echo '&nbsp;<span style="font-weight: bold; color: red;"><a href="?item='.$k.'">'.$k.'</a></span>&nbsp;';

        }else echo '&nbsp;<a href="?item='.$k.'">'.$k.'</a>&nbsp;';

      endforeach;

      ?>
      <!--<img src="img/logo-tp.png" style="border: none; width: 200px; margin-right: 10px;"/>-->
      <br><a href="login?do=logout"><?php echo LOGOUT ?>(<?php echo $_SESSION['customer_email'] ?>)</a>
    </div>
    <!--=========================================Toggle Button=========================================  -->
    <button class="tgl-button">&#9776;</button>
    <!--=========================================CHOICE FORMMMMM=========================================  -->

    <div id="toggle-div">
      <div id="choice_form">
        <form style="float: left">
          <select id="fillAction"
          onchange="whatNext(this.value);">
          <option value=""><?php echo CHOOSE ?></option>
          <option value="all"><?php echo FILL_ALL_WITH_ONE ?></option>
          <option value="50"><?php echo FILL_50_WITH_1 ?></option>
          <option value="100"><?php echo FILL_100_WITH_1 ?></option>
          <option value="25"><?php echo FILL_25_WITH_1 ?></option>
        </select>
        <p>
          <a href="#" onclick="clearAllInputsValues();"><?php echo CLEAR_VALUES ?></a>
          <br><span class="asterisk">*</span><?php echo ASTERISK_MSG ?>
        </p>
      </form>
    </div>
    <!--=========================================REMPLIR FORM=========================================  -->
    <div id="remplir_form" >
      <input type="text" id="num_items" placeholder="<?php echo DIFF_ITEMS ?>" style="width: 100px;">&nbsp;<input
      type="text" id="quantity" placeholder="<?php echo Q ?>" style="width: 50px;">
      <p>
        <a href="#"
        onclick="fillXQuantitiesWithXItems(document.getElementById('quantity').value, document.getElementById('num_items').value);"><?php echo FILL ?></a>
      </p>
    </div>
    <?php if(in_array($_SESSION['item'], ['tampoon', 'pitchfix', ])){ ?>
      <!--=========================================PRESENT DIV=========================================  -->
      <div id="present_div">
        <h2><?php echo STANDING_UNIT ?></h2>
        <p id="give_border">
          <label><?php echo NO ?></label>&nbsp;<input type="radio" value="3" name="standing_unit" checked>
          <label>27 <?php echo UNITS ?></label>&nbsp;<input type="radio" value="1" name="standing_unit">
          <label>45 <?php echo UNITS ?></label>&nbsp;<input type="radio" value="2" name="standing_unit">
        </p>
      </div>
      <?php } ?>
      <div id="infos"><p id="return_from_makeSum" style="margin-bottom: 0;"></p></div>

    </div>
  </div>
  <!-- MAIN DIV for ITEMS -->
  <div id="main">
    <form method="post" name="the_form">
      <?php
      require_once 'Manager/DatabaseManager.php';
      $dbm = new DatabaseManager;

      $outputDBM = $dbm->fetchItemInfos($_SESSION['item'], FALSE);

      foreach ($outputDBM as $rows):

        $ref = str_replace([' ', '.', ], ['espace', 'point', ], $rows['reference']); //because php replace spaces and points by underscores when posting

        $icon = 'icon/'.$_SESSION['item'].'/'.$rows['reference'].'.jpg';

        echo '<div class="container_icon" id="container_'.$ref.'"><table><tr><td><img class="icon" src="' . $icon . '" /></td></tr>';
        
echo '<tr><td id="titleOfTamp" title="'.str_ireplace(['_', '.', ], ' ', $rows['reference']).'">' .str_replace(['_', '.', ], ' ', substr($rows['reference'], 0, 8)). '</td></tr>';
        echo '<tr><td>';
        echo '<input placeholder="'.(($rows['quantity'] < 0) ? 0 : $rows['quantity']).'" type="number" min="0" max="99"';
        echo ' id="'.$ref.'" name="'.$ref.'" onclick="makeSum(this);" onkeyup="makeSum(this);" ';
        echo 'onchange="switchDivDisplay(this.value, \'container_'.$ref.'\')" ';
        echo 'onfocus="if(document.getElementById(\'checkvalues\').style.visibility === \'visible\') document.getElementById(\'checkvalues\').style.visibility = \'hidden\';"/>';
        echo '&nbsp;<span id="span_'.$ref.'"'.(($rows['quantity'] > 0) ? '>dispo' : 'class="asterisk">*').'</span>';
        echo '</td></tr>';
        echo '</table></div>';

      endforeach;
      ?>
    </form>
  </div>
  <!-- MAIN DIV with ITEM End -->
</body>
</html>
