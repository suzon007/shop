/*you can use these globals variables (they are wrtitten by PHP in ../index.php and passed to JS)

var item
var XRateInt
var minimumQuantityOrder
var currency
var locale
 */

var sum;
var total;
var styleTampoonContainer = 'border: 2px solid cornflowerblue;border-radius: 3px;padding: 5px;';

function makeSum(p1_input_number)
{
    sum = 0;

    if(func_num_args() > 0)
    {
        if(p1_input_number.value > p1_input_number.placeholder)
        {
            document.getElementById('span_'+p1_input_number.id).innerHTML = '*';
            document.getElementById('span_'+p1_input_number.id).className = 'asterisk';

        }else if(p1_input_number.value <= p1_input_number.placeholder)
        {
            document.getElementById('span_'+p1_input_number.id).innerHTML = translations[locale][10];
            document.getElementById('span_'+p1_input_number.id).className = '';
        }
    }

    var inputs = document.querySelectorAll('#main input');

    for(var i = 0; i < inputs.length; i++)
    {
        if(inputs[i].value != '')
        {

            sum += parseInt(inputs[i].value);
        }
    }

    if(isNaN(sum)){

        sum = 0;
        alert(translations[locale][0]);

    }
    var divInfo = document.getElementById('return_from_makeSum');

    divInfo.innerHTML = translations[locale][9]+': '+sum+'<br>';

    if(item === 'tampoon') //need to find better approach in a near future
    {
        if(sum < 100)
        {
            total = sum * tampoonRate1;
            divInfo.innerHTML += 'Total: '+total+' '+currency+'<br>';

        }else
        {
            total = sum * tampoonRate2;
            divInfo.innerHTML += 'Total: '+total+' '+currency+'<br>';
        }

    }else if(item === 'markball'){

        total = sum * markballRate1;
        divInfo.innerHTML += 'Total: '+total+' '+currency+'<br>';
    }

    if(sum >= minimumQuantityOrder)
    {
        divInfo.innerHTML += '<p><a id="for_send" href="#" onclick="checkValues();">'+translations[locale][1]+'</a></p>';
    }

    if(sum > 0)
    {
        document.getElementById('infos').style.display = 'block';

    }else{
        document.getElementById('infos').style.display = 'none';
        document.getElementById(p1_input_number.id).value = '';
    }

}

function checkValues()
{
    document.getElementById('checkvalues').style.visibility = 'visible';

    var htmOutput = '<p id="pForProcessOrder"><a href="#" style="font-size: 20px;"  onclick="processOrder();">'+translations[locale][4]+'</a></p><p id="return_from_processOrder"></p>';

    document.getElementById('return_from_checkvalues').innerHTML = htmOutput;
}

function processOrder()
{

    try{
        var standingUnits = document.getElementsByName('standing_unit');

    }catch(e){ alert(e.toString()); }

    var containerSendMailLink = document.getElementById('pForProcessOrder');
    containerSendMailLink.innerHTML = '<img src="img/spinner.gif" style="border: none;" />';

    var oData = new FormData(document.forms.namedItem('the_form'));
    var oReq = new XMLHttpRequest();

    oReq.open('POST', './ajax/processOrder.php', true);

    oData.append('quantityTampoon', sum);
    oData.append('total', total);
    oData.append('item', item);

    if(standingUnits != null || standingUnits != undefined || standingUnits != 'undefined')
    {
        for(var i = 0; i < standingUnits.length; i++)
        {
            if(standingUnits[i].checked)
            {
                oData.append('standingUnit', standingUnits[i].value);
            }
        }
    }

    oReq.onload = function(oEvent)
    {
        if (oReq.status === 200)
        {
            if(oReq.responseText.substr(0,1) === 'e')
            {
                containerSendMailLink.innerHTML = '<a href="#" style="font-size: 16px;"  onclick="processOrder();">'+translations[locale][4]+'</a>';
                document.getElementById('return_from_processOrder').innerHTML = oReq.responseText.substr(1);

            }else
            {
                containerSendMailLink.innerHTML = '';
                document.getElementById('return_from_processOrder').innerHTML = oReq.responseText;
            }

        }else{

            document.getElementById('return_from_processOrder').innerHTML = 'Error ' + oReq.status;
        }
    };

    oReq.send(oData);
}

