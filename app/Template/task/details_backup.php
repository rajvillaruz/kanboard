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
					$stmt = $conn->prepare("SELECT id, cost, activity, client, sr_no, rel_sr FROM tasks WHERE id =". $task['id']);
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
						echo' Mantis SR ID: <a target="_blank" href="'. $row->sr_no .'">' . $sr_no . '</a>';
						echo'</li>';
						}
					}
				}
			catch(PDOException $e) {
				echo "Error: " . $e->getMessage();
			}
			//$conn = null; //Original code
			
			/*-----OUTPUT RELATED SRS----Makoy-----*/
			try{
			$query = "SELECT id, task_id, rel_sr, sr_link FROM task_has_rel_sr WHERE task_id =". $task['id'];
			$stmt2 = $conn->prepare($query);
					$stmt2->execute();
					
					$stmt2->setFetchMode(PDO::FETCH_OBJ);
					
					echo'<li>';
						echo "<ul>";
							echo "Related Mantis SR's:";
								while($row2 = $stmt2->fetch()) {
									if (!empty($row2->rel_sr)){
											$sr_link = $row2->sr_link ;
											echo "<li>";
								/*EDIT SR ID** echo '<form method="POST" style="display:inline"><input type="submit" value="Edit" style="font-size:11px;color:green;background:none!important;border:none; padding:0!important;border-bottom:1px solid #444; cursor: pointer;"><input type="hidden" name="edit_sr" value="Norway"></form> '; */
								/*DELETE SR ID*/ echo '<form method="POST" style="display:inline"><input class="click_delete" id="delete_sr_'. $row2->rel_sr .'" type="submit" value="Delete" style="font-size:11px;color:red;background:none!important;border:none; padding:0!important;border-bottom:1px solid #444; cursor: pointer;"><input type="hidden" name="delete_sr" value="'. $row2->rel_sr  .'"></form> ';
												echo'<a target="_blank" href="'. $row2->sr_link .'"> ' . $row2->rel_sr . '</a> ';
											echo "</li>";
									}
								}
						echo'</ul>';		
					echo"</li>";
				}
			
			catch(PDOException $e) {
				echo "Error: " . $e->getMessage();
			}
			
			//192.10.10.103/srms/view.php?id= 17625
			/*-----RELATE TO MANTIS-----Makoy-----*/
			
					echo'<li>';
						echo '<form method="POST" style="display:inline">';
							echo'Relate to SR ID: <input id="sr_id_field" type="number" name="sr_no" placeholder="SR#" style="font-size:16px; height:24px; width:60px;">';
							echo' <input id="add_sr_btn" type="submit" value="Add" style="width:40px;">';
						echo '</form>';
					echo'</li>';
			
			if (!empty($_POST["sr_no"])){
				$sr_no = $_POST["sr_no"];
				$sr_link = "http://192.10.10.103/srms/view.php?id=" .$sr_no;
				try {
				
					$stmt5 = $conn->prepare("SELECT * FROM `task_has_rel_sr` WHERE task_id=". $task['id'] . " AND rel_sr=". $_POST["sr_no"]);
					$stmt5->execute();
					
					$stmt5->setFetchMode(PDO::FETCH_OBJ);
					// set the resulting array to associative
					while(!empty($stmt5->fetch())==FALSE) {
						$stmt_ins = $conn->prepare("INSERT INTO task_has_rel_sr(task_id, rel_sr, sr_link) VALUES (". $task['id'] .",". $sr_no .",'". $sr_link ."')");
						$stmt_ins->execute();
						header('Refresh: 0');
						break;
						}
				}
				catch(PDOException $e) {
					echo "Error: " . $e->getMessage();
				//$conn = null;
				}
			}
			if (!empty($_POST["delete_sr"])){
			$rel_no = $_POST["delete_sr"];
				try {
						$stmt_ins = $conn->prepare("DELETE FROM task_has_rel_sr WHERE task_id=". $task['id'] ." AND rel_sr =". $rel_no);
						//var_dump($stmt_ins);
						$stmt_ins->execute();
						header('Refresh: 0');
				}
				catch(PDOException $e) {
					echo "Error: " . $e->getMessage();
				//$conn = null;
				}
			}
			$conn = null;
		?>
    </ul>
</div>
