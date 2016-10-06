<?php

namespace Controller;

use PDO;

/**
 * Report controller
 *
 * @package  controller
 */
class Report extends Base
{
	public function index()
	{
		$repFunc = $this->request->getStringParam('repFunc');
		
        if ($repFunc != null) {
				$this->$repFunc();
        }
		
		$this->response->html($this->template->layout('report/index', array(
			'values' => array(
			'controller' => 'report',
			'action' => 'index',	
			'reportType' => 0,
			'repFunc' => '',),
		'title' => t('Generate Report'),
		'errors' => array(),
		'date_format' => $this->config->get('application_date_format'),
		'date_formats' => $this->dateParser->getAvailableFormats(),
		)));
	}
	
	public function doPrintProcess($startPrint){
		$reportType = $this->request->getStringParam('reportType');
		$repFunc = $this->request->getStringParam('repFunc');
		
		$noOption = array('1', '2', '6', '8', '9');
		
		$repTitle = 'KB' . substr($repFunc, -1) . ($reportType==0 ? '(Summary)' : '(Detailed)');
		
		if (in_array(substr($repFunc, -1), $noOption)) {
			$repTitle = 'KB' . substr($repFunc, -1);
		}

		$data = $this->$startPrint();
		$this->response->forceDownload($repTitle . '.csv');
		$this->response->csv($data);
	}
	
	public function report1()	
	{		
		$this->doPrintProcess('getReport1Result');
	}
	
	public function getReport1Result()
	{
		$report1Result = $this->getReport1Query();
		$results = array($this->getReport1Columns());
		foreach ($report1Result as &$report1Result) {
			$results[] = $report1Result;
		}
		return $results;
	}
	
	public function getReport1Query()
	{
		$sql = 'SELECT count(a.task_id), a.task_id, c.client, c.title, b.name, TIME_FORMAT(SEC_TO_TIME(sum(a.time_spent)/count(a.task_id)),"%Hh %im")
				  FROM transitions a, users b, tasks c
				 WHERE a.project_id = 2
				   AND a.src_column_id = 7
				   AND a.dst_column_id = 8
				   AND a.user_id = b.id
				   AND a.task_id = c.id
			  GROUP BY task_id
			  ORDER BY task_id asc';

		$rq = $this->db->execute($sql, array());
		return $rq->fetchAll(PDO::FETCH_ASSOC);
	}
	
	public function getReport1Columns()
	{
		return array(
			e('NO.OF TIMES RETURNED'),
			e('TASK ID'),
			e('CLIENT'),
			e('TITLE'),
			e('NAME'),
			e('AVERAGE TIME TO APPROVE/DISAPPROVE(For SA Analysis)'),
		);
	}
	
	public function report2()
	{		 
		$this->doPrintProcess('getReport2Result');
	}
	
	public function getReport2Result()
	{
		$report2Result = $this->getReport2Query();
		$results = array($this->getReport2Columns());
		foreach ($report2Result as &$report2Result) {
			$results[] = $report2Result;
		}
		return $results;
	}
	
	public function getReport2Query()
	{
		$sql = 'SELECT f.CLIENT_VALIDATED, f.VALIDATED_COUNT, COALESCE(i.APPROVED_COUNT, 0) AS APPROVED, 
					   ROUND(((COALESCE(i.APPROVED_COUNT, 0)/f.VALIDATED_COUNT) *100), 2) AS PERCENTAGE
				  FROM (SELECT e.CLIENT_VALIDATED, count(distinct e.task_id) AS VALIDATED_COUNT
						  FROM (SELECT distinct a.task_id , b.client AS CLIENT_VALIDATED
								  FROM transitions a, tasks b
								 WHERE a.project_id = 2
								   AND a.dst_column_id = 8
								   AND a.task_id = b.id
								   AND b.client is not null
								   AND b.swimlane_id = 0
							  GROUP BY a.task_id
							 UNION ALL
								SELECT distinct c.task_id , d.client AS CLIENT_VALIDATED
								  FROM transitions c, tasks d
								 WHERE c.project_id = 20
								   AND c.dst_column_id = 93
								   AND c.task_id = d.id
								   AND d.client is not null
								   AND d.swimlane_id = 0
							  GROUP BY c.task_id) e
					  GROUP BY e.CLIENT_VALIDATED)f
				  LEFT JOIN
						(SELECT h.client AS CLIENT_APPROVED , COUNT(distinct h.id) AS APPROVED_COUNT
						   FROM transitions g, tasks h
						  WHERE g.project_id = 7
						    AND g.dst_column_id = 27
						    AND g.task_id = h.id
					   GROUP BY CLIENT_APPROVED)i
				  ON f.CLIENT_VALIDATED = i.CLIENT_APPROVED
				 WHERE f.CLIENT_VALIDATED IS NOT NULL';

		$rq = $this->db->execute($sql, array());
		return $rq->fetchAll(PDO::FETCH_ASSOC);
	}
	
