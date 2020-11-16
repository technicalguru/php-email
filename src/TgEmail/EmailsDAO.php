<?php

namespace TgEmail;

use TgDatabase\DAO;

/**
 * The DAO for Email objects
 * @author ralph
 *        
 */
class EmailsDAO extends DAO {

    /**
     */
    public function __construct($database, $tableName = '#__mail_queue', $modelClass = 'TgEmail\\Email', $idColumn = 'uid') {
        parent::__construct($database, $tableName, $modelClass, $idColumn);
    }
    
    public function housekeeping($maxSentDays = 90, $maxFailedDays = 180) {
        $this->database->delete($this->tableName, 'status=\'sent\'   AND TIMESTAMPDIFF(DAY, sent_time, NOW()) >= '.$maxSentDays);
        $this->database->delete($this->tableName, 'status=\'failed\' AND TIMESTAMPDIFF(DAY, sent_time, NOW()) >= '.$maxFailedDays);
    }
    
    public function getPendingEmails() {
        return $this->find(array('status' => Email::PENDING), array('queued_time'));
    }
}

