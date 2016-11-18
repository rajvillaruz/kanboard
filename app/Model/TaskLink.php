<?php

namespace Model;

use SimpleValidator\Validator;
use SimpleValidator\Validators;
use PDO;

/**
 * TaskLink model
 *
 * @package model
 * @author  Olivier Maridat
 * @author  Frederic Guillot
 */
class TaskLink extends Base
{
    /**
     * SQL table name
     *
     * @var string
     */
    const TABLE = 'task_has_links';

    /**
     * Get a task link
     *
     * @access public
     * @param  integer   $task_link_id   Task link id
     * @return array
     */
    public function getById($task_link_id)
    {
        return $this->db->table(self::TABLE)->eq('id', $task_link_id)->findOne();
    }

    /**
     * Get the opposite task link (use the unique index task_has_links_unique)
     *
     * @access public
     * @param  array     $task_link
     * @return array
     */
    public function getOppositeTaskLink(array $task_link)
    {
        $opposite_link_id = $this->link->getOppositeLinkId($task_link['link_id']);

        return $this->db->table(self::TABLE)
                    ->eq('opposite_task_id', $task_link['task_id'])
                    ->eq('task_id', $task_link['opposite_task_id'])
                    ->eq('link_id', $opposite_link_id)
                    ->findOne();
    }