	public function getReport2Columns()
	{
		return array(
			e('CLIENT'),
			e('NO. OF VALIDATED SR'),
			e('NO. OF APPROVED SR'),
			e('PERCENTAGE'),
		);
	}
	
	public function report3()
	{		 
		$this->doPrintProcess('getReport3Result');
	}
	
	public function getReport3Result()
	{
		$report3Result = $this->getReport3Query();
		$results = array($this->getReport3Columns());
		foreach ($report3Result as &$report3Result) {
			$results[] = $report3Result;
		}
		return $results;
	}
	
	public function getReport3Query()
	{
		$from = $this->request->getStringParam('from');
		$to = $this->request->getStringParam('to');
		$reportType = $this->request->getStringParam('reportType');
		
		$sql = 'SELECT COALESCE(b.name, b.username), d.dev, count(*)
				  FROM transitions a, users b, tasks c, (SELECT DISTINCT e.task_id, COALESCE(f.name, f.username) AS DEV
														   FROM transitions e, users f
														  WHERE e.project_id = 3 
															AND e.user_id = f.id 
															AND e.dst_column_id = 12) d
				 WHERE a.user_id = b.id
				   AND a.dst_column_id = 46
				   AND a.project_id = 11
				   AND a.task_id = c.id
				   AND a.task_id = d.task_id
			  GROUP BY a.user_id, d.dev
			  ORDER BY a.user_id ASC';
		
		$sql2 = 'SELECT COALESCE(b.name, b.username), d.dev, a.task_id, c.title, count(a.task_id)
				   FROM transitions a, users b, tasks c, (SELECT DISTINCT e.task_id, COALESCE(f.name, f.username) AS DEV
															FROM transitions e, users f
														   WHERE e.project_id = 3 
															 AND e.user_id = f.id 
															 AND e.dst_column_id = 12) d
				  WHERE a.user_id = b.id
					AND a.dst_column_id = 46	
					AND a.project_id = 11
					AND a.task_id = c.id
					AND a.task_id = d.task_id
					AND a.date between ? and ?
			   GROUP BY a.task_id, a.user_id
			   ORDER BY a.user_id';
				
		if (! is_numeric($from)) {
            $from = $this->dateParser->removeTimeFromTimestamp($this->dateParser->getTimestamp($from));
        }

        if (! is_numeric($to)) {
            $to = $this->dateParser->removeTimeFromTimestamp(strtotime('+1 day', $this->dateParser->getTimestamp($to)));
        }
		
		$rq = $this->db->execute(($reportType == 0 ? $sql : $sql2), array($from, $to));
		return $rq->fetchAll(PDO::FETCH_ASSOC);
	}
	
	public function getReport3Columns()
	{
		$reportType = $this->request->getStringParam('reportType');
		
		if($reportType == 0){
			return array(
				e('SA'),
				e('DEVELOPER'),
				e('NO.OF DISAPPROVED TASK'),
			);
		}else{
			return array(
				e('SA'),
				e('DEVELOPER'),
				e('KB NO.'),
				e('KB TITLE'),
				e('NO. OF TIMES RETURNED'),
			);
		}
	}
	
	public function report4()
	{		 
		$this->doPrintProcess('getReport4Result');
	}
	
	public function getReport4Result()
	{
		$report4Result = $this->getReport4Query();
		$results = array($this->getReport4Columns());
		foreach ($report4Result as &$report4Result) {
			$results[] = $report4Result;
		}
		return $results;
	}
	
