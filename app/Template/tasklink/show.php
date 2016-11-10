<?php if (! empty($links)): ?>
<div class="page-header">
    <h2><?= t('Links') ?></h2>
</div>
<table id="links">
    <tr>
        <th class="column-20"><?= t('Label') ?></th>
        <th class="column-30"><?= t('Task') ?></th>
        <th><?= t('Column') ?></th>
        <th><?= t('Assignee') ?></th>
        <?php if (! isset($not_editable)): ?>
            <th><?= t('Action') ?></th>
        <?php endif ?>
    </tr>
    <?php foreach ($links as $label => $grouped_links): ?>
        <?php $hide_td = false ?>
        <?php foreach ($grouped_links as $link): ?>
        <tr>
            <?php if (! $hide_td): ?>
                <td rowspan="<?= count($grouped_links) ?>"><?= t('This task') ?> <strong><?= t($label) ?></strong></td>
                <?php $hide_td = true ?>
            <?php endif ?>

            <td>
                <?php if (! isset($not_editable)): ?>
                    <?= $this->url->link(
                        $this->e('#'.$link['task_id'].' '.$link['title']),
                        'task',
                        'show',
                        array('task_id' => $link['task_id'], 'project_id' => $link['project_id']),
                        false,
                        $link['is_active'] ? '' : 'task-link-closed'
                    ) ?>
                <?php else: ?>
                    <?= $this->url->link(
                        $this->e('#'.$link['task_id'].' '.$link['title']),
                        'task',
                        'readonly',
                        array('task_id' => $link['task_id'], 'token' => $project['token']),
                        false,
                        $link['is_active'] ? '' : 'task-link-closed'
                    ) ?>
                <?php endif ?>

                <br/>

                <?php if (! empty($link['task_time_spent'])): ?>
                    <strong><?= $this->e($link['task_time_spent']).'h' ?></strong> <?= t('spent') ?>
                <?php endif ?>

                <?php if (! empty($link['task_time_estimated'])): ?>
                    <strong><?= $this->e($link['task_time_estimated']).'h' ?></strong> <?= t('estimated') ?>
                <?php endif ?>
            </td>
            <td><?= $this->e($link['column_title']) ?></td>
            <td>
                <?php if (! empty($link['task_assignee_username'])): ?>
                    <?php if (! isset($not_editable)): ?>
                        <?= $this->url->link($this->e($link['task_assignee_name'] ?: $link['task_assignee_username']), 'user', 'show', array('user_id' => $link['task_assignee_id'])) ?>
                    <?php else: ?>
                        <?= $this->e($link['task_assignee_name'] ?: $link['task_assignee_username']) ?>
                    <?php endif ?>
                <?php endif ?>
            </td>
            <?php if (! isset($not_editable)): ?>
            <td>
                <ul>
                    <li><?= $this->url->link(t('Edit'), 'tasklink', 'edit', array('link_id' => $link['id'], 'task_id' => $task['id'], 'project_id' => $task['project_id'])) ?></li>
                    <li><?= $this->url->link(t('Remove'), 'tasklink', 'confirm', array('link_id' => $link['id'], 'task_id' => $task['id'], 'project_id' => $task['project_id'])) ?></li>
                </ul>
            </td>
            <?php endif ?>
        </tr>
        <?php endforeach ?>
    <?php endforeach ?>
</table>

<?php if (! isset($not_editable) && isset($link_label_list)): ?>
    <form action="<?= $this->url->href('tasklink', 'save', array('task_id' => $task['id'], 'project_id' => $task['project_id'])) ?>" method="post" autocomplete="off">

        <?= $this->form->csrf() ?>
        <?= $this->form->hidden('task_id', array('task_id' => $task['id'])) ?>
        <?= $this->form->hidden('opposite_task_id', array()) ?>

        <?= $this->form->select('link_id', $link_label_list, array(), array()) ?>

        <?= $this->form->text(
            'title',
            array(),
            array(),
            array(
                'required',
                'placeholder="'.t('Start to type task title...').'"',
                'title="'.t('Start to type task title...').'"',
                'data-dst-field="opposite_task_id"',
                'data-search-url="'.$this->url->href('app', 'autocomplete', array('exclude_task_id' => $task['id'])).'"',
            ),
            'task-autocomplete') ?>

        <input type="submit" value="<?= t('Add') ?>" class="btn btn-blue"/>
    </form>
<?php endif ?>

<?php endif ?>

<!---------------------------------------------------------------------------------------------------------->
<!-- MANTIS LINKS -- Makoy -->
<!---------------------------------------------------------------------------------------------------------->

<div class="xtask-show-details" id="sr-rel-wrap">
	<br />
	<p style="font-weight: bold; font-size: 22px;">Mantis Relationships</p>
	<hr style="border-top: dotted 0.5px; color:lightgray;">
	<div id="mantis-content">
	<?php
		
	?>
	<?php
		$servername = DB_HOSTNAME;
		$username = DB_USERNAME;
		$password = DB_PASSWORD;
		$dbname = DB_NAME;
		try {
			$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$stmt = $conn->prepare("SELECT id, title, cost, activity, client, sr_no FROM tasks WHERE id =" . $task['id']);
			$stmt->execute();
			$stmt->setFetchMode(PDO::FETCH_OBJ);

			// set the resulting array to associative
			
			while ($row = $stmt->fetch()) {
				if (!empty($row->sr_no)) {
					$sr_no = substr($row->sr_no, strpos($row->sr_no, "=") + 1);

					// echo'<li>';

					echo ' <b>• Task SR ID</b>: <a target="_blank" href="' . $row->sr_no . '">' . $sr_no . '</a><br />';

					// echo'</li>';

				}

				define("KB_SR_ID", $row->sr_no);
				define("TITLE", $row->title);
				define("CLIENT", $row->client);
			}
		}

		catch(PDOException $e) {
			echo "Error: " . $e->getMessage();
		}

		$conn = null;
	?>
	
	<?php
		try {
			$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$stmt = $conn->prepare("SELECT name FROM users WHERE id =" . $task['owner_id']);
			$stmt->execute();
			$stmt->setFetchMode(PDO::FETCH_OBJ);
			// set the resulting array to associative
			
			/*
			if($stmt->fetch()==0){
				echo "Unassigned";
			}
			while ($row = $stmt->fetch()) {
					echo "Asignee Name: " .$row->name;
					break;
			}
			*/
			
		}
		catch(PDOException $e) {
			echo "Error: " . $e->getMessage();
		}
		$conn = null;
	?><!--//FETCH ASIGNEE NAME-->
	
	<?php
		$g_hostname = 'localhost';
		$g_db_type = 'mysql';
		$g_database_name = '_mantis_db';
		// $g_database_name = 'cpisrcom_mantis_db';
		$g_db_username = 'root';
		$g_db_password = 'ilovecpi';
		// $g_db_password = 'mantis';

		/*-----OUTPUT RELATED SRS----Makoy-----*/
		try {
			$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$query = "SELECT id, task_id, rel_sr, sr_link FROM task_has_rel_sr WHERE task_id =" . $task['id'];
			$stmt2 = $conn->prepare($query);
			$stmt2->execute();
			$exist = $stmt2->setFetchMode(PDO::FETCH_OBJ);

			// $display = true;

			$stmt_rel = $conn->prepare("SELECT id, task_id, rel_sr, sr_link FROM task_has_rel_sr WHERE task_id =" . $task['id']);
			$stmt_rel->bindParam(1, $_GET['id'], PDO::PARAM_INT);
			$stmt_rel->execute();
			$sr_rel_exist = $stmt_rel->fetch(PDO::FETCH_ASSOC);

			// SELECT SUMMARY FROM SR TABLE

			$conn_summ = new PDO("mysql:host=$g_hostname;dbname=$g_database_name", $g_db_username, $g_db_password);
			$conn_summ->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			if ($sr_rel_exist) {
				echo "<br /><table style='border:1px black;'>";
				echo "<tr><th>SR ID</th><th>Summary</th><th>Action</th></tr>";
				$count = 1;
				$scount = 1;
				while ($row2 = $stmt2->fetch()) {
					if (!empty($row2->rel_sr)) {
						$stmt_summ = $conn_summ->prepare("SELECT id, summary FROM mantis_bug_table WHERE id =" . $row2->rel_sr);

						// var_dump("SELECT id, summary FROM mantis_bug_table WHERE id =". $stmt2->fetch()->rel_sr);break;

						$stmt_summ->bindParam(1, $_GET['id'], PDO::PARAM_INT);
						$stmt_summ->execute();
						$sr_summ = $stmt_summ->fetch(PDO::FETCH_ASSOC);
						$sr_link = $row2->sr_link;
						$summary = $sr_summ["summary"];
						echo '	<tr id="sr_row_'. $row2->rel_sr .'">';
						echo "		<td width='10%'>";
						echo '			<a target="_blank" href="' . $row2->sr_link . '"> ' . $row2->rel_sr . '</a> ';
						/*DELETE SR ID*/
						echo "		</td>";
						echo "		<td width='70%'>";
						echo $summary;
						echo "		</td>";
						echo "		<td width='20%'>";
						//echo '<form method="POST" style="display:inline"><input class="click_delete" id="delete_sr_' . $row2->rel_sr . '" type="submit" title="Delete SR' . $row2->rel_sr . '" value="Remove" style="font-weight:bold;font-size:11px;color:red;background:none!important;border:none; padding:0!important;cursor: pointer;"><input type="hidden" name="delete_sr" value="' . $row2->rel_sr . '"></form>';
						echo '<div class="btn_remove_srlink" id="x_'.$row2->rel_sr.'"><form style="display:inline"><input type="button" title="Delete SR' . $row2->rel_sr . '" value="Remove" style="font-weight:bold;font-size:11px;color:red;background:none!important;border:none; padding:0!important;cursor: pointer;"><input type="hidden" name="delete_sr" value="' . $row2->rel_sr . '"></form></div>';
						echo '<div class="confirm_remove_srlink" id="x_'.$row2->rel_sr.'">Delete SR <strong>'. $row2->rel_sr .'</strong> relation? <form action="#sr-rel-wrap" method="POST" style="display:inline"><br>[ <input class="click_delete" id="delete_sr_' . $row2->rel_sr . '" type="submit" title="Delete SR' . $row2->rel_sr . '" value="Yes" style="font-weight:bold;font-size:11px;color:red;background:none!important;border:none; padding:0!important;cursor: pointer;"> ]<input type="hidden" name="delete_sr" value="' . $row2->rel_sr . '"></form><form style="display:inline"> [ <input class="click_cancel" id="delete_sr_' . $row2->rel_sr . '" type="button" title="Cancel" value="No" style="font-weight:bold;font-size:11px;color:black;background:none!important;border:none; padding:0!important;cursor: pointer;"> ]</form></div>';
						echo "		</td>";
						echo "	</tr>";

						// echo "</li>";
						// echo'</ul>';

					}
					$count = 0;
					$scount = 0;
				}
				echo "</table>";
			}
		}

		catch(PDOException $e) {
			echo "Error: " . $e->getMessage();
		}
		
		$conn_summ = null;
		
		// END SELECT SUMMARY FROM SR TABLE
		
		// 192.10.10.103/srms/view.php?id= 17625 //Format

		/*-----RELATE TO MANTIS-----Makoy-----*/
		echo '<div id="add-sr-frm">';
		echo '<form action="#add-sr-frm" method="POST" style="display:inline">';
		echo 'Relate to SR ID <input id="sr_id_field" type="number" name="sr_no" placeholder="SR #">';
		echo ' <input id="add_sr_btn" class="btn btn-blue" type="submit" value="Add">';
		echo ' <div class="blank_err" style="display: inline; color: red; font-size:14px;"> Please enter a valid SR number.</div>';
		echo ' <div class="exist_err" style="display: inline; color: red; font-size:14px;"> SR Relationship already exist.</div>';
		echo '</form>';
	
		if (!empty($_POST["sr_no"])) {
			$sr_no = $_POST["sr_no"];
			$kb_sr_id = substr(KB_SR_ID, strpos(KB_SR_ID, "=") + 1); //Extract SR ID from link.
			
			if($sr_no != $kb_sr_id){
				
				define("INPUT_SR_ID", $_POST["sr_no"]);
				$sr_link = "http://192.10.10.103/srms/view.php?id=" . $sr_no;
				try {

					// Check if SR bug ID exist

					try {
						$conn_srms = new PDO("mysql:host=$g_hostname;dbname=$g_database_name", $g_db_username, $g_db_password);
						$conn_srms->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

						// $query_srms = "SELECT id, summary FROM mantis_bug_table WHERE id =". $sr_no;
						// $stmt_srms= $conn_srms->prepare($query_srms);
						// $stmt_srms->execute();
						// $sr_id_exist = $stmt_srms->setFetchMode(PDO::FETCH_OBJ);
						// $srms_summary = $stmt_srms->fetch()->summary;

						$stmt7 = $conn_srms->prepare("SELECT id FROM mantis_bug_table WHERE id =" . $sr_no);
						$stmt7->bindParam(1, $_GET['id'], PDO::PARAM_INT);
						$stmt7->execute();
						$sr_id_exist = $stmt7->fetch(PDO::FETCH_ASSOC);
						if ($sr_id_exist) {
							$stmt5 = $conn->prepare("SELECT * FROM `task_has_rel_sr` WHERE task_id=" . $task['id'] . " AND rel_sr=" . $_POST["sr_no"]);
							$stmt5->execute();
							$stmt5->setFetchMode(PDO::FETCH_OBJ);

							// set the resulting array to associative

							if (empty($stmt5->fetch())) {
								$stmt_ins = $conn->prepare('INSERT INTO `task_has_rel_sr`(task_id, rel_sr, sr_link) VALUES (' . $task['id'] . ',' . $sr_no . ',"' . $sr_link . '")');
								$stmt_ins->execute();
								
								//Insert to PROJECT_ACTIVITIES table
								
								foreach ($_SESSION['user'] as $name => $value){
									if ($name == "id"){
										$id = $value;
									}
									if ($name == "name"){
										$asignee_name = $value;
									}
								}
								
								$pa_project_id = $task['project_id'];
								$pa_task_id = $task['id'];
								$pa_creator_id = $id;
								$pa_event_name = "task.update";		
								$pa_date_creation = time();
								
								$data = '{"task":
											{
												"id":"'.$pa_task_id.'"
												,"title":""
												,"project_id":"'.$pa_project_id.'"
												,"date_creation":"'.$pa_date_creation.'"
											},
										"changes":
											{
												"sr_relation":"'.$sr_no.'"
											}
										}';
								
								$stmt_ins = $conn->prepare('INSERT INTO `project_activities`(date_creation, event_name, creator_id, project_id, task_id, data) VALUES (' . $pa_date_creation . ',"' . $pa_event_name . '",' . $pa_creator_id . ',' . $pa_project_id .',' . $pa_task_id .',"' . mysql_real_escape_string($data) .'")');
								$stmt_ins->execute();
								//END -- Insert to PROJECT_ACTIVITIES table
								
								//--------------------------------------------------------ADD TO MANTIS RELATIONSHIP TABLE -- BEGIN
								//Change the credentials accordingly.
								
								$src_sr = $kb_sr_id;
								$des_sr = $sr_no;
								$rel_type = 1; //1=Relationship
								
								try {
									$mantis_conn = new PDO("mysql:host=".MANTIS_SERVERNAME.";dbname=".MANTIS_DBNAME."", MANTIS_USERNAME, MANTIS_PASSWORD);
									$mantis_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
									
									$mantis_stmt_ins = $mantis_conn->prepare('INSERT INTO `mantis_bug_relationship_table`(source_bug_id, destination_bug_id, relationship_type) VALUES('.$src_sr.', '.$des_sr.', '.$rel_type.')');
									$mantis_stmt_ins->execute();
									header('Refresh: 0');
									
								}
									catch(PDOException $e) {
									echo "Error: " . $e->getMessage();
								}
								//--------------------------------------------------------ADD TO MANTIS RELATIONSHIP TABLE -- END
								
								// ADD RELATION TO RELATED KANBAN Task - START ---------------------------
							
							try {
								$stmt2 = $conn->prepare('SELECT id, sr_no FROM `tasks` WHERE sr_no = \'http://192.10.10.103/srms/view.php?id=' . INPUT_SR_ID . '\'');
								
								$stmt2->execute();
								$stmt2->setFetchMode(PDO::FETCH_OBJ);

								// set the resulting array to associative

								while ($row = $stmt2->fetch()) {
									if (!empty($row->sr_no)) {
										$sr_no2 = substr($row->sr_no, strpos($row->sr_no, "=") + 1);
										$kb_sr_id = substr(KB_SR_ID, strpos(KB_SR_ID, "=") + 1); //Extract SR ID from link.
										try {
											$stmt5 = $conn->prepare('SELECT * FROM `task_has_rel_sr` WHERE task_id=' . $row->id . ' AND rel_sr=' . $sr_no2);
											$stmt5->execute();
											
											$stmt5->setFetchMode(PDO::FETCH_OBJ);

											// set the resulting array to associative

											if (empty($stmt5->fetch())) {
												$stmt_ins = $conn->prepare("INSERT INTO `task_has_rel_sr`(task_id, rel_sr, sr_link) VALUES (" . $row->id . "," . $kb_sr_id . ",'" . KB_SR_ID . "')");
												$stmt_ins->execute();
												//header('Refresh: 0');
												//break;
												
												$data_2 = '{"task":
																{
																	"id":"'.$row->id.'"
																	,"title":""
																	,"project_id":"'.$pa_project_id.'"
																	,"date_creation":"'.$pa_date_creation.'"
																},
														"changes":
																{
																	"sr_relation":"'.$kb_sr_id.'"
																}
														}';
												
												$stmt_ins_2 = $conn->prepare('INSERT INTO `project_activities`(date_creation, event_name, creator_id, project_id, task_id, data) VALUES (' . $pa_date_creation . ',"' . $pa_event_name . '",' . $pa_creator_id . ',' . $pa_project_id .',' . $row->id.',"' . mysql_real_escape_string($data_2) .'")');
												$stmt_ins_2->execute();
											}
										}

										catch(PDOException $e) {
											echo "Error: " . $e->getMessage();

											// $conn = null;

										}
									}
								}
							}

							catch(PDOException $e) {
								echo "Error: " . $e->getMessage();
							}

							// ADD RELATION TO RELATED KANBAN TASK - END ------------------------------
							//header('Refresh: 0');
							}
						}
						else{
							echo ' <div class="not_exist_err" style="display: inline; color: red; font-size:14px;"> SR number doesn\'t exist.</div>';
							
						}
					}

					catch(PDOException $e) {
						echo "Error: " . $e->getMessage();
					}

					$conn_srms = null;

					// Check if SR bug ID exist END

				}

				catch(PDOException $e) {
					echo "Error: " . $e->getMessage();

					// $conn = null;

				}
			}
			else{
				echo ' <div class="par_sr_err" style="display: inline; color: red; font-size:14px;"> Can\'t add parent SR number.</div>';
			}
			
		}
	
		if (!empty($_POST["delete_sr"])) {

			$rel_no = $_POST["delete_sr"];
			try {
				$stmt_ins = $conn->prepare("DELETE FROM task_has_rel_sr WHERE task_id=" . $task['id'] . " AND rel_sr =" . $rel_no);
				$stmt_ins->execute();
				//header('Refresh: 0');
				
				//Insert "DELETE SR" to PROJECT_ACTIVITIES table
				foreach ($_SESSION['user'] as $name => $value){
					if ($name == "id"){
						$id = $value;
					}
					if ($name == "name"){
						$asignee_name = $value;
					}
				}
				
				$pa_project_id = $task['project_id'];
				$pa_task_id = $task['id'];
				$pa_creator_id = $id;
				$pa_event_name = "task.update";		
				$pa_date_creation = time();
				$data = '{"task":
							{
								"id":"'.$pa_task_id.'"
								,"title":""
								,"project_id":"'.$pa_project_id.'"
								,"date_creation":"'.$pa_date_creation.'"
							},
						"changes":
							{
								"sr_delete":"'.$rel_no.'"
							}
						}';
				
				$stmt_ins = $conn->prepare('INSERT INTO `project_activities`(date_creation, event_name, creator_id, project_id, task_id, data) VALUES (' . $pa_date_creation . ',"' . $pa_event_name . '",' . $pa_creator_id . ',' . $pa_project_id .',' . $pa_task_id .',"' . mysql_real_escape_string($data) .'")');
				$stmt_ins->execute();
				
				//END -- Insert "DELETE SR" to PROJECT_ACTIVITIES table			
			}

			catch(PDOException $e) {
				echo "Error: " . $e->getMessage();

				// $conn = null;

			}
			
				
			
				// DELETE RELATION TO RELATED KANBAN Task - START ---------------------------
						
						try {
							$stmt2 = $conn->prepare('SELECT id, sr_no FROM `tasks` WHERE sr_no = \'http://192.10.10.103/srms/view.php?id=' . $rel_no . '\'');
							//var_dump($stmt2);break;
							$stmt2->execute();
							$stmt2->setFetchMode(PDO::FETCH_OBJ);

							// set the resulting array to associative

							while ($row = $stmt2->fetch()) {
								
								if (!empty($row->sr_no)) {
									$task_id = $row->id;
									$sr_no = $row->sr_no;
									$kb_sr_id = substr(KB_SR_ID, strpos(KB_SR_ID, "=") + 1); //Extract SR ID from link.
									try {
										$stmt5 = $conn->prepare('DELETE FROM task_has_rel_sr WHERE task_id=' . $task_id . ' AND rel_sr =' . $kb_sr_id);
										$stmt5->execute();
										//header('Refresh: 0');
										
										//BEGIN - Add REMOVE activity in the related task
										$data = '{"task":
														{
															"id":"'.$task_id.'"
															,"title":""
															,"project_id":"'.$pa_project_id.'"
															,"date_creation":"'.$pa_date_creation.'"
														},
												"changes":
														{
															"sr_delete":"'.$kb_sr_id.'"
														}
												}';
							
										$stmt_ins = $conn->prepare('INSERT INTO `project_activities`(date_creation, event_name, creator_id, project_id, task_id, data) VALUES (' . $pa_date_creation . ',"' . $pa_event_name . '",' . $pa_creator_id . ',' . $pa_project_id .',' . $task_id.',"' . mysql_real_escape_string($data) .'")');
										$stmt_ins->execute();
										
											//END - Add REMOVE activity in the related task
										
										//--------------------------------------------------------DELETE FROM MANTIS RELATIONSHIP TABLE -- BEGIN
										//Change the credentials accordingly.
										
										$src_sr = $kb_sr_id;
										$des_sr = $rel_no;
										$rel_type = 1; //1=Relationship
										
										$mantis_conn = new PDO("mysql:host=".MANTIS_SERVERNAME.";dbname=".MANTIS_DBNAME."", MANTIS_USERNAME, MANTIS_PASSWORD);
										$mantis_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
										
											$mantis_stmt_ins = $mantis_conn->prepare('DELETE FROM `mantis_bug_relationship_table` WHERE source_bug_id IN('.$src_sr.', '.$des_sr.') AND destination_bug_id IN('.$src_sr.', '.$des_sr.')');
											$mantis_stmt_ins->execute();
											//var_dump($mantis_stmt_ins );break;
										
										//--------------------------------------------------------REMOVE TO MANTIS RELATIONSHIP TABLE -- END
										
									}

									catch(PDOException $e) {
										echo "Error: " . $e->getMessage();

										// $conn = null;

									}
								}
							}
						}

						catch(PDOException $e) {
							echo "Error: " . $e->getMessage();
						}
		header('Refresh: 0');
					
					// DELETE RELATION TO RELATED KANBAN TASK - END ------------------------------
			
		}
		$conn = null;
	?>
	</div><br>
	
	
	</div>
	<hr style="border-top: dotted 0.5px; color:lightgray;">
	<br>
	
</div>