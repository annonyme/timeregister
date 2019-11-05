<?php
namespace timerec\daos;

use timerec\entities\Customer;
use PDBC\PDBCObjectMapper;
use timerec\entities\Group;
use core\utils\XWServerInstanceToolKit;
use PDBC\PDBCCache;

class CustomerDAO{
	private $db=null;
	
	static private $instance=null;
	
	static public function instance():CustomerDAO{
		if(self::$instance==null){
			self::$instance=new CustomerDAO();
		}
		return self::$instance;
	}
	
	public function __construct(){
		$dbName=XWServerInstanceToolKit::instance()->getServerSwitch()->getDbname();
		$this->db=PDBCCache::getInstance()->getDB($dbName);
	}
	
	/**
	 * @return Customer
	 * @param int $id
	 */
	public function loadCustomer(int $id):Customer{
		$mapper=new PDBCObjectMapper();
		return $mapper->load($this->db, $id, Customer::class);
	}
	
	/**
	 * @return Customer
	 * @param Customer $customer
	 */
	public function saveCustomer(Customer $customer):Customer{
		$mapper=new PDBCObjectMapper();
		return $mapper->merge($this->db, $customer);
	}
	
	/**
	 * @param Customer $customer
	 */
	public function deleteCustomer(Customer $customer){
		$mapper=new PDBCObjectMapper();
		$mapper->delete($this->db, $customer);
	}
	
	/**
	 * @return Customer[]
	 * @param Group $group
	 */
	public function loadCustomerListByGroup(Group $group):array{
		$mapper=new PDBCObjectMapper();
		return $mapper->loadListByColumn($this->db, "GROUP_ID", $group->getId(), "int", Customer::class);
	}
}