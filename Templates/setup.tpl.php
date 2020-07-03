<?php foreach ($settings as $s): ?>

<style>
    .setting.set {
        background: #8de298;
    }
    .setting.notset {
        background: #e28d8d;
    }
</style>

<div class=" setting <?=$s['set'] ? 'set' : 'notset'; ?>">
    <i class="fa fa-warning"></i><div><?$s['text'];?></div>
</div>

<?php endforeach; ?>

