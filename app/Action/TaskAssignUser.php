<?php

namespace Action;

use Integration\GithubWebhook;
use Integration\BitbucketWebhook;

/**
 * Assign a task to someone
 *
 * @package action
 * @author  Frederic Guillot
 */
class TaskAssignUser extends Base
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
            GithubWebhook::EVENT_ISSUE_ASSIGNEE_CHANGE,
            BitbucketWebhook::EVENT_ISSUE_ASSIGNEE_CHANGE,
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
        return array();
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
            'owner_id',
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
            'owner_id' => $data['owner_id'],
        );

		<?php

namespace Action;

use Integration\GithubWebhook;
use Integration\BitbucketWebhook;

/**
 * Assign a task to someone
 *
 * @package action
 * @author  Frederic Guillot
 */
class TaskAssignUser extends Base
{
    const TABLE = 'users';
    /**
     * Get the list of compatible events
     *
     * @access public
     * @return array
     */
    public function getCompatibleEvents()
    {
        return array(
            GithubWebhook::EVENT_ISSUE_ASSIGNEE_CHANGE,
            BitbucketWebhook::EVENT_ISSUE_ASSIGNEE_CHANGE,
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
        return array();
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
            'owner_id',
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
            'owner_id' => $data['owner_id'],
        );

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
        return true;
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
        return true;
    }
}
