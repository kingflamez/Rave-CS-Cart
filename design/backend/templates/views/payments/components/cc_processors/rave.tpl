<div class="control-group">
    <label class="control-label" for="rave_tpk_{$payment_id}">Test Public Key:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][rave_tpk]" id="rave_tpk_{$payment_id}" value="{$processor_params.rave_tpk}"   size="60">
    </div>
</div>
<div class="control-group">
    <label class="control-label" for="rave_tsk_{$payment_id}">Test Secret Key:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][rave_tsk]" id="rave_tsk_{$payment_id}" value="{$processor_params.rave_tsk}"   size="60">
    </div>
</div>
<div class="control-group">
    <label class="control-label" for="rave_lpk_{$payment_id}">Live Public Key:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][rave_lpk]" id="rave_lpk_{$payment_id}" value="{$processor_params.rave_lpk}"   size="60">
    </div>
</div>
<div class="control-group">
    <label class="control-label" for="rave_lsk_{$payment_id}">Live Secret Key:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][rave_lsk]" id="rave_lsk_{$payment_id}" value="{$processor_params.rave_lsk}"   size="60">
    </div>
</div>
{* <input type="hidden" name="payment_data[processor_params][iframe_mode]" value="Y"   size="60"> *}

<div class="control-group form-field">
    <label class="control-label" for="rave_country_{$payment_id}">Country:</label>
    <div class="controls">
      <select name="payment_data[processor_params][rave_country]" id="rave_country_{$payment_id}">
          <option value="NG"{if $processor_params.rave_country == "NG"}selected="selected"{/if}>Nigeria</option>
          <option value="GH" {if $processor_params.rave_country == "GH"}selected="selected"{/if}>Ghana</option>
          <option value="ZA" {if $processor_params.rave_country == "ZA"}selected="selected"{/if}>South Africa</option>
          <option value="KE" {if $processor_params.rave_country == "KE"}selected="selected"{/if}>Kenya</option>
      </select>
    </div>
</div>

<div class="control-group form-field">
    <label class="control-label" for="rave_currency_{$payment_id}">Currency:</label>
    <div class="controls">
      <select name="payment_data[processor_params][rave_currency]" id="rave_currency_{$payment_id}">
          <option value="NGN"{if $processor_params.rave_currency == "NGN"}selected="selected"{/if}>Naira</option>
          <option value="USD" {if $processor_params.rave_currency == "USD"}selected="selected"{/if}>US Dollars</option>
          <option value="EUR" {if $processor_params.rave_currency == "EUR"}selected="selected"{/if}>Euros</option>
          <option value="GBP" {if $processor_params.rave_currency == "GBP"}selected="selected"{/if}>Pounds Sterling</option>
          <option value="GHS" {if $processor_params.rave_currency == "GHS"}selected="selected"{/if}>Ghana Cedis</option>
          <option value="KES" {if $processor_params.rave_currency == "KES"}selected="selected"{/if}>Kenya Shillings</option>
          <option value="ZAR" {if $processor_params.rave_currency == "ZAR"}selected="selected"{/if}>South African Rands</option>
      </select>
    </div>
</div>

<div class="control-group form-field">
    <label class="control-label" for="rave_payment_method_{$payment_id}">Payment Method:</label>
    <div class="controls">
      <select name="payment_data[processor_params][rave_payment_method]" id="rave_payment_method_{$payment_id}">
          <option value="both"{if $processor_params.rave_payment_method == "both"}selected="selected"{/if}>All</option>
          <option value="card" {if $processor_params.rave_payment_method == "card"}selected="selected"{/if}>Cards only</option>
          <option value="account" {if $processor_params.rave_payment_method == "account"}selected="selected"{/if}>Account Only</option>
          <option value="ussd" {if $processor_params.rave_payment_method == "ussd"}selected="selected"{/if}>USSD only</option>
      </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="rave_logo_{$payment_id}">Logo URL:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][rave_logo]" id="rave_logo_{$payment_id}" value="{$processor_params.rave_logo}" >
    </div>
</div>

<div class="control-group form-field">
    <label class="control-label" for="iframe_mode_{$payment_id}">Mode:</label>
    <div class="controls">
      <select name="payment_data[processor_params][rave_mode]" id="iframe_mode_{$payment_id}">
          <option value="test"{if $processor_params.rave_mode == "test"}selected="selected"{/if}>Test</option>
          <option value="live" {if $processor_params.rave_mode == "live"}selected="selected"{/if}>Live</option>
      </select>
    </div>

</div>
