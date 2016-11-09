<div class="page-header">
    <h2><?= t('Edit a task') ?></h2>
</div>
<section id="task-section">
<form method="post" action="<?= $this->url->href('taskmodification', 'update', array('task_id' => $task['id'], 'project_id' => $task['project_id'], 'ajax' => $ajax)) ?>" autocomplete="off">

    <?= $this->form->csrf() ?>

    <div class="form-column">

        <?= $this->form->label(t('Title'), 'title') ?>
        <?= $this->form->text('title', $values, $errors, array('autofocus', 'required', 'maxlength="200"', 'tabindex="1"')) ?><br/>

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

    </div>

    <div class="form-column">
        <?= $this->form->hidden('id', $values) ?>
        <?= $this->form->hidden('project_id', $values) ?>

        <?= $this->form->label(t('Assignee'), 'owner_id') ?>
        <?= $this->form->select('owner_id', $users_list, $values, $errors, array('tabindex="3"')) ?><br/>

        <?= $this->form->label(t('Category'), 'category_id') ?>
        <?= $this->form->select('category_id', $categories_list, $values, $errors, array('tabindex="4"')) ?><br/>

        <?= $this->form->label(t('Color'), 'color_id') ?>
        <?= $this->form->select('color_id', $colors_list, $values, $errors, array('tabindex="5"')) ?><br/>

        <?= $this->form->label(t('Complexity'), 'score') ?>
        <?= $this->form->number('score', $values, $errors, array('tabindex="6"')) ?><br/>

        <?= $this->form->label(t('Due Date'), 'date_due') ?>
        <?= $this->form->text('date_due', $values, $errors, array('placeholder="'.$this->text->in($date_format, $date_formats).'"', 'tabindex="7"'), 'form-date') ?><br/>
        <div class="form-help"><?= t('Others formats accepted: %s and %s', date('Y-m-d'), date('Y_m_d')) ?></div>

         <?php
         	$cost_list = array('Unassigned' => 'Unassigned',
								'Maintenance' => 'Maintenance',
        						'Development' => 'Development',
								'Enhancement' => 'Enhancement',
								'Implementation' => 'Implementation',
								'Others' => 'Others');

			$activity_list[''] = array('Unassigned' => 'Unassigned');
			$activity_list['Unassigned'] = array('Unassigned' => 'Unassigned');

			$activity_list['Maintenance'] = array('Bug Fixing' => 'Bug Fixing',
											  'Consultation' => 'Consultation');

			$activity_list['Development'] = array('Internal SR' => 'Internal SR',
											  'Industry Enhancement' => 'Industry Enhancement');

			$activity_list['Enhancement'] = array('Specific Enhancement' => 'Specific Enhancement');

			$activity_list['Implementation'] = array('Configurable Document' => 'Configurable Document',
											  'GAP Analysis' => 'GAP Analysis',
                                              'Regression' => 'Regression',
											  'SIT' => 'SIT',
											  'UT' => 'UT',
											  'UAT' => 'UAT');

			$activity_list['Others'] = array('Meetings' => 'Meetings',
										 'Trainings' => 'Trainings',
										 'Tech Sessions' => 'Tech Sessions');

			$client_list = array('Unassigned' => 'Unassigned',
								'UCPB' => 'UCPB',
								'FGIC' => 'FGIC',
								'PHILFIRE' => 'PHILFIRE',
								'RSIC' => 'RSIC',
								'MAC' => 'MAC',
								'AUI' => 'AUI',
								'CIC/PNG' => 'CIC/PNG',
								'PNBGEN' => 'PNBGEN',
								'FLT PRIME' => 'FLT PRIME',
								'CPAIC' => 'CPAIC',
								'TPISC' => 'TPISC',
								'NIA' => 'NIA',
								'ALL' => 'ALL');

			function enhancement($post)
			{
				$conn = new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_NAME);

				$sql = "
					SELECT
						cost, activity, client
					FROM
						tasks
					WHERE
						id = '{$post['id']}'
				";

				$result = $conn->query($sql);
				$info = $result->fetch_assoc();

				return $info;
			}

			$info = enhancement($values);
			$values = array_merge($values, $info);
		?>

		<?= $this->form->label(t('Cost Center'), 'cost') ?>
		<div id='cost'><?= $this->form->select('cost', $cost_list, $values, $errors, array('tabindex="4"')) ?> <br/></div>

		<?= $this->form->label(t('Activity'), 'activity') ?>
        <div id='activity'><?= $this->form->select('activity', $activity_list[$values['cost']], $values, $errors, array('tabindex="4"')) ?> <br/></div>

        <?= $this->form->label(t('Client'), 'client') ?>
        <div id='client'><?= $this->form->select('client', $client_list, $values, $errors, array('tabindex="4"')) ?> <br/></div>
    </div>

    <div class="form-actions">
        <input type="submit" value="<?= t('Save') ?>" class="btn btn-blue" tabindex="10">
        <?= t('or') ?>
        <?php if ($ajax): ?>
            <?= $this->url->link(t('cancel'), 'board', 'show', array('project_id' => $task['project_id']), false, 'close-popover') ?>
        <?php else: ?>
            <?= $this->url->link(t('cancel'), 'task', 'show', array('task_id' => $task['id'], 'project_id' => $task['project_id'])) ?>
        <?php endif ?>
    </div>
</form>
</section>
