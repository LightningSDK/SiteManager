<div class="field-update">
<h2>Set a value for the field: <?= $configField; ?></h2>
<input type="hidden" name="field" id="configField" value="<?= $configField; ?>">
<input type="text" name="value" id="configValue" value="<?= $configValue; ?>">
<button type="submit" value="submit" class="button" onclick="lightning.modules.sitemanager.setConfigVal(this)">Save</button>
</div>
