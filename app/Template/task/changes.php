<?php if ( empty($changes)): ?>
    <ul>
        <?php
        
		$cost_list = array('Unassigned' => 'Unassigned',
								'Maintenance' => 'Maintenance', 
        						'Development' => 'Development',
								'Enhancement' => 'Enhancement',
								'Implementation' => 'Implementation',
								'Others' => 'Others');
		
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
											  'UAT' => 'UAT',
											  'UT' => 'UT');
											  
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
								'AFPGEN' => 'AFPGEN',
								'ALLs' => 'ALLs');
								

        foreach ($changes as $field => $value) {

            switch ($field) {
                case 'title':
                    echo '<li>'.t('New title: %s', $task['title']).'</li>';
                    break;
                case 'owner_id':
                    if (empty($task['owner_id'])) {
                        echo '<li>'.t('The task is not assigned anymore').'</li>';
                    }
                    else {
                        echo '<li>'.t('New assignee: %s', $task['assignee_name'] ?: $task['assignee_username']).'</li>';
                    }
                    break;
                case 'category_id':
                    if (empty($task['category_id'])) {
                        echo '<li>'.t('There is no category now').'</li>';
                    }
                    else {
                        echo '<li>'.t('New category: %s', $task['category_name']).'</li>';
                    }
                    break;
                case 'color_id':
                    echo '<li>'.t('New color: %s', $this->text->in($task['color_id'], $this->task->getColors())).'</li>';
                    break;
				case 'cost':
                    echo '<li>'.t('Cost Center: %s', $this->text->in($task['cost'], $cost_list)).'</li>';
                    break;
				case 'activity':
                    echo '<li>'.t('Activity: %s', $this->text->in($task['activity'], $activity_list[$this->text->in($task['cost'], $cost_list)])).'</li>';
                    break;	
				case 'client':
                    echo '<li>'.t('Client: %s', $this->text->in($task['client'], $client_list)).'</li>';
			
				 break;
                case 'score':
                    echo '<li>'.t('New complexity: %d', $task['score']).'</li>';
                    break;
                case 'date_due':
                    if (empty($task['date_due'])) {
                        echo '<li>'.t('The due date have been removed').'</li>';
                    }
                    else {
                        echo '<li>'.dt('New due date: %B %e, %Y', $task['date_due']).'</li>';
                    }
                    break;
                case 'description':
                    if (empty($task['description'])) {
                        echo '<li>'.t('There is no description anymore').'</li>';
                    }
                    break;
                case 'recurrence_status':
                case 'recurrence_trigger':
                case 'recurrence_factor':
                case 'recurrence_timeframe':
                case 'recurrence_basedate':
                case 'recurrence_parent':
                case 'recurrence_child':
                    echo '<li>'.t('Recurrence settings have been modified').'</li>';
                    break;
                case 'time_spent':
                    echo '<li>'.t('Time spent changed: %sh', $task['time_spent']).'</li>';
                    break;
                case 'time_estimated':
                    echo '<li>'.t('Time estimated changed: %sh', $task['time_estimated']).'</li>';
                    break;
                case 'date_started':
                    if ($value != 0) {
                        echo '<li>'.dt('Start date changed: %B %e, %Y', $task['date_started']).'</li>';
                    }
                    break;
				case 'sr_relation':
                    echo '<li>'.t('Added SR Relation: %s', $changes['sr_relation']).'</li>';
                break;
				case 'sr_delete':
                    echo '<li>'.t('Removed SR Relation: %s', $changes['sr_delete']).'</li>';
                break;
                default:
                    echo '<li>'.t('The field "%s" have been updated', $field).'</li>';
            }
        }

        ?>
		
    </ul>

    <?php if (! empty($changes['description'])): ?>
        <p><?= t('The description have been modified') ?></p>
        <div class="markdown"><?= $this->text->markdown($task['description']) ?></div>
    <?php endif ?>
	
       
  
<?php endif ?>