    /**
     * Get all links attached to a task
     *
     * @access public
     * @param  integer   $task_id   Task id
     * @return array
     */
    public function getAll($task_id)
    {
		//--------------ADD ALL RELATIONSHIPS FROM MANTIS RELATIONSHIP TABLE TO TASK HAS LINKS-- BEGIN -- ROCHELLE VILLARUZ
		//Change the credentials accordingly.
			define("MANTIS_SERVERNAME" , "localhost");
			define("MANTIS_USERNAME" , "root");
			define("MANTIS_PASSWORD" , "ilovecpi");
			define("MANTIS_DBNAME" , "_mantis_db");
			$servername = DB_HOSTNAME;
			$username = DB_USERNAME;
			$password = DB_PASSWORD;
			$dbname = DB_NAME;

			$mantis_conn = new PDO("mysql:host=".MANTIS_SERVERNAME.";dbname=".MANTIS_DBNAME."", MANTIS_USERNAME, MANTIS_PASSWORD);
			$mantis_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$stmt1 = $mantis_conn->prepare("SELECT bug_id FROM mantis_custom_field_string_table WHERE field_id=23 AND value=" . $task_id);
			$stmt1->execute();
			$stmt1->setFetchMode(PDO::FETCH_OBJ);

			while ($row = $stmt1->fetch()) {
				if (!empty($row->bug_id)) {
					$src_sr = $row->bug_id;
					$stmt2 = $mantis_conn->prepare("SELECT * FROM `mantis_bug_relationship_table` WHERE source_bug_id=". $src_sr);
					$stmt2->execute();
					$stmt2->setFetchMode(PDO::FETCH_OBJ);
					while ($row1 = $stmt2->fetch()) {
						$des_bug_id = $row1->destination_bug_id;
                        $rel_mantis = $row1->relationship_type;

                        if($rel_mantis == 0)
                          $rel_type = 4;
                        if($rel_mantis == 1)
                          $rel_type = 1;
                        if($rel_mantis == 2)
                          $rel_type = 7;
                        if($rel_mantis == 3)
                          $rel_type = 6;

						$stmt3 = $mantis_conn->prepare("SELECT value FROM mantis_custom_field_string_table WHERE field_id=23 AND bug_id=" . $des_bug_id);
						$stmt3->execute();
						$stmt3->setFetchMode(PDO::FETCH_OBJ);

						while ($row2 = $stmt3->fetch()) {
							$opposite_task = $row2->value;
							$link = $this->db->table(self::TABLE)
									 ->eq('opposite_task_id', $opposite_task)
									 ->eq('task_id', $task_id)
									 ->eq('link_id', $rel_type)
									 ->findOne();

							if (empty($link)) {
								$this->db->table(self::TABLE)->insert(array(
											'task_id' => $task_id,
											'opposite_task_id' => $opposite_task,
											'link_id' => $rel_type
											));
							}
						}

                        if ($rel_mantis == 0) {

                                    $temp2  = $opposite_task;
                                    $opposite_task = $task_id;
                                    $link2 = $this->db->table(self::TABLE)
                                        ->eq('opposite_task_id', $opposite_task)
                                        ->eq('task_id', $temp2)
                                        ->eq('link_id', 5)
                                        ->findOne();
                                    if (empty($link2)) {
                                        $this->db->table(self::TABLE)->insert(array(
                                            'task_id' => $temp2,
                                            'opposite_task_id' => $opposite_task,
                                            'link_id' => 5
                                        ));
                                    }
                        }

                        if ($rel_mantis == 1) {
                                $temp2  = $opposite_task;
                                $opposite_task = $task_id;
                                $link2 = $this->db->table(self::TABLE)
                                        ->eq('opposite_task_id', $opposite_task)
                                        ->eq('task_id', $temp2)
                                        ->eq('link_id', 1)
                                        ->findOne();
                                    if (empty($link2)) {
                                        $this->db->table(self::TABLE)->insert(array(
                                            'task_id' => $temp2,
                                            'opposite_task_id' => $opposite_task,
                                            'link_id' => 1
                                        ));
                                    }
                        }


					}
				}
			}



		//--------------ADD ALL RELATIONSHIPS FROM MANTIS RELATIONSHIP TABLE TO TASK HAS LINKS-- END

        //--------------REMOVE RELATIONSHIPS FROM TASK_HAS_LINKS TABLE DELETED FROM MANTIS_BUG_RELATIONSHIP TABLE-- BEGIN -- ROCHELLE VILLARUZ

			$mantis_conn = new PDO("mysql:host=".MANTIS_SERVERNAME.";dbname=".MANTIS_DBNAME."", MANTIS_USERNAME, MANTIS_PASSWORD);
            $mantis_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $relationship_kb = $this->db->table(self::TABLE)->eq('task_id', $task_id)->findAll();

            foreach ($relationship_kb as $rel) {

                $stmt1 = $mantis_conn->prepare("SELECT bug_id FROM mantis_custom_field_string_table WHERE field_id=23 AND value=" . $rel['task_id']);
                $stmt1->execute();
                $stmt1->setFetchMode(PDO::FETCH_OBJ);

				$des_bug = 0;
				$src_bug = 0;
				$rel_mantis = 0;
                while ($row1 = $stmt1->fetch()) {
        			if (!empty($row1->bug_id))
        				$src_bug = $row1->bug_id;
        		}

                $stmt2 = $mantis_conn->prepare("SELECT bug_id FROM mantis_custom_field_string_table WHERE field_id=23 AND value=" . $rel['opposite_task_id']);
                $stmt2->execute();
                $stmt2->setFetchMode(PDO::FETCH_OBJ);

                while ($row2 = $stmt2->fetch()) {
        			if (!empty($row2->bug_id))
        				$des_bug = $row2->bug_id;
        		}

                if($rel['link_id'] == 1)
                  $rel_mantis = 1;
                if($rel['link_id'] == 4)
                  $rel_mantis = 0;
                if($rel['link_id'] == 6)
                  $rel_mantis = 3;
                if($rel['link_id'] == 7)
                  $rel_mantis = 2;
				if($rel['link_id'] == 5) {
                    $rel_mantis = 0;
                    $temp= $des_bug;
                    $des_bug = $src_bug;
                    $src_bug = $temp;
                }

                $stmt3 = $mantis_conn->prepare("SELECT * FROM `mantis_bug_relationship_table` WHERE source_bug_id=". $src_bug . " AND destination_bug_id=". $des_bug . " AND relationship_type=". $rel_mantis);
                $stmt3->execute();
                $stmt3->setFetchMode(PDO::FETCH_OBJ);
                $isOppositeEmpty = true;

                if($rel_mantis == 1) {
                    $stmt4 = $mantis_conn->prepare("SELECT * FROM `mantis_bug_relationship_table` WHERE source_bug_id=". $des_bug . " AND destination_bug_id=". $src_bug . " AND relationship_type=". $rel_mantis);
                    $stmt4->execute();
                    $stmt4->setFetchMode(PDO::FETCH_OBJ);
                    if (!empty($stmt4->fetch())) {
                        $isOppositeEmpty = false;
                    }
                }

				if (empty($stmt3->fetch()) && $isOppositeEmpty ) {
                    $this->db
                         ->table(self::TABLE)
                         ->eq('id', $rel['id'])->remove();
                }

            }

		//--------------REMOVE RELATIONSHIPS FROM TASK_HAS_LINKS TABLE DELETED FROM MANTIS_BUG_RELATIONSHIP TABLE-- END

        return $this->db
                    ->table(self::TABLE)
                    ->columns(
                        self::TABLE.'.id',
                        self::TABLE.'.opposite_task_id AS task_id',
                        Link::TABLE.'.label',
                        Task::TABLE.'.title',
                        Task::TABLE.'.is_active',
                        Task::TABLE.'.project_id',
                        Task::TABLE.'.time_spent AS task_time_spent',
                        Task::TABLE.'.time_estimated AS task_time_estimated',
                        Task::TABLE.'.owner_id AS task_assignee_id',
                        User::TABLE.'.username AS task_assignee_username',
                        User::TABLE.'.name AS task_assignee_name',
                        Board::TABLE.'.title AS column_title'
                    )
                    ->eq(self::TABLE.'.task_id', $task_id)
                    ->join(Link::TABLE, 'id', 'link_id')
                    ->join(Task::TABLE, 'id', 'opposite_task_id')
                    ->join(Board::TABLE, 'id', 'column_id', Task::TABLE)
                    ->join(User::TABLE, 'id', 'owner_id', Task::TABLE)
                    ->asc(Link::TABLE.'.id')
                    ->desc(Board::TABLE.'.position')
                    ->desc(Task::TABLE.'.is_active')
                    ->asc(Task::TABLE.'.position')
                    ->asc(Task::TABLE.'.id')
                    ->findAll();
    }

