<div class="control-group form-field">
    <label class="control-label" for="rave_config_env_{$payment_id}">Environment:</label>
    <div class="controls">
      <select name="payment_data[processor_params][rave_env]" id="rave_config_env_{$payment_id}">
          <option value="staging"{if $processor_params.rave_env == "staging"}selected="selected"{/if}>Staging</option>
          <option value="live" {if $processor_params.rave_env == "live"}selected="selected"{/if}>{__("Live")}</option>
      </select>
    </div>

</div>
<div class="control-group">
    <label class="control-label" for="rave_config_pk_{$payment_id}">Secret Key:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][rave_sk]" id="rave_config_pk_{$payment_id}" value="{$processor_params.rave_sk}"   size="60">
    </div>
</div>
<div class="control-group">
    <label class="control-label" for="rave_config_sk_{$payment_id}">Public Key:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][rave_pk]" id="rave_config_sk_{$payment_id}" value="{$processor_params.rave_pk}"   size="60">
    </div>
</div>
<div class="control-group">
    <label class="control-label" for="rave_config_logo_{$payment_id}">Rave Logo:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][rave_logo]" id="rave_config_logo_{$payment_id}" value="{$processor_params.rave_logo}"   size="60">
    </div>
</div>

<div class="control-group form-field">
    <label class="control-label" for="rave_config_country_{$payment_id}">Country:</label>
    <div class="controls">
      <select name="payment_data[processor_params][rave_country]" id="rave_config_country_{$payment_id}">
          <option value="NG"{if $processor_params.rave_country == "NG"}selected="selected"{/if}>Nigeria</option>
          <option value="GH" {if $processor_params.rave_country == "GH"}selected="selected"{/if}>Ghana</option>
          <option value="KE" {if $processor_params.rave_country == "KE"}selected="selected"{/if}>Kenya</option>
      </select>
    </div>
</div>

<div class="control-group form-field">
    <label class="control-label" for="rave_config_payment_method_{$payment_id}">Payment Method:</label>
    <div class="controls">
      <select name="payment_data[processor_params][payment_method]" id="rave_config_payment_method_{$payment_id}">
          <option value="both"{if $processor_params.payment_method == "both"}selected="selected"{/if}>All</option>
          <option value="card" {if $processor_params.payment_method == "card"}selected="selected"{/if}>Cards Only</option>
          <option value="account" {if $processor_params.payment_method == "account"}selected="selected"{/if}>Account Only</option>
          <option value="ussd" {if $processor_params.payment_method == "ussd"}selected="selected"{/if}>USSD Only</option>
      </select>
    </div>
</div>
