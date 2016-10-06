<section id="main">
    <div class="page-header">
        <ul>
            <li style="font-size:125%;">
				Select report to generate.
            </li>
        </ul>
    </div>
	<table>
		<tr>
			<td style="width:500px; border:none;">
				<section class="sidebar-container" id="config-section">
					<?= $this->render('report/sidebar') ?>
				</section>
			</td>
			<td style="border:none;">
				<?php include 'reportPages.php';?>
			</td>
		</tr>
	</table>
</section>
