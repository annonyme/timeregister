<?php
namespace timerec\daos;

use core\utils\XWServerInstanceToolKit;
use PDBC\PDBCCache;
use timerec\entities\Group;
use PDBC\PDBCObjectMapper;
use xw\entities\users\XWUser;
use core\database\XWSQLStatement;

class GroupDAO{
	private $db=null;
	
	static private $instance=null;
	
	static public function instance():GroupDAO{
		if(self::$instance==null){
			self::$instance=new GroupDAO();
		}
		return self::$instance;
	}
	
	public function __construct(){
		$dbName=XWServerInstanceToolKit::instance()->getServerSwitch()->getDbname();
		$this->db=PDBCCache::getInstance()->getDB($dbName);
	}
	
	/**
	 * @return Group
	 * @param int $id
	 */
	public function loadGroup(int $id):Group{
		$mapper=new PDBCObjectMapper();
		return $mapper->load($this->db, $id, Group::class);
	}
	
	/**
	 * @return Group
	 * @param Group $group
	 */
	public function saveGroup(Group $group): Group{
		$mapper=new PDBCObjectMapper();
		return $mapper->merge($this->db, $group);
	}
	
	/**
	 * @param Group $group
	 */
	public function deleteGroup(Group $group){
		$mapper=new PDBCObjectMapper();
		$mapper->merge($this->db, $group);
	}
	
	/**
	 * @return Group[]
	 * @param XWUser $user
	 */
	public function loadGroupListByUser(XWUser $user):array{
		$sql = "SELECT G.* 
				FROM TIMEREC_GROUPS G,
				     TIMEREC_GROUPS_USERS GU 
				WHERE GU.USER_ID=#{userId}
				  AND G.GROUP_ID=GU.GROUP_ID
				ORDER BY G.GROUP_NAME";
		$stmt=new XWSQLStatement($sql);
		$stmt->setInt("userId", $user->getId());
		
		$mapper=new PDBCObjectMapper();
		return $mapper->queryList($this->db, $stmt->getSQL(), Group::class);
	}
	
	/**
	 * @return Group[]
	 * @param XWUser $user
	 */
	public function loadGroupListByOwner(XWUser $user):array{
		$mapper=new PDBCObjectMapper();
		return $mapper->loadListByColumn($this->db, "USER_ID", $user->getId(), "int", Group::class);
	}
	
	/**
	 * @return XWUser[]
	 * @param Group $group
	 */
	public function loadUserListByGroup(Group $group):array{
		$sql="SELECT U.* 
			  FROM XW_USERS U, 
				   TIMEREC_GROUPS_USERS GU
			  WHERE GU.GROUP_ID=#{groupId} 
				AND U.USER_ID=GU.USER_ID 
			  ORDER BY U.USER_NAME";
		
		$stmt=new XWSQLStatement($sql);
		$stmt->setInt("groupId", $group->getId());
		
		$mapper=new PDBCObjectMapper();
		return $mapper->queryList($this->db, $stmt->getSQL(), XWUser::class);
	}
	
	/**
	 * @return bool
	 * @param Group $group
	 * @param XWUser $user
	 */
	public function isMemberOfGroup(Group $group, XWUser $user):bool{
		$result = false;
		$users=$this->loadUserListByGroup($group);
		foreach ($users as $member){
			if($member->getId()==$user->getId()){
				$result=true;
			}
		}
		return $result;
	}
	
	/**
	 * 
	 * @param XWUser $user
	 * @param Group $group
	 */
	public function addUserToGroup(XWUser $user, Group $group){
		$sql="INSERT INTO TIMEREC_GROUPS_USERS(GROUP_ID, USER_ID)
				VALUES (#{groupId},#{userId})";
		$stmt=new XWSQLStatement($sql);
		$stmt->setInt("groupId", $group->getId());
		$stmt->setInt("userId", $user->getId());
		
		$this->db->execute($stmt->getSQL());
	}
	
	/**
	 * 
	 * @param XWUser $user
	 * @param Group $group
	 */
	public function removeUserFromGroup(XWUser $user, Group $group){
		$sql="DELETE FROM TIMEREC_GROUPS_USERS WHERE GROUP_ID=#{groupId} AND USER_ID=#{userId}";
		$stmt=new XWSQLStatement($sql);
		$stmt->setInt("groupId", $group->getId());
		$stmt->setInt("userId", $user->getId());
		
		$this->db->execute($stmt->getSQL());
	}
	
	/**
	 * @return Group
	 * @param string $code
	 */
	public function loadGroupByInvitationCode($code):Group{
	    $sql = "SELECT G.*
				FROM TIMEREC_GROUPS G
				WHERE G.GROUP_INVITATIONCODE = :code
				ORDER BY G.GROUP_NAME";
	    $stmt=new XWSQLStatement($sql);
	    $stmt->setString("code", $code);
	    
	    $mapper=new PDBCObjectMapper();
	    return $mapper->querySingle($this->db, $stmt->getSQL(), Group::class);
	}
	
}