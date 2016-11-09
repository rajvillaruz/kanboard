<?php if (! $ajax): ?>
<div class="page-header">
    <ul>
        <li><i class="fa fa-th fa-fw"></i><?= $this->url->link(t('Back to the board'), 'board', 'show', array('project_id' => $values['project_id'])) ?></li>
    </ul>
</div>
<?php else: ?>
<div class="page-header">
    <h2><?= t('New task') ?></h2>
</div>
<?php endif ?>

<section id="task-section">
<form method="post" action="<?= $this->url->href('taskcreation', 'save', array('project_id' => $values['project_id'])) ?>" autocomplete="off">

    <?= $this->form->csrf() ?>

    <div class="form-column">
        <?= $this->form->label(t('Title'), 'title') ?>
        <?= $this->form->text('title', $values, $errors, array('autofocus', 'required', 'maxlength="200"', 'tabindex="1"'), 'form-input-large') ?><br/>

        <?= $this->form->label(t('Description'), 'description') ?>

        <div class="form-tabs">
            <div class="write-area">
                <?= $this->form->textarea('description', $values, $errors, array('placeholder="'.t('Leave a description').'"', 'tabindex="2"')) ?>
            </div>
            <div class="preview-area">
                <div class="markdown"></div>
            </div>
            <ul class="form-tabs-nav">
                <li class="form-tab form-tab-selected">
                    <i class="fa fa-pencil-square-o fa-fw"></i><a id="markdown-write" href="#"><?= t('Write') ?></a>
                </li>
                <li class="form-tab">
                    <a id="markdown-preview" href="#"><i class="fa fa-eye fa-fw"></i><?= t('Preview') ?></a>
                </li>
            </ul>
        </div>

        <div class="form-help"><a href="http://kanboard.net/documentation/syntax-guide" target="_blank" rel="noreferrer"><?= t('Write your text in Markdown') ?></a></div>

        <?php if (! isset($duplicate)): ?>
            <?= $this->form->checkbox('another_task', t('Create another task'), 1, isset($values['another_task']) && $values['another_task'] == 1) ?>
        <?php endif ?>
    </div>

    <div class="form-column">
        <?= $this->form->hidden('project_id', $values) ?>

        <?= $this->form->label(t('Assignee'), 'owner_id') ?>
        <?= $this->form->select('owner_id', $users_list, $values, $errors, array('tabindex="3"')) ?><br/>

        <?= $this->form->label(t('Category'), 'category_id') ?>
        <?= $this->form->select('category_id', $categories_list, $values, $errors, array('tabindex="4"')) ?><br/>

        <?php if (! (count($swimlanes_list) === 1 && key($swimlanes_list) === 0)): ?>
        <?= $this->form->label(t('Swimlane'), 'swimlane_id') ?>
        <?= $this->form->select('swimlane_id', $swimlanes_list, $values, $errors, array('tabindex="5"')) ?><br/>
        <?php endif ?>

        <?= $this->form->label(t('Column'), 'column_id') ?>
        <?= $this->form->select('column_id', $columns_list, $values, $errors, array('tabindex="6"')) ?><br/>

        <?= $this->form->label(t('Color'), 'color_id') ?>
        <?= $this->form->select('color_id', $colors_list, $values, $errors, array('tabindex="7"')) ?><br/>

        <?= $this->form->label(t('Complexity'), 'score') ?>
        <?= $this->form->number('score', $values, $errors, array('tabindex="8"')) ?><br/>

        <?= $this->form->label(t('Original estimate'), 'time_estimated') ?>
        <?= $this->form->numeric('time_estimated', $values, $errors, array('tabindex="9"')) ?> <?= t('hours') ?><br/>

        <?= $this->form->label(t('Due Date'), 'date_due') ?>
        <?= $this->form->text('date_due', $values, $errors, array('placeholder="'.$this->text->in($date_format, $date_formats).'"', 'tabindex="10"'), 'form-date') ?><br/>
        <div class="form-help"><?= t('Others formats accepted: %s and %s', date('Y-m-d'), date('Y_m_d')) ?></div>
		
		<!--Makoy Enhancement-->
		
		<div class="gen-enh-new-task">
		<?php

						echo'<p>Cost Center</p>';
						echo'<select class="newCost" name="cost" id="newCost">';
							echo'	<option value="Unassigned">Unassigned</option>';
							echo'	<option value="Maintenance">Maintenance</option>';
							echo'	<option value="Development">Development</option>';
							echo'	<option value="Enhancement">Enhancement</option>';
							echo'	<option value="Implementation">Implementation</option>';
							echo'	<option value="Others">Others</option>';
						echo'</select><br/><br/>';

						echo'<p>Activity</p>';
						echo'<select name="activity" id="newActivity">';
							echo' <option value="Unassigned">Unassigned</option>';
						echo'</select>';
							
						echo'<br/><br/>';
						echo'<p>Client</p>';
						echo'<select name="client" id="newClient">';
								echo'<option value="Unassigned">Unassigned</option>';
								echo'<option value="All">All</option>';
								echo'<option value="UCPB">UCPB</option>';
								echo'<option value="FGIC">FGIC</option>';
								echo'<option value="PHILFIRE">PHILFIRE</option>';
								echo'<option value="RSIC">RSIC</option>';
								echo'<option value="MAC">MAC</option>';
								echo'<option value="AUII">AUII</option>';
								echo'<option value="CIC/PNG">CIC/PNG</option>';
								echo'<option value="PNBGEN">PNBGEN</option>';
								echo'<option value="FLT PRIME">FLT PRIME</option>';
								echo'<option value="CPAIC">CPAIC</option>';
								echo'<option value="TPISC">TPISC</option>';
								echo'<option value="NIA">NIA</option>';
						echo'</select><br/>';
				
		?>
		<br/><br/>
		
		</div>
		
		<!--END MAKOY ENHANCEMENT-->
		
    </div>

    <div class="form-actions">
        <input type="submit" value="<?= t('Save') ?>" class="btn btn-blue" tabindex="11"/>
        <?= t('or') ?> <?= $this->url->link(t('cancel'), 'board', 'show', array('project_id' => $values['project_id']), false, 'close-popover') ?>
    </div>
</form>
</section>