    /**
     * Get all links attached to a task grouped by label
     *
     * @access public
     * @param  integer   $task_id   Task id
     * @return array
     */
    public function getAllGroupedByLabel($task_id)
    {
        $links = $this->getAll($task_id);
        $result = array();

        foreach ($links as $link) {

            if (! isset($result[$link['label']])) {
                $result[$link['label']] = array();
            }

            $result[$link['label']][] = $link;
        }

        return $result;
    }

    /**
     * Create a new link
     *
     * @access public
     * @param  integer   $task_id            Task id
     * @param  integer   $opposite_task_id   Opposite task id
     * @param  integer   $link_id            Link id
     * @return integer                       Task link id
     */
    public function create($task_id, $opposite_task_id, $link_id)
    {
        $this->db->startTransaction();

        // Get opposite link
        $opposite_link_id = $this->link->getOppositeLinkId($link_id);

        // Create the original task link
        $this->db->table(self::TABLE)->insert(array(
            'task_id' => $task_id,
            'opposite_task_id' => $opposite_task_id,
            'link_id' => $link_id,
        ));

        $task_link_id = $this->db->getLastId();

        // Create the opposite task link
        $this->db->table(self::TABLE)->insert(array(
            'task_id' => $opposite_task_id,
            'opposite_task_id' => $task_id,
            'link_id' => $opposite_link_id,
        ));

		//--------------ADD TO MANTIS RELATIONSHIP TABLE -- BEGIN------------- Rochelle Villaruz

		//Change the credentials accordingly.

		define("MANTIS_SERVERNAME" , "localhost");
        define("MANTIS_USERNAME" , "root");
        define("MANTIS_PASSWORD" , "ilovecpi");
        define("MANTIS_DBNAME" , "_mantis_db");

		$src_sr = 0;
		$des_sr = 0;
        if($link_id == 4)
            $rel_type = 0;
        if($link_id == 6)
            $rel_type = 3;
        if($link_id == 7)
            $rel_type = 2;
		if($link_id == 5)
            $rel_type = 4;
		if($link_id == 1)
            $rel_type = 1;

        $mantis_conn = new PDO("mysql:host=".MANTIS_SERVERNAME.";dbname=".MANTIS_DBNAME."", MANTIS_USERNAME, MANTIS_PASSWORD);
        $mantis_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt1 = $mantis_conn->prepare("SELECT bug_id FROM mantis_custom_field_string_table WHERE field_id=23 AND value=" . $task_id);
        $stmt1->execute();
		$stmt1->setFetchMode(PDO::FETCH_OBJ);
        while ($row = $stmt1->fetch()) {
			if (!empty($row->bug_id))
				$src_sr = $row->bug_id;
		}

		$stmt2 = $mantis_conn->prepare("SELECT bug_id FROM mantis_custom_field_string_table WHERE field_id=23 AND value=" .  $opposite_task_id);
		$stmt2->execute();
		$stmt2->setFetchMode(PDO::FETCH_OBJ);
		while ($row = $stmt2->fetch()) {
			if (!empty($row->bug_id))
				$des_sr = $row->bug_id;
		}

		if($src_sr != 0 && $des_sr != 0) {
			$mantis_stmt_ins = $mantis_conn->prepare('INSERT INTO `mantis_bug_relationship_table`(source_bug_id, destination_bug_id, relationship_type) VALUES('.$src_sr.', '.$des_sr.', '.$rel_type.')');
			$mantis_stmt_ins->execute();
			$date = date("Y-m-d H:i:s");
			$mantis_stmt_history_ins = $mantis_conn->prepare('INSERT INTO `mantis_bug_history_table`(user_id, bug_id, date_modified, new_value, type) VALUES(275,'.$src_sr.', "'.$date.'", '.$des_sr.', 18)');
			$mantis_stmt_history_ins->execute();
		}

        //---------------ADD TO MANTIS RELATIONSHIP TABLE -- END

        $this->db->closeTransaction();

        return (int) $task_link_id;
    }

