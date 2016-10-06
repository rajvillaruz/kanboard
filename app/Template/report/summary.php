<?php
	$title = '';
	$values['reportType'] = 0;
	$values['repFunc'] = 'report' . $_GET['report'];
	
	 switch (isset($_GET['report']) ? $_GET['report'] : '') {
		case '3' :
			$title = 'No. of times SA returned the SR to developer';
			break;
		case '4' :
			$title = 'No. of times TL returned the SR to developer';
			break;
		case '5' :
			$title = 'Ave. time taken by QA to approve the SR';
			break;
		case '7' :
			$title = 'Total development time';
			break;
	}
?>

<div class="page-header">
    <h2>
        <?= t($title) ?>
    </h2>
</div>

<p class="alert alert-info"><?= t('Enter parameter values:') ?></p>
	
	
<form method="get" action="?" autocomplete="off">
	Report Type:<br><br>
	<a href="?controller=report&action=index&report=<?= $_GET['report']; ?>&repType=0" style="text-decoration:none; color:black;">
		<input type="radio" id="rdoReportType" name="rdoSummary" value="0" checked="checked"/> Summary
	</a>
	<a href="?controller=report&action=index&report=<?= $_GET['report']; ?>&repType=1" style="text-decoration:none; color:black;">
		<input type="radio" id="rdoReportType" name="rdoDetailed" value="1"/> Detailed
	</a>
	<br><br>
	
	<?= $this->form->hidden('controller', $values) ?>
    <?= $this->form->hidden('action', $values) ?>
	<?= $this->form->hidden('reportType', $values) ?>
	<?= $this->form->hidden('repFunc', $values) ?>
	
    <div class="form-actions">
        <input type="submit" value="<?= t('Print CSV') ?>" class="btn btn-blue"/>
    </div>
</form>


	
		
