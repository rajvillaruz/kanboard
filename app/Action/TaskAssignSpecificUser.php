<?php

namespace Action;

use Model\Task;

/**
 * Assign a task to a specific user
 *
 * @package action
 * @author  Frederic Guillot
 */
class TaskAssignSpecificUser extends Base
{
    /**
     * Get the list of compatible events
     *
     * @access public
     * @return array
     */
    public function getCompatibleEvents()
    {
        return array(
            Task::EVENT_CREATE_UPDATE,
            Task::EVENT_MOVE_COLUMN,
        );
    }

    /**
     * Get the required parameter for the action (defined by the user)
     *
     * @access public
     * @return array
     */
    public function getActionRequiredParameters()
    {
        return array(
            'column_id' => t('Column'),
            'user_id' => t('Assignee'),
        );
    }

    /**
     * Get the required parameter for the event
     *
     * @access public
     * @return string[]
     */
    public function getEventRequiredParameters()
    {
        return array(
            'task_id',
            'column_id',
        );
    }

    /**
     * Execute the action (assign the given user)
     *
     * @access public
     * @param  array   $data   Event data dictionary
     * @return bool            True if the action was executed or false when not executed
     */
    public function doAction(array $data)
    {
        $values = array(
            'id' => $data['task_id'],
            'owner_id' => $this->getParam('user_id'),
        );
		
		while ($row = $stmt1->fetch()) {
            if (!empty($row->bug_id)) {
                $src_sr = $row->bug_id;
                $stmt2 = $mantis_conn->prepare("SELECT * FROM `mantis_bug_relationship_table` WHERE source_bug_id=". $src_sr);
                $stmt2->execute();
                $stmt2->setFetchMode(PDO::FETCH_OBJ);


                while ($row1 = $stmt2->fetch()) {
                    $user = DB::table('users')->where('id', $values['owner_id'])->first();
                    $stmt3 = $mantis_conn->prepare("UPDATE mantis_bug_table SET handler_id=". $user['username'] ."WHERE field_id=23 AND bug_id=" . $src_sr);
                    $stmt3->execute();
                    $stmt3->setFetchMode(PDO::FETCH_OBJ);
                }
            }
        }

        return $this->taskModification->update($values);
    }

    /**
     * Check if the event data meet the action condition
     *
     * @access public
     * @param  array   $data   Event data dictionary
     * @return bool
     */
    public function hasRequiredCondition(array $data)
    {
        return $data['column_id'] == $this->getParam('column_id');
    }
}
