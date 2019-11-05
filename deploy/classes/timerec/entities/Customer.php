<?php
namespace timerec\entities;

/**
 * @dbtable=TIMEREC_CUSTOMERS
 */
class Customer{
	/**
	 * @dbcolumn=CUSTOMER_ID
	 * @dbprimary
	 * @dbtype=int
	 */
	private $id=0;
	/**
	 * @dbcolumn=CUSTOMER_NAME
	 * @dbtype=string
	 */
	private $name="";
	/**
	 * @dbcolumn=CUSTOMER_ADDRESS
	 * @dbtype=string
	 */
	private $address="";
	/**
	 * @dbcolumn=CUSTOMER_PHONE
	 * @dbtype=string
	 */
	private $phone="";
	/**
	 * @dbcolumn=CUSTOMER_EMAIL
	 * @dbtype=string
	 */
	private $email="";
	 
	/**
	 * @dbcolumn=GROUP_ID
	 * @dbtype=int
	 */
	private $groupId=0;
	
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
	
	public function getAddress() {
		return $this->address;
	}
	
	public function setAddress($address) {
		$this->address = $address;
	}
	
	public function getEmail() {
		return $this->email;
	}
	
	public function setEmail($email) {
		$this->email = $email;
	}
	
	public function getGroupId() {
		return $this->groupId;
	}
	
	public function setGroupId($groupId) {
		$this->groupId = $groupId;
	}
	
	public function getPhone(){
	    return $this->phone;
	}
	
	public function setPhone($phone){
	    $this->phone = $phone;
	}
}