    /**
     * Update a task link
     *
     * @access public
     * @param  integer   $task_link_id          Task link id
     * @param  integer   $task_id               Task id
     * @param  integer   $opposite_task_id      Opposite task id
     * @param  integer   $link_id               Link id
     * @return boolean
     */
    public function update($task_link_id, $task_id, $opposite_task_id, $link_id)
    {
        $this->db->startTransaction();

        // Get original task link
        $task_link = $this->getById($task_link_id);

        // Find opposite task link
        $opposite_task_link = $this->getOppositeTaskLink($task_link);

        // Get opposite link
        $opposite_link_id = $this->link->getOppositeLinkId($link_id);

        // Update the original task link
        $rs1 = $this->db->table(self::TABLE)->eq('id', $task_link_id)->update(array(
            'task_id' => $task_id,
            'opposite_task_id' => $opposite_task_id,
            'link_id' => $link_id,
        ));

        // Update the opposite link
        $rs2 = $this->db->table(self::TABLE)->eq('id', $opposite_task_link['id'])->update(array(
            'task_id' => $opposite_task_id,
            'opposite_task_id' => $task_id,
            'link_id' => $opposite_link_id,
        ));

        $this->db->closeTransaction();

        return $rs1 && $rs2;
    }

    /**
     * Remove a link between two tasks
     *
     * @access public
     * @param  integer   $task_link_id
     * @return boolean
     */
    public function remove($task_link_id)
    {
        $this->db->startTransaction();

        $link = $this->getById($task_link_id);
        $link_id = $this->link->getOppositeLinkId($link['link_id']);

        $this->db->table(self::TABLE)->eq('id', $task_link_id)->remove();

        $this->db
            ->table(self::TABLE)
            ->eq('opposite_task_id', $link['task_id'])
            ->eq('task_id', $link['opposite_task_id'])
            ->eq('link_id', $link_id)->remove();

		//---------------------DELETE FROM MANTIS RELATIONSHIP TABLE -BEGIN------------- ROCHELLE VILLARUZ

			define("MANTIS_SERVERNAME" , "localhost");
			define("MANTIS_USERNAME" , "root");
			define("MANTIS_PASSWORD" , "ilovecpi");
			define("MANTIS_DBNAME" , "_mantis_db");
			$rel_type = 0; //1=Relationship

				$mantis_conn = new PDO("mysql:host=".MANTIS_SERVERNAME.";dbname=".MANTIS_DBNAME."", MANTIS_USERNAME, MANTIS_PASSWORD);
				$mantis_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$stmt1 = $mantis_conn->prepare("SELECT bug_id FROM mantis_custom_field_string_table WHERE field_id=23 AND value=" . $link['task_id']);
				$stmt1->execute();
				$stmt1->setFetchMode(PDO::FETCH_OBJ);

				// set the resulting array to associative

				while ($row = $stmt1->fetch()) {
					if (!empty($row->bug_id))
						$src_sr = $row->bug_id;
				}

				$stmt2 = $mantis_conn->prepare("SELECT bug_id FROM mantis_custom_field_string_table WHERE field_id=23 AND value=" .  $link['opposite_task_id']);
				$stmt2->execute();
				$stmt2->setFetchMode(PDO::FETCH_OBJ);

				// set the resulting array to associative

				while ($row = $stmt2->fetch()) {
					if (!empty($row->bug_id))
						$des_sr = $row->bug_id;
				}

				$mantis_stmt_ins = $mantis_conn->prepare('DELETE FROM `mantis_bug_relationship_table` WHERE source_bug_id IN('.$src_sr.', '.$des_sr.') AND destination_bug_id IN('.$src_sr.', '.$des_sr.')');
				$mantis_stmt_ins->execute();
				$date = date("Y-m-d H:i:s");
				$mantis_stmt_history_ins = $mantis_conn->prepare('INSERT INTO `mantis_bug_history_table`(user_id, bug_id, date_modified, new_value, type) VALUES(275,'.$src_sr.', "'.$date.'", '.$des_sr.', 19)');
				$mantis_stmt_history_ins->execute();
			//--------------------- DELETE FROM MANTIS RELATIONSHIP TABLE -------- END

        $this->db->closeTransaction();

        return true;
    }