	public function getReport4Query()
	{	
		$from = $this->request->getStringParam('from');
		$to = $this->request->getStringParam('to');
		$reportType = $this->request->getStringParam('reportType');
		
		$sql = 'SELECT COALESCE(b.name, b.username), d.dev, count(*)
				   FROM transitions a, users b, tasks c, (SELECT DISTINCT e.task_id, COALESCE(f.name, f.username) AS DEV
															FROM transitions e, users f
														   WHERE e.project_id = 3 
															 AND e.user_id = f.id 
															 AND e.dst_column_id = 12) d
			      WHERE a.user_id = b.id
					AND a.dst_column_id = 50
				    AND a.project_id = 12
				    AND a.task_id = c.id
				    AND a.task_id = d.task_id
			   GROUP BY a.user_id, d.dev
			   ORDER BY a.user_id ASC';
		
		$sql2 = ' SELECT COALESCE(b.name, b.username), d.dev, a.task_id, c.title, count(a.task_id)
					FROM transitions a, users b, tasks c, (SELECT DISTINCT e.task_id, COALESCE(f.name, f.username) AS DEV
															 FROM transitions e, users f
															WHERE e.project_id = 3 
															  AND e.user_id = f.id 
															 AND e.dst_column_id = 12) d
				   WHERE a.user_id = b.id
					 AND a.dst_column_id = 50
					 AND a.project_id = 12
					 AND a.task_id = c.id
					 AND a.task_id = d.task_id
					 AND a.date between ? and ?
				GROUP BY a.task_id, a.user_id
				ORDER BY a.user_id';
				
		if (! is_numeric($from)) {
            $from = $this->dateParser->removeTimeFromTimestamp($this->dateParser->getTimestamp($from));
        }

        if (! is_numeric($to)) {
            $to = $this->dateParser->removeTimeFromTimestamp(strtotime('+1 day', $this->dateParser->getTimestamp($to)));
        }
		
		$rq = $this->db->execute(($reportType == 0 ? $sql : $sql2), array($from, $to));
		return $rq->fetchAll(PDO::FETCH_ASSOC);
	}
	
	public function getReport4Columns(){
		$reportType = $this->request->getStringParam('reportType');
		
		if($reportType == 0){
			return array(
				e('TL'),
				e('DEVELOPER'),
				e('NO.OF DISAPPROVED TASK'),
			);
		}else{
			return array(
				e('TL'),
				e('DEVELOPER'),
				e('KB NO.'),
				e('KB TITLE'),
				e('NO. OF TIMES RETURNED'),
			);
		}
	}
	
	public function report5()
	{		 
		$this->doPrintProcess('getReport5Result');
	}
	
	public function getReport5Result()
	{
		$report5Result = $this->getReport5Query();
		$results = array($this->getReport5Columns());
		foreach ($report5Result as &$report5Result) {
			$results[] = $report5Result;
		}
		return $results;
	}
	
	public function getReport5Query()
	{
		$from = $this->request->getStringParam('from');
		$to = $this->request->getStringParam('to');
		$reportType = $this->request->getStringParam('reportType');
		
		$sql = 'SELECT COALESCE(b.name, b.username), count(DISTINCT a.task_id), TIME_FORMAT(SEC_TO_TIME(sum(a.time_spent)/count(DISTINCT a.task_id)),"%Hh %im")
				   FROM transitions a, users b
				  WHERE a.project_id = 7
					AND a.src_column_id = 55
					AND a.user_id = b.id
			   GROUP BY a.user_id';
		
		$sql2 = 'SELECT COALESCE(b.name, b.username), a.task_id, c.title, TIME_FORMAT(SEC_TO_TIME(SUM(a.time_spent)),"%Hh %im")
				   FROM transitions a, users b, tasks c
			      WHERE a.project_id = 7	
				    AND a.src_column_id = 55
				    AND a.user_id = b.id
				    AND a.task_id = c.id
				    AND a.date between ? and ?
			   GROUP BY a.user_id, a.task_id
			   ORDER BY a.task_id';
				
		if (! is_numeric($from)) {
            $from = $this->dateParser->removeTimeFromTimestamp($this->dateParser->getTimestamp($from));
        }

        if (! is_numeric($to)) {
            $to = $this->dateParser->removeTimeFromTimestamp(strtotime('+1 day', $this->dateParser->getTimestamp($to)));
        }
		
		$rq = $this->db->execute(($reportType == 0 ? $sql : $sql2), array($from, $to));
		return $rq->fetchAll(PDO::FETCH_ASSOC);
	}
	
	public function getReport5Columns()
	{
		$reportType = $this->request->getStringParam('reportType');
		
		if($reportType == 0){
			 return array(
				e('QA'),
				e('NO. OF TESTED KN TASK'),
				e('AVERAGE TIME'),
		);
		}else{
			return array(
				e('QA'),
				e('KB NO.'),
				e('KB TITLE'),
				e('TOTAL TIME'),
			);
		}
	}
	
