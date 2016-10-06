<?php
	 $values['repFunc'] = 'report8';
?>

<div class="page-header">
    <h2>
        <?= t('Total time to resolve SR') ?>
    </h2>
</div>

<p class="alert alert-info"><?= t('Enter Start Date and End date') ?></p>

<form method="get" action="?" autocomplete="off">
	<?= $this->form->hidden('controller', $values) ?>
    <?= $this->form->hidden('action', $values) ?>
	<?= $this->form->hidden('repFunc', $values) ?>

    <?= $this->form->label(t('Start Date'), 'from') ?>
    <?= $this->form->text('from', $values, $errors, array('required', 'placeholder="'.$this->text->in($date_format, $date_formats).'"'), 'form-date') ?><br/>

    <?= $this->form->label(t('End Date'), 'to') ?>
	<?= $this->form->text('to', $values, $errors, array('required', 'placeholder="'.$this->text->in($date_format, $date_formats).'"'), 'form-date') ?>
    
    <div class="form-help"><?= t('Others formats accepted: %s and %s', date('Y-m-d'), date('Y_m_d')) ?></div>
	
    <div class="form-actions">
        <input type="submit" value="<?= t('Print CSV') ?>" class="btn btn-blue"/>
    </div>
</form>