    /**
     * Common validation rules
     *
     * @access private
     * @return array
     */
    private function commonValidationRules()
    {
        return array(
            new Validators\Required('task_id', t('Field required')),
            new Validators\Required('opposite_task_id', t('Field required')),
            new Validators\Required('link_id', t('Field required')),
            new Validators\NotEquals('opposite_task_id', 'task_id', t('A task cannot be linked to itself')),
            new Validators\Exists('opposite_task_id', t('This linked task id doesn\'t exists'), $this->db->getConnection(), Task::TABLE, 'id')
        );
    }

    /**
     * Validate creation
     *
     * @access public
     * @param  array   $values           Form values
     * @return array   $valid, $errors   [0] = Success or not, [1] = List of errors
     */
    public function validateCreation(array $values)
    {
        $v = new Validator($values, $this->commonValidationRules());

        return array(
            $v->execute(),
            $v->getErrors()
        );
    }

    /**
     * Validate modification
     *
     * @access public
     * @param  array   $values           Form values
     * @return array   $valid, $errors   [0] = Success or not, [1] = List of errors
     */
    public function validateModification(array $values)
    {
        $rules = array(
            new Validators\Required('id', t('Field required')),
        );

        $v = new Validator($values, array_merge($rules, $this->commonValidationRules()));

        return array(
            $v->execute(),
            $v->getErrors()
        );
    }
}
