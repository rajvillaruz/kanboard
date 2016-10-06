<div class="sidebar" style="width:500px">
    <h2><?= t('List of Reports') ?></h2>
    <ul>
		<li>
			<a href="?controller=report&action=index&report=1">Average time taken to approve or disapprove validated SR</a>
        </li>
		<li>
			<a href="?controller=report&action=index&report=2">No. Of client SR requests approved/ Total no. of validated SRs</a>
        </li>
		<li>
			<a href="?controller=report&action=index&report=3">No. of times SA returned the SR to developer</a>
        </li>
		<li>
			<a href="?controller=report&action=index&report=4">No. of times TL returned the SR to developer</a>
        </li>
		<li>
			<a href="?controller=report&action=index&report=5">Ave. time taken by QA to approve the SR</a>
        </li>
		<li>
			<a href="?controller=report&action=index&report=6">Ave. time from SR validation approval to assignment</a>
        </li>
		<li>
			<a href="?controller=report&action=index&report=7">Total development time</a>
        </li>
		<li>
			<a href="?controller=report&action=index&report=8">Total time to resolve SR</a>
        </li>
		<li>
			<a href="?controller=report&action=index&report=9">No. of SRs with failed testing</a>
        </li>
    </ul>
    <div class="sidebar-collapse"><a href="#" title="<?= t('Hide sidebar') ?>"><i class="fa fa-chevron-left"></i></a></div>
    <div class="sidebar-expand" style="display: none"><a href="#" title="<?= t('Expand sidebar') ?>"><i class="fa fa-chevron-right"></i></a></div>
</div>