	public function report6()
	{		 
		$this->doPrintProcess('getReport6Result');
	}
	
	public function getReport6Result()
	{
		$report6Result = $this->getReport6Query();
		$results = array($this->getReport6Columns());
		foreach ($report6Result as &$report6Result) {
			$results[] = $report6Result;
		}
		return $results;
	}
	
	public function getReport6Query()
	{
		$sql = 'SELECT j.client, count(j.task_id), TIME_FORMAT(SEC_TO_TIME(sum(j.total_spent)/count(j.task_id)),"%Hh %im") AS AVERAGE_TIME 
				  FROM (SELECT f.task_id, (f.total_spent + i.total_spent) AS TOTAL_SPENT, f.client 
						  FROM (SELECT distinct e.task_id, SUM(e.total_spent) AS TOTAL_SPENT, e.client
								  FROM (SELECT distinct a.task_id, SUM(a.time_spent) AS TOTAL_SPENT, b.client
										  FROM transitions a, tasks b
										 WHERE a.project_id = 2
										   AND a.dst_column_id = 8
										   AND a.task_id = b.id
										   AND b.client is not null
										   AND b.swimlane_id = 0
									  GROUP BY a.task_id
									 UNION ALL
										SELECT distinct c.task_id, SUM(c.time_spent) AS TOTAL_SPENT, d.client 
										  FROM transitions c, tasks d
										 WHERE c.project_id = 20
										   AND c.dst_column_id = 93
										   AND c.task_id = d.id
										   AND d.client is not null
										   AND d.swimlane_id = 0
									  GROUP BY c.task_id) e
							  GROUP BY e.task_id
                              ORDER BY e.task_id)f
						LEFT JOIN
							   (SELECT distinct g.task_id, SUM(g.time_spent) AS TOTAL_SPENT, h.client 
								  FROM transitions g, tasks h	
								 WHERE g.project_id = 3
								   AND g.src_column_id = 10
								   AND g.dst_column_id in(11,53,277)
								   AND g.task_id = h.id
								   AND h.client is not null
								   AND h.swimlane_id = 0
							  GROUP BY g.task_id
							  ORDER BY g.task_id)i
						ON f.task_id = i.task_id
						 WHERE i.task_id IS NOT NULL)j
                GROUP BY j.client';

		$rq = $this->db->execute($sql, array());
		return $rq->fetchAll(PDO::FETCH_ASSOC);
	}
	
	public function getReport6Columns()
	{
		return array(
			e('CLIENT'),
			e('TOTAL TASK'),
			e('AVERAGE TIME FROM VALIDATION TO APPROVAL'),
		);
	}
	
	public function report7()
	{		 
		$this->doPrintProcess('getReport7Result');
	}
	
	public function getReport7Result()
	{
		$report7Result = $this->getReport7Query();
		$results = array($this->getReport7Columns());
		foreach ($report7Result as &$report7Result) {
			$results[] = $report7Result;
		}
		return $results;
	}
	
	public function getReport7Query()
	{
		$from = $this->request->getStringParam('from');
		$to = $this->request->getStringParam('to');
		$reportType = $this->request->getStringParam('reportType');
		
		$sql = 'SELECT COALESCE(b.name, b.username), count(DISTINCT a.task_id), TIME_FORMAT(SEC_TO_TIME(sum(a.time_spent)/count(DISTINCT a.task_id)),"%Hh %im")
				  FROM transitions a, users b
				 WHERE a.project_id = 3
				   AND a.src_column_id = 11
				   AND a.user_id = b.id
			  GROUP BY a.user_id';
		
		$sql2 = 'SELECT COALESCE(b.name, b.username), a.task_id, c.title, TIME_FORMAT(SEC_TO_TIME(SUM(a.time_spent)),"%Hh %im")
					FROM transitions a, users b, tasks c
				   WHERE a.project_id = 3
					 AND a.src_column_id = 11
					 AND a.user_id = b.id
					 AND a.task_id = c.id
					 and a.date between ? and ?
				GROUP BY a.user_id, a.task_id
				ORDER BY a.user_id,a.task_id';
				
		if (! is_numeric($from)) {
            $from = $this->dateParser->removeTimeFromTimestamp($this->dateParser->getTimestamp($from));
        }

        if (! is_numeric($to)) {
            $to = $this->dateParser->removeTimeFromTimestamp(strtotime('+1 day', $this->dateParser->getTimestamp($to)));
        }
		
		$rq = $this->db->execute(($reportType == 0 ? $sql : $sql2), array($from, $to));
		return $rq->fetchAll(PDO::FETCH_ASSOC);
		
	}
	
