<style>
.nps-button {
    cursor:pointer;
    background-image: url("./img/nps-btn.gif");
    background-position: center top;
    background-repeat: repeat-x;
    border-radius: 4px 4px 4px 4px;
    color: #FFFFFF;
    font-size: 16px;
    height: 45px;
    line-height: 45px;
    text-align: center;
    text-shadow: 0 1px 1px #1B5C8B;
    vertical-align: middle;
    width: 280px;
}
.nps-button:hover, a.nps-button:active {
    background-position: center bottom;
    color: #FFF;
    text-decoration: none;
}
#psp_Product, #psp_NumPayments {
    border-color: grey;
}
#form_container div label {
    display: block;
    float: left;
    height: 32px;
    padding: 8px 4px;
    margin: 4px;
    width: 100px;
}
#psp_NumPayments, #psp_Product {
    height: 32px;
    padding: 4px;
    margin: 4px;
    width: 200px;
}
#form_container {
    text-align: left;
    margin: auto;
    width: 350px;
}
#loader_message {
    text-align: left;
    margin: 5px auto;
    width: 96px;
}
#loader_message img {
    float: left;
    margin: 0 5px 0 0px;
}
</style>

<script>
    var retry = 0;
    function retryRequestRetrieveInstallments() {
        if (retry < 2) {
            setTimeout(requestRetrieveInstallments, 3000);
            retry++;
        } else {
            $('#psp_Product').val("");
            $('#psp_Product').prop('disabled', false);
            $('#psp_NumPayments').prop('disabled', true);
            $('#psp_NumPayments').html('');
            $('#psp_NumPayments').append('<option value="">{l s='--Please Select--' mod='nps'}</option>');
            $('#installments').hide();
            $('#loader_message').hide();
            $('#error_message').show();
        }
    }
    function requestRetrieveInstallments() {
        $.ajax({
            type: 'POST',
            headers: {
                'cache-control': 'no-cache'
            },
            url: 'payment.php' + '?retrieve-installments',
            data: 'id_payment_product=' + parseInt($('#psp_Product').val()),
            beforeSend: function () {
                $('#psp_Product').prop('disabled', true);
                $('#psp_NumPayments').prop('disabled', true);
                $('#installments').hide();
                $('#loader_message').show();
                $('#error_message').hide();
            },
            success: function (response) {
                try {
                    var json = $.parseJSON(response);
                    if ((json instanceof Array) && (json.length > 0)) {
                        $('#psp_NumPayments').html('');
                        $('#psp_NumPayments').append('<option value="">{l s='--Please Select--' mod='nps'}</option>');

                        $.each(json, function (key, value) {
                            var desc = value.qty + ' (+' + parseFloat(value.rate, 2).toFixed(2) + '%)';
                            $('#psp_NumPayments').append('<option value="' + value.qty + '">' + desc + '</option>');
                        });

                        $('#psp_Product').prop('disabled', false);
                        $('#psp_NumPayments').prop('disabled', false);
                        $('#installments').show();
                        $('#loader_message').hide();
                    } else {
                        retryRequestRetrieveInstallments();
                    }
                } catch (e) {
                    retryRequestRetrieveInstallments();
                }
            },
            error: function () {
                retryRequestRetrieveInstallments();
            }
        });
    }
    function retrieveInstallments() {
        if ($('#psp_Product').val()) {
            $('#psp_Product').css('border-color', 'gray');
            $('#psp_NumPayments').css('border-color', 'gray');
            retry = 0;
            requestRetrieveInstallments();
        } else {
            $('#psp_Product').prop('disabled', false);
            $('#psp_NumPayments').prop('disabled', true);
            $('#psp_NumPayments').html('');
            $('#psp_NumPayments').append('<option value="">{l s='--Please Select--' mod='nps'}</option>');
            $('#installments').hide();
            $('#loader_message').hide();
            $('#error_message').hide();
        }
    }

    function submitFormNps() {
        if ($('#psp_Product').val()) {
            $('#psp_Product').css('border-color', 'gray');
        } else {
            $('#psp_Product').css('border-color', 'red');
        }
        if ($('#psp_NumPayments').val()) {
            $('#psp_NumPayments').css('border-color', 'gray');
        } else {
            $('#psp_NumPayments').css('border-color', 'red');
        }
        if ($('#psp_Product').val() && $('#psp_NumPayments').val()) {
            $('#formNps').submit();
        }
    }

    window.onload = function () {
        if ($('#psp_Product').val()) {
            retrieveInstallments();
        }
    }
</script>

<div style="text-align: center;">
    {if isset($error)}
        <p style="color: red;">{l s='An error occured, please try again later.' mod='nps'}</p>
    {else}
        <p style="font-size: 15px;">{l s='You are going to be redirected to NPS\'s website for your payment.' mod='nps'}</p>
        <form action="payment.php?create-pending-order" method="POST" id="formNps">  
            {foreach from=$npsRedirection item=value}
                <input type="hidden" value="{$value.value}" name="{$value.name}"/>
            {/foreach}

            <div id="form_container">
                <div>
                    <label>{l s='Card' mod='nps'}</label>
                    <select name="psp_Product" id="psp_Product" onchange="retrieveInstallments()">
                        <option value="">{l s='--Please Select--' mod='nps'}</option>
                        {foreach from=$npsProducts item=value}
                            <option value="{$value.value}">{$value.name}</option>
                        {/foreach}
                    </select>

                    <div id="loader_message" style="display: none;">
                        <img src="./img/ajax-loader.gif" alt="Loading..." title="Loading...">
                        <p>{l s='Loading...' mod='nps'}<p>
                    </div>
                </div>

                <div id="installments" style="display: none;">
                    <label>{l s='Installments' mod='nps'}</label>
                    <select name="psp_NumPayments" id="psp_NumPayments">
                        <option value="">{l s='--Please Select--' mod='nps'}</option>
                    </select>  
                </div>
                    
                <div style="clear:both;"></div>
            </div>

            <div id="error_message" style="display: none;">
                <p>{l s='At this moment the selected option is not available. Please try again later.' mod='nps'}<p>
            </div>

            <hr/>
            <input class="nps-button" id="npsSubmit" type="button" value="{l s='Please click here' mod='nps'}" onclick="submitFormNps()"/>
        </form>
    {/if}
</div>