function clearAllInputsValues()
{
    var inputs = document.querySelectorAll('#main input');

    for(var i = 0; i < inputs.length; i++)
    {
        inputs[i].value = '';
        document.getElementById('container_'+inputs[i].id).style.cssText = 'border: none;';
    }

    document.getElementById('infos').style.display = 'none';
}

function fillAllWith1Q()
{
    var inputs = document.querySelectorAll('#main input');

    for(var i = 0; i < inputs.length; i++)
    {
        document.getElementById('container_'+inputs[i].name).style.cssText = 'border: none;';
        inputs[i].value = 1;
    }

    makeSum();
}

function fill50ValWithXQ(p1_which_quantity)
{
    clearAllInputsValues();

    var inputs = document.querySelectorAll('#main input');

    var inputs_name = [];

    for(var i = 0; i < inputs.length; i++)
    {
        document.getElementById('container_'+inputs[i].name).style.cssText = 'border: none;';
        inputs_name.push(inputs[i].name);
    }

    var array_random = array_rand(inputs_name, 50);

    for(var prop in array_random)
    {
        document.getElementById(inputs_name[array_random[prop]]).value = p1_which_quantity;
        document.getElementById('container_'+inputs_name[array_random[prop]]).style.cssText = styleTampoonContainer;

    }

    makeSum();
}

function fillXQuantitiesWithXItems(p1_which_quantity, p2_differents_items)
{
    if(p1_which_quantity != '' && p2_differents_items != '')
    {
        var p1_q = parseInt(p1_which_quantity);
        var p2_diff_items = parseInt(p2_differents_items);

        if(p1_q != 0 && p2_diff_items != 0)
        {
            if(p2_diff_items > 1)
            {
                clearAllInputsValues();

                var inputs = document.querySelectorAll('#main input');

                var numInputs = inputs.length;

                if(p2_diff_items <= numInputs)
                {
                    var availableTampoons = [];

                    for(var i = 0; i < inputs.length; i++)
                    {
                        document.getElementById('container_'+inputs[i].name).style.cssText = 'border: none;';

                        if(inputs[i].max >= p1_q)
                        {
                            //console.log(inputs[i].id+' : '+inputs[i].max);
                            availableTampoons.push(inputs[i].name);
                        }
                    }
                    //console.log(availableTampoons.length+' >= '+p2_diff_items);

                    if(availableTampoons.length >= p2_diff_items)
                    {
                        var array_random = array_rand(availableTampoons, p2_diff_items);

                        for(var prop in array_random)
                        {
                            document.getElementById(availableTampoons[array_random[prop]]).value = p1_q;
                            document.getElementById('container_'+availableTampoons[array_random[prop]]).style.cssText = styleTampoonContainer;
                        }

                        makeSum();

                    }else{ alert(translations[locale][6]); }

                }else{ alert(translations[locale][7]+numInputs); }

            }else{ alert(translations[locale][8]); }
        }
    }
}

function changePassword()
{
    var oOutput = document.getElementById('return_from_changePassword');

    document.getElementById('containerLinkAction').innerHTML = '<img src="../img/spinner.gif" style="border: none;" />';

    var oData = new FormData(document.forms.namedItem('the_form'));

    var oReq = new XMLHttpRequest();

    oReq.open('POST', '../ajax/changePassword.php', true);

    oReq.onload = function(oEvent)
    {
        if (oReq.status === 200)
        {
            if(oReq.responseText.substr(0, 1) === 'e')
            {
                document.getElementById('containerLinkAction').innerHTML = '<a href="#" onclick="changePassword();">Change</a><br>';
                oOutput.innerHTML = oReq.responseText.substr(1);

            }else
            {
                document.getElementById('containerLinkAction').innerHTML = '';
                oOutput.innerHTML = oReq.responseText;

            }

        }else
        {
            oOutput.innerHTML = 'Error ' + oReq.status;
        }
    };

    oReq.send(oData);
}

function switchDivDisplay(p1_tampoon_q, p2_div_container_id)
{
    if(p1_tampoon_q == 0)
    {
        document.getElementById(p2_div_container_id).style.cssText = 'border: none;';

    }else{
        document.getElementById(p2_div_container_id).style.cssText = styleTampoonContainer;
    }
}

