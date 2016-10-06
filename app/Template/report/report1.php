<?php
	 $values['repFunc'] = 'report1';
?>

<div class="page-header">
    <h2>
        <?= t('Average time taken to approve or disapprove validated SR') ?>
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