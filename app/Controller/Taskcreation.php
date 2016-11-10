<?php

namespace Controller;

use Model\Project as ProjectModel;

/**
 * Task Creation controller
 *
 * @package  controller
 * @author   Frederic Guillot
 */
class Taskcreation extends Base
{
    /**
     * Display a form to create a new task
     *
     * @access public
     */
    public function create(array $values = array(), array $errors = array())
    {
        $project = $this->getProject();
        $method = $this->request->isAjax() ? 'render' : 'layout';
        $swimlanes_list = $this->swimlane->getList($project['id'], false, true);

        if (empty($values)) {

            $values = array(
                'swimlane_id' => $this->request->getIntegerParam('swimlane_id', key($swimlanes_list)),
                'column_id' => $this->request->getIntegerParam('column_id'),
                'color_id' => $this->request->getStringParam('color_id', $this->color->getDefaultColor()),
                'owner_id' => $this->request->getIntegerParam('owner_id'),
                'another_task' => $this->request->getIntegerParam('another_task'),
            );
        }

        $this->response->html($this->template->$method('task_creation/form', array(
            'ajax' => $this->request->isAjax(),
            'errors' => $errors,
            'values' => $values + array('project_id' => $project['id']),
            'projects_list' => $this->project->getListByStatus(ProjectModel::ACTIVE),
            'columns_list' => $this->board->getColumnsList($project['id']),
            'users_list' => $this->projectPermission->getMemberList($project['id'], true, false, true),
            'colors_list' => $this->color->getList(),
            'categories_list' => $this->category->getList($project['id']),
            'swimlanes_list' => $swimlanes_list,
            'date_format' => $this->config->get('application_date_format'),
            'date_formats' => $this->dateParser->getAvailableFormats(),
            'title' => $project['name'].' &gt; '.t('New task')
        )));
    }

    /**
     * Validate and save a new task
     *
     * @access public
     */
    public function save()
    {
        $project = $this->getProject();
        $values = $this->request->getValues();

        list($valid, $errors) = $this->taskValidator->validateCreation($values);

        if ($valid) {

            if ($this->taskCreation->create($values)) {
                $this->session->flash(t('Task created successfully.'));
				
				// Assign the task to a user in MANTIS --- Rochelle Villaruz
				define("MANTIS_SERVERNAME" , "localhost");
				define("MANTIS_USERNAME" , "root");
				define("MANTIS_PASSWORD" , "ilovecpi");
				define("MANTIS_DBNAME" , "_mantis_db");
				$mantis_conn = new PDO("mysql:host=".MANTIS_SERVERNAME.";dbname=".MANTIS_DBNAME."", MANTIS_USERNAME, MANTIS_PASSWORD);
				$mantis_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$stmt1 = $mantis_conn->prepare("SELECT bug_id FROM mantis_custom_field_string_table WHERE field_id=23 AND value=" . $values['id']);
				$stmt1->execute();
				$stmt1->setFetchMode(PDO::FETCH_OBJ);

				while ($row = $stmt1->fetch()) {
					if (!empty($row->bug_id)) {
						$src_sr = $row->bug_id;
						$stmt2 = $mantis_conn->prepare("SELECT * FROM mantis_bug_table WHERE id=". $src_sr);
						$stmt2->execute();
						$stmt2->setFetchMode(PDO::FETCH_OBJ);


						while ($row1 = $stmt2->fetch()) {
							$user = $this->db->table('users')->eq('id', $values['owner_id'])->findOne();
							$stmt3 = $mantis_conn->prepare("SELECT * FROM mantis_user_table WHERE username='". $user['username']."'");
							$stmt3->execute();
							$stmt3->setFetchMode(PDO::FETCH_OBJ);
							while ($row2 = $stmt3->fetch()) {
								if (!empty($row2->id)) {
									$user_id = $row2->id;
									$stmt4 = $mantis_conn->prepare("UPDATE mantis_bug_table SET handler_id=". $user_id ." WHERE id=" . $src_sr);
									$stmt4->execute();              
									$stmt4->setFetchMode(PDO::FETCH_OBJ);
								}
							}
							
						}
					}
				}
                if (isset($values['another_task']) && $values['another_task'] == 1) {
                    unset($values['title']);
                    unset($values['description']);
                    $this->response->redirect($this->helper->url->to('taskcreation', 'create', $values));
                }
                else {
                    $this->response->redirect($this->helper->url->to('board', 'show', array('project_id' => $project['id'])));
                }
            }
            else {
                $this->session->flashError(t('Unable to create your task.'));
            }
        }

        $this->create($values, $errors);
    }
}
