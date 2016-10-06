<?php
	 $values['repFunc'] = 'report6';
?>

<div class="page-header">
    <h2>
        <?= t('Ave. time from SR validation approval to assignment') ?>
    </h2>
</div>

<form method="get" action="?" autocomplete="off">
	<?= $this->form->hidden('controller', $values) ?>
    <?= $this->form->hidden('action', $values) ?>
	<?= $this->form->hidden('repFunc', $values) ?>
	
    <div class="form-actions">
        <input type="submit" value="<?= t('Print CSV') ?>" class="btn btn-blue"/>
    </div>
</form>