<?php
namespace timerec\entities;

/**
 * @dbtable=TIMEREC_GROUPS
 */
class Group{
	/**
	 * @dbcolumn=GROUP_ID
	 * @dbprimary
	 * @dbtype=int
	 */
	private $id=0;
	/**
	 * @dbcolumn=GROUP_NAME
	 * @dbtype=string
	 */
	private $name="";
	/**
	 * @dbcolumn=GROUP_DESCRIPTION
	 * @dbtype=string
	 */
	private $description="";
	 
	/**
	 * @dbcolumn=USER_ID
	 * @dbtype=int
	 */
	private $ownerId=0;
	
	/**
	 * @dbcolumn=GROUP_INVITATIONCODE
	 * @dbtype=string
	 */
	private $invitationCode = "";
	
	public function getId() {
		return $this->id;
	}
	
	public function setId($id) {
		$this->id = $id;
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function setName($name) {
		$this->name = $name;
	}
	
	public function getDescription() {
		return $this->description;
	}
	
	public function setDescription($description) {
		$this->description = $description;
	}
	
	public function getOwnerId() {
		return $this->ownerId;
	}
	
	public function setOwnerId($ownerId) {
		$this->ownerId = $ownerId;
	}
	
	public function getInvitationCode() {
		return $this->invitationCode;
	}
	
	public function setInvitationCode($invitationCode) {
		$this->invitationCode = $invitationCode;
	}
}