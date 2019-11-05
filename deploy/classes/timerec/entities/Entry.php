<?php
namespace timerec\entities;

/**
 * @dbtable=TIMEREC_ENTRIES
 */
class Entry{
	/**
	 * @dbcolumn=ENTRY_ID
	 * @dbprimary
	 * @dbtype=int
	 */
	private $id=0;
	/**
	 * @dbcolumn=ENTRY_START
	 * @dbtype=date
	 */
	private $start="";
	/**
	 * @dbcolumn=ENTRY_STOP
	 * @dbtype=date
	 */
	private $stop="";
	/**
	 * @dbcolumn=ENTRY_COMMENT
	 * @dbtype=string
	 */
	private $comment="";
	/**
	 * @dbcolumn=ENTRY_PAYED
	 * @dbtype=bool
	 */
	private $payed=false;
	 
	/**
	 * @dbcolumn=USER_ID
	 * @dbtype=int
	 */
	private $userId=0;
	 
	/**
	 * @dbcolumn=CUSTOMER_ID
	 * @dbtype=int
	 */
	private $customerId=0;
	
	public function getId() {
		return $this->id;
	}
	
	public function setId($id) {
		$this->id = $id;
	}
	
	public function getStart() {
		return $this->start;
	}
	
	public function setStart($start) {
		$this->start = $start;
	}
	
	public function getStop() {
		return $this->stop;
	}
	public function setStop($stop) {
		$this->stop = $stop;
	}
	
	public function getComment() {
		return $this->comment;
	}
	
	public function setComment($comment) {
		$this->comment = $comment;
	}
	
	public function isPayed() {
		return $this->payed;
	}
	
	public function setPayed($payed) {
		$this->payed = $payed;
	}
	public function getUserId() {
		return $this->userId;
	}
	
	public function setUserId($userId) {
		$this->userId = $userId;
	}
	
	public function getCustomerId() {
		return $this->customerId;
	}
	
	public function setCustomerId($customerId) {
		$this->customerId = $customerId;
	}
}