	public function getReport7Columns()
	{
		$reportType = $this->request->getStringParam('reportType');
		
		if($reportType == 0){
			return array(
			e('DEVELOPER'),
			e('NO. OF UNIQUE TASK'),
			e('AVERAGE TIME'),
		);
		}else{
			return array(
				e('DEVELOPER'),
				e('KB NO.'),
				e('KB TITLE'),
				e('TOTAL TIME'),
			);
		}
	}
	
	public function report8()
	{		 
		$this->doPrintProcess('getReport8Result');
	}
	
	public function getReport8Result()
	{
		$report8Result = $this->getReport8Query();
		$results = array($this->getReport8Columns());
		foreach ($report8Result as &$report8Result) {
			$results[] = $report8Result;
		}
		return $results;
	}
	
	public function getReport8Query()
	{
		$from = $this->request->getStringParam('from');
		$to = $this->request->getStringParam('to');
		
		$sql = 'SELECT b.task_id, c.title, d.time_total 
				  FROM transitions b, tasks c, (SELECT a.task_id, sum(a.time_spent)/3600 AS TIME_TOTAL 
												  FROM transitions a
                                              GROUP BY a.task_id
											  ORDER BY a.task_id desc) d
				 WHERE b.task_id = c.id
				   AND d.task_id = b.task_id
				   AND b.dst_column_id = 267
				   AND b.date between ? and ?
			  GROUP BY task_id';

		if (! is_numeric($from)) {
            $from = $this->dateParser->removeTimeFromTimestamp($this->dateParser->getTimestamp($from));
        }

        if (! is_numeric($to)) {
            $to = $this->dateParser->removeTimeFromTimestamp(strtotime('+1 day', $this->dateParser->getTimestamp($to)));
        }
		
		$rq = $this->db->execute($sql, array($from, $to));
		return $rq->fetchAll(PDO::FETCH_ASSOC);
	}
	
	public function getReport8Columns()
	{
		return array(
			e('TASK ID'),
			e('TITLE'),
			e('TOTAL DEVELOPMENT TIME'),
		);
	}
	
	public function report9()
	{		 
		$this->doPrintProcess('getReport9Result');
	}
	
	public function getReport9Result()
	{
		$report9Result = $this->getReport9Query();
		$results = array($this->getReport9Columns());
		foreach ($report9Result as &$report9Result) {
			$results[] = $report9Result;
		}
		return $results;
	}
	
	public function getReport9Query()
	{
		$from = $this->request->getStringParam('from');
		$to = $this->request->getStringParam('to');
		$sql = 'SELECT g.sa, d.dev, COALESCE(b.name, b.username), a.task_id, c.title, count(a.task_id)
				  FROM transitions a, users b, tasks c, (SELECT DISTINCT e.task_id, COALESCE(f.name, f.username) AS DEV
														   FROM transitions e, users f
														  WHERE e.project_id = 3 
														    AND e.user_id = f.id 
															AND e.dst_column_id = 12) d,
														(SELECT DISTINCT e.task_id, COALESCE(f.name, f.username) AS SA
														   FROM transitions e, users f
														  WHERE e.project_id = 11 
															AND e.user_id = f.id 
															AND e.dst_column_id = 75) g
				 WHERE a.user_id = b.id
				   AND a.dst_column_id = 28
				   AND a.project_id = 7
				   AND a.task_id = c.id
				   AND a.task_id = d.task_id
				   AND a.task_id = g.task_id
				   AND a.date between ? and ?
			  GROUP BY a.user_id, d.dev, a.task_id
			  ORDER BY a.user_id';

		if (! is_numeric($from)) {
            $from = $this->dateParser->removeTimeFromTimestamp($this->dateParser->getTimestamp($from));
        }

        if (! is_numeric($to)) {
            $to = $this->dateParser->removeTimeFromTimestamp(strtotime('+1 day', $this->dateParser->getTimestamp($to)));
        }
		
		$rq = $this->db->execute($sql, array($from, $to));
		return $rq->fetchAll(PDO::FETCH_ASSOC);
	}
	
	public function getReport9Columns()
	{
		return array(
			e('SA'),
			e('DEVELOPER'),
			e('QA'),
			e('KB NO.'),
			e('KB TITLE'),
			e('NO. OF TIMES FAILED'),
		);
	}
	
}
 