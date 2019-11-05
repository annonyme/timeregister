<?php
namespace timerec\daos;

use core\utils\XWServerInstanceToolKit;
use PDBC\PDBCCache;
use PDBC\PDBCObjectMapper;
use timerec\entities\Entry;
use timerec\entities\Customer;
use core\utils\dates\XWCalendar;
use xw\entities\users\XWUserDAO;

class EntryDAO{
	private $db=null;
	
	static private $instance=null;
	
	static public function instance(): EntryDAO{
		if(self::$instance==null){
			self::$instance=new EntryDAO();
		}
		return self::$instance;
	}
	
	public function __construct(){
		$dbName=XWServerInstanceToolKit::instance()->getServerSwitch()->getDbname();
		$this->db=PDBCCache::getInstance()->getDB($dbName);
	}
	
	/**
	 * @return Entry
	 * @param int $id
	 */
	public function loadEntry(int $id):Entry{
		$mapper=new PDBCObjectMapper();
		return $mapper->load($this->db, $id, Entry::class);
	}
	
	/**
	 * @return Entry
	 * @param Entry $entry
	 */
	public function saveEntry(Entry $entry):Entry{
		$mapper=new PDBCObjectMapper();
		return $mapper->merge($this->db, $entry);
	}
	
	/**
	 * @param Entry $entry
	 */
	public function deleteEntry(Entry $entry){
		$mapper=new PDBCObjectMapper();
		$mapper->delete($this->db, $entry);
	}
	
	/**
	 * @return Entry[]
	 * @param Customer $customer
	 */
	public function loadEntryListByCustomer(Customer $customer): array{
		$mapper=new PDBCObjectMapper();
		return $mapper->loadListByColumn($this->db, "CUSTOMER_ID", $customer->getId(), "int", Entry::class, "ENTRY_START", "DESC");
	}
	
	public function getDate(Entry $entry, $full = true): string{
	    $result = "Today";
	    $cal = new XWCalendar();
		$cal->setMySQLDateString($entry->getStart());
		if($full) {
			$result = $cal->format("Y-m-d H:i:s");
		}
		else {
			$result = $cal->format("Y-m-d");
		}	    
	    return $result;
	}

	public function getEndDate(Entry $entry, $full = true): string{
		$result = "";
		if($entry->getStop()) {
			$cal = new XWCalendar();
			$cal->setMySQLDateString($entry->getStop());
			if($full) {
				$result = $cal->format("Y-m-d H:i:s");
			}
			else {
				$result = $cal->format("Y-m-d");
			}
		}	    	    
	    return $result;
	}
	
	public function getTime(Entry $entry): string{
	    $result = "-";
	    $calStart = new XWCalendar();
	    $calStart->setMySQLDateString($entry->getStart());
	    $calStop = new XWCalendar();
	    $calStop->setMySQLDateString($entry->getStop());
	
		$result = gmdate('H:i:s', $calStop->getTimeInMillis() - $calStart->getTimeInMillis());
	    return $result;
	}
	
	public function isRunning(array $entries): bool{
	    $result = false;
	    foreach ($entries as $entry){
	        if($this->isNotStopped($entry) &&  $entry->getUserId() == XWUserDAO::instance()->getCurrentUser()->getId()){
	            $result = true;
	        }
	    }
	    return $result;
	}
	
	public function isNotStopped(Entry $entry):bool{
	    return strlen($entry->getStop()) == 0 ||  $entry->getStop() == "0000-00-00 00:00:00";
	}
	
	public function isStopped(Entry $entry):bool{
	    return strlen($entry->getStop()) > 0 &&  $entry->getStop() != "0000-00-00 00:00:00";
	}
}