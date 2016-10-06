<?php

namespace Controller;
use PDO;
/**
 * Task Modification controller
 *
 * @package  controller
 * @author   Frederic Guillot
 */
class Taskmodification extends Base
{
    /**
     * Set automatically the start date
     *
     * @access public
     */
    public function start()
    {
        $task = $this->getTask();
        $this->taskModification->update(array('id' => $task['id'], 'date_started' => time()));
        $this->response->redirect($this->helper->url->to('task', 'show', array('project_id' => $task['project_id'], 'task_id' => $task['id'])));
    }

    /**
     * Update time tracking information
     *
     * @access public
     */
    public function time()
    {
        $task = $this->getTask();
        $values = $this->request->getValues();

        list($valid,) = $this->taskValidator->validateTimeModification($values);

        if ($valid && $this->taskModification->update($values)) {
            $this->session->flash(t('Task updated successfully.'));
        }
        else {
            $this->session->flashError(t('Unable to update your task.'));
        }

        $this->response->redirect($this->helper->url->to('task', 'show', array('project_id' => $task['project_id'], 'task_id' => $task['id'])));
    }

    /**
     * Edit description form
     *
     * @access public
     */
    public function description()
    {
        $task = $this->getTask();
        $ajax = $this->request->isAjax() || $this->request->getIntegerParam('ajax');

        if ($this->request->isPost()) {

            $values = $this->request->getValues();

            list($valid, $errors) = $this->taskValidator->validateDescriptionCreation($values);

            if ($valid) {

                if ($this->taskModification->update($values)) {
                    $this->session->flash(t('Task updated successfully.'));
                }
                else {
                    $this->session->flashError(t('Unable to update your task.'));
                }

                if ($ajax) {
                    $this->response->redirect($this->helper->url->to('board', 'show', array('project_id' => $task['project_id'])));
                }
                else {
                    $this->response->redirect($this->helper->url->to('task', 'show', array('project_id' => $task['project_id'], 'task_id' => $task['id'])));
                }
            }
        }
        else {
            $values = $task;
            $errors = array();
        }

        $params = array(
            'values' => $values,
            'errors' => $errors,
            'task' => $task,
            'ajax' => $ajax,
        );

        if ($ajax) {
            $this->response->html($this->template->render('task_modification/edit_description', $params));
        }
        else {
            $this->response->html($this->taskLayout('task_modification/edit_description', $params));
        }
    }

    /**
     * Display a form to edit a task
     *
     * @access public
     */
    public function edit(array $values = array(), array $errors = array())
    {
        $task = $this->getTask();
        $ajax = $this->request->isAjax();

        if (empty($values)) {
            $values = $task;
        }

        $this->dateParser->format($values, array('date_due'));

        $params = array(
            'values' => $values,
            'errors' => $errors,
            'task' => $task,
            'users_list' => $this->projectPermission->getMemberList($task['project_id']),
            'colors_list' => $this->color->getList(),
            'categories_list' => $this->category->getList($task['project_id']),
            'date_format' => $this->config->get('application_date_format'),
            'date_formats' => $this->dateParser->getAvailableFormats(),
            'ajax' => $ajax,
        );

        if ($ajax) {
            $this->response->html($this->template->render('task_modification/edit_task', $params));
        }
        else {
            $this->response->html($this->taskLayout('task_modification/edit_task', $params));
        }
    }

    /**
     * Validate and update a task
     *
     * @access public
     */
    public function update()
    {
        $task = $this->getTask();
        $values = $this->request->getValues();
		
	
		
        list($valid, $errors) = $this->taskValidator->validateModification($values);

        if ($valid) {

            if ($this->taskModification->update($values)) {
                $this->session->flash(t('Task updated successfully.'));
				
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
							if (!empty($row2 = $stmt3->fetch())) {
								if (!empty($row2->id)) {
									$user_id = $row2->id;
									$stmt4 = $mantis_conn->prepare("UPDATE mantis_bug_table SET handler_id=". $user_id ." WHERE id=" . $src_sr);
									$stmt4->execute();             
								}
							} else {
								$user_id = 0;
								$stmt4 = $mantis_conn->prepare("UPDATE mantis_bug_table SET handler_id=". $user_id ." WHERE id=" . $src_sr);
								$stmt4->execute();  
							}
							
						}
					}
				}

                if ($this->request->getIntegerParam('ajax')) {
                    $this->response->redirect($this->helper->url->to('board', 'show', array('project_id' => $task['project_id'])));
                }
                else {
                    $this->response->redirect($this->helper->url->to('task', 'show', array('project_id' => $task['project_id'], 'task_id' => $task['id'])));
                }
            }
            else {
                $this->session->flashError(t('Unable to update your task.'));
            }
        }

        $this->edit($values, $errors);
    }

    /**
     * Edit recurrence form
     *
     * @access public
     */
    public function recurrence()
    {
        $task = $this->getTask();

        if ($this->request->isPost()) {

            $values = $this->request->getValues();

            list($valid, $errors) = $this->taskValidator->validateEditRecurrence($values);

            if ($valid) {

                if ($this->taskModification->update($values)) {
                    $this->session->flash(t('Task updated successfully.'));
                }
                else {
                    $this->session->flashError(t('Unable to update your task.'));
                }

                $this->response->redirect($this->helper->url->to('task', 'show', array('project_id' => $task['project_id'], 'task_id' => $task['id'])));
            }
        }
        else {
            $values = $task;
            $errors = array();
        }

        $params = array(
            'values' => $values,
            'errors' => $errors,
            'task' => $task,
            'recurrence_status_list' => $this->task->getRecurrenceStatusList(),
            'recurrence_trigger_list' => $this->task->getRecurrenceTriggerList(),
            'recurrence_timeframe_list' => $this->task->getRecurrenceTimeframeList(),
            'recurrence_basedate_list' => $this->task->getRecurrenceBasedateList(),
        );

        $this->response->html($this->taskLayout('task_modification/edit_recurrence', $params));
    }
}