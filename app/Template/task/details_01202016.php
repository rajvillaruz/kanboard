<div class="color-<?= $task['color_id'] ?> task-show-details">
    <h2><?= $this->e('#'.$task['id'].' '.$task['title']) ?></h2>
    <?php if ($task['score']): ?>
        <span class="task-score"><?= $this->e($task['score']) ?></span>
    <?php endif ?>
    <ul>
        <?php if ($task['reference']): ?>
        <li>
            <strong><?= t('Reference: %s', $task['reference']) ?></strong>
        </li>
        <?php endif ?>
        <?php if (! empty($task['swimlane_name'])): ?>
        <li>
            <?= t('Swimlane: %s', $task['swimlane_name']) ?>
        </li>
        <?php endif ?>
        <li>
            <?= dt('Created on %B %e, %Y at %k:%M %p', $task['date_creation']) ?>
        </li>
        <?php if ($task['date_modification']): ?>
        <li>
            <?= dt('Last modified on %B %e, %Y at %k:%M %p', $task['date_modification']) ?>
        </li>
        <?php endif ?>
        <?php if ($task['date_completed']): ?>
        <li>
            <?= dt('Completed on %B %e, %Y at %k:%M %p', $task['date_completed']) ?>
        </li>
        <?php endif ?>
        <?php if ($task['date_started']): ?>
        <li>
            <?= dt('Started on %B %e, %Y', $task['date_started']) ?>
        </li>
        <?php endif ?>
        <?php if ($task['date_due']): ?>
        <li>
            <strong><?= dt('Must be done before %B %e, %Y', $task['date_due']) ?></strong>
        </li>
        <?php endif ?>
        <?php if ($task['time_estimated']): ?>
        <li>
            <?= t('Estimated time: %s hours', $task['time_estimated']) ?>
        </li>
        <?php endif ?>
        <?php if ($task['time_spent']): ?>
        <li>
            <?= t('Time spent: %s hours', $task['time_spent']) ?>
        </li>
        <?php endif ?>
        <?php if ($task['creator_username']): ?>
        <li>
            <?= t('Created by %s', $task['creator_name'] ?: $task['creator_username']) ?>
        </li>
        <?php endif ?>
        <li>
            <strong>
            <?php if ($task['assignee_username']): ?>
                <?= t('Assigned to %s', $task['assignee_name'] ?: $task['assignee_username']) ?>
            <?php else: ?>
                <?= t('There is nobody assigned') ?>
            <?php endif ?>
            </strong>
        </li>
        <li>
            <?= t('Column on the board:') ?>
            <strong><?= $this->e($task['column_title']) ?></strong>
            (<?= $this->e($task['project_name']) ?>)
            <?= dt('since %B %e, %Y at %k:%M %p', $task['date_moved']) ?>
        </li>
        <li><?= t('Task position:').' '.$this->e($task['position']) ?></li>
        <?php if ($task['category_name']): ?>
        <li>
            <?= t('Category:') ?> <strong><?= $this->e($task['category_name']) ?></strong>
        </li>
        <?php endif ?>
        <li>
            <?php if ($task['is_active'] == 1): ?>
                <?= t('Status is open') ?>
            <?php else: ?>
                <?= t('Status is closed') ?>
            <?php endif ?>
        </li>
        <?php if ($project['is_public']): ?>
        <li>
            <?= $this->url->link(t('Public link'), 'task', 'readonly', array('task_id' => $task['id'], 'token' => $project['token']), false, '', '', true) ?>
        </li>
        <?php endif ?>

        <?php if (! isset($not_editable) && $task['recurrence_status'] != \Model\Task::RECURRING_STATUS_NONE): ?>
        <li>
            <strong><?= t('Recurring information') ?></strong>
            <?= $this->render('task/recurring_info', array(
                'task' => $task,
                'recurrence_trigger_list' => $recurrence_trigger_list,
                'recurrence_timeframe_list' => $recurrence_timeframe_list,
                'recurrence_basedate_list' => $recurrence_basedate_list,
            )) ?>
        </li>
        <?php endif ?>
		
		<?php
		
		$servername = DB_HOSTNAME;
		$username = DB_USERNAME;
		$password = DB_PASSWORD;
		$dbname = DB_NAME;
			
			try {
					$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
					$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
					$stmt = $conn->prepare("SELECT id, cost, activity, client, sr_no FROM tasks WHERE id =". $task['id']);
					$stmt->execute();
					
					$stmt->setFetchMode(PDO::FETCH_OBJ);
					// set the resulting array to associative
					while($row = $stmt->fetch()) {
					if (!empty($row->cost)){
						echo'<li>';
						echo' Cost Center: '. $row->cost;
						echo'</li>';
					}
					if (!empty($row->activity)){
						echo'<li>';
						echo' Activity: '. $row->activity;
						echo'</li>';
					}
					if (!empty($row->client)){
						echo'<li>';
						echo' Client: '. $row->client;
						echo'</li>';
						}
					if (!empty($row->sr_no)){
						$sr_no = substr($row->sr_no, strpos($row->sr_no, "=") + 1);
						echo'<li>';
						echo' Mantis SR ID: <a href="'. $row->sr_no .'">' . $sr_no . '</a>';
						echo'</li>';
						}
					}
				}
			catch(PDOException $e) {
				echo "Error: " . $e->getMessage();
			}
			$conn = null;
		?>
    </ul>
</div>