function handleSession(p1_email)
{
    if(p1_email.length > 5) //an email contains at least: x@x.xx
    {
        document.getElementById('return_from_handleSession').innerHTML = '<img src="../img/spinner.gif" style="border: none;" />';

        var oData = new FormData(document.forms.namedItem('the_form'));

        var oReq = new XMLHttpRequest();

        oReq.open('POST', './ajax/handleSession.php', true);

        oReq.onload = function(oEvent)
        {
            if (oReq.status === 200)
            {
                if(oReq.responseText.substr(0, 1) === 'e')
                {
                    document.getElementById('return_from_handleSession').innerHTML = oReq.responseText.substr(1);

                }else
                {
                    document.getElementById('return_from_handleSession').innerHTML = oReq.responseText;
                }

            }else
            {
                document.getElementById('return_from_handleSession').innerHTML = 'Error ' + oReq.status;
            }
        };

        oReq.send(oData);
    }
}

function sendResetPasswdLink(p1_email_rescue)
{
    if(p1_email_rescue.length > 5) //an email contains at least: x@x.xx
    {
        document.getElementById('return_from_sendResetPasswdLink').innerHTML = '<img src="../img/spinner.gif" style="border: none;" />';

        var oData = new FormData(document.forms.namedItem('recover_passwd'));

        var oReq = new XMLHttpRequest();

        oReq.open('POST', '../ajax/sendResetPasswdLink.php', true);

        oReq.onload = function(oEvent)
        {
            if (oReq.status === 200)
            {
                if(oReq.responseText.substr(0, 1) === 'e')
                {
                    document.getElementById('return_from_sendResetPasswdLink').innerHTML = '<a href="#" onclick="sendResetPasswdLink(document.getElementById(\'email_rescue\').value);">'+translations[locale][4]+'</a><br>';
                    document.getElementById('return_from_sendResetPasswdLink').innerHTML += oReq.responseText.substr(1);

                }else
                {
                    document.getElementById('return_from_sendResetPasswdLink').innerHTML = oReq.responseText;
                }

            }else
            {
                document.getElementById('return_from_sendResetPasswdLink').innerHTML = 'Error ' + oReq.status;
            }
        };

        oReq.send(oData);
    }
}

function updatePasswd()
{
    document.getElementById('return_from_updatePasswd').innerHTML = '<img src="../img/spinner.gif" style="border: none;" />';

    var oData = new FormData(document.forms.namedItem('the_form'));

    var oReq = new XMLHttpRequest();

    oReq.open('POST', '../ajax/updatePasswd.php', true);

    oReq.onload = function(oEvent)
    {
        if (oReq.status === 200)
        {
            if(oReq.responseText.substr(0, 1) === 'e')
            {
                document.getElementById('return_from_updatePasswd').innerHTML = oReq.responseText.substr(1);

            }else
            {
                document.getElementById('return_from_updatePasswd').innerHTML = oReq.responseText;
            }

        }else
        {
            document.getElementById('return_from_updatePasswd').innerHTML = 'Error ' + oReq.status;
        }
    };

  oReq.send(oData);
}

function array_rand(input, num_req) {
    //  discuss at: http://phpjs.org/functions/array_rand/
    // original by: Waldo Malqui Silva (http://waldo.malqui.info)
    //   example 1: array_rand( ['Kevin'], 1 );
    //   returns 1: 0

    var indexes = [];
    var ticks = num_req || 1;
    var checkDuplicate = function (input, value) {
        var exist = false,
            index = 0,
            il = input.length;
        while (index < il) {
            if (input[index] === value) {
                exist = true;
                break;
            }
            index++;
        }
        return exist;
    };

    if (Object.prototype.toString.call(input) === '[object Array]' && ticks <= input.length) {
        while (true) {
            var rand = Math.floor((Math.random() * input.length));
            if (indexes.length === ticks) {
                break;
            }
            if (!checkDuplicate(indexes, rand)) {
                indexes.push(rand);
            }
        }
    } else {
        indexes = null;
    }

    return ((ticks == 1) ? indexes.join() : indexes);
}

function func_num_args () {
    // http://kevin.vanzonneveld.net
    // +   original by: Brett Zamir (http://brett-zamir.me)
    // %        note 1: May not work in all JS implementations
    // *     example 1: function tmp_a () {return func_num_args();}
    // *     example 1: tmp_a('a', 'b');
    // *     returns 1: 2
    if (!arguments.callee.caller) {
        try {
            throw new Error('Either you are using this in a browser which does not support the "caller" property or you are calling this from a global context');
            //return false;
        } catch (e) {
            return false;
        }
    }

    return arguments.callee.caller.arguments.length;
}