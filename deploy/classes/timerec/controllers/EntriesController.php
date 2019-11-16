<?php
namespace timerec\controllers;

use core\modules\controllers\XWModulePageController;
use core\net\XWRequest;
use timerec\daos\GroupDAO;
use timerec\daos\CustomerDAO;
use timerec\daos\EntryDAO;
use core\modules\controllers\XWModulePageRenderingResult;
use timerec\entities\Entry;
use xw\entities\users\XWUserDAO;
use core\addons\XWAddonManager;
use core\utils\displayMessages\DisplayMessageFactory;
use core\utils\dates\XWCalendar;
use xw\entities\users\XWUserManagmentDAO;
use hannespries\events\EventHandler;

class EntriesController extends XWModulePageController{        
    /**
     * @param array $entries
     * @return null|Entry
     */
    private function findLastOpenEntryOfUser($entries){
        $result = null;
        foreach ($entries as $entry){
            if(EntryDAO::instance()->isNotStopped($entry) &&  $entry->getUserId() == XWUserDAO::instance()->getCurrentUser()->getId()){
                $result = $entry;
            }
        }
        return $result;
    }

    public function result(): XWModulePageRenderingResult{
	    $result=new XWModulePageRenderingResult();
	    $model=[];
	
		/** @var $messages DisplayMessageFactory */
		$messages = Services::getContainer()->get('messages');

		if(XWUserDAO::instance()->isCurrentUserValid() && $this->getRequest()->exists("customerId")){
			/** @var $events hannespries\events\EventHandler */
			$events = Services::getContainer()->get('events');			
			
			$customer = CustomerDAO::instance()->loadCustomer($this->getRequest()->getInt("customerId"));
			$group = GroupDAO::instance()->loadGroup($customer->getGroupId());
			$model["isOwner"]=$group->getOwnerId()==$_SESSION["XWUSER"]->getId();
			$model["group"]=$group;
			$model["customer"]=$customer;
			$model["recordedTime"]=false;
			if($group->getOwnerId()==$_SESSION["XWUSER"]->getId() || GroupDAO::instance()->isMemberOfGroup($group, $_SESSION["XWUSER"])){
			    $entries = EntryDAO::instance()->loadEntryListByCustomer($customer);
			    if($this->getRequest()->exists("recordTime")){				    
				    $lastEntry = $this->findLastOpenEntryOfUser($entries);
				    $cal = new XWCalendar();
				    if($lastEntry !== null){
				        $lastEntry->setStop($cal->getMySQLDateString());
				        if($this->getRequest()->exists("timeComment") && strlen($this->getRequest()->get("timeComment")) > 0){
				            $comment = XWAddonManager::instance()->getAddonByName("XWParserToolKit")->disableHTML($this->getRequest()->get("timeComment"));
				            $lastEntry->setComment($comment);
						}
						
						$events->fireFilterEvent('timerec_stop_presave', $lastEntry, ['subject' => $this]);
						EntryDAO::instance()->saveEntry($lastEntry);
						$events->fireFilterEvent('timerec_stop_postsave', $lastEntry, ['subject' => $this]);
	
						$messages->addDisplayMessage('Saved', "Time-Record for  '".$customer->getName()."' stoped.");
					}
					else{
						$entry=new Entry();						
						$entry->setCustomerId($customer->getId());
						$entry->setStart($cal->getMySQLDateString());
						$entry->setUserId(XWUserDAO::instance()->getCurrentUser()->getId());
						if($this->getRequest()->exists("timeComment") && strlen($this->getRequest()->get("timeComment")) > 0){
							$comment = XWAddonManager::instance()->getAddonByName("XWParserToolKit")->disableHTML($this->getRequest()->get("timeComment"));
							$entry->setComment($comment);
						}
						
						$events->fireFilterEvent('timerec_start_presave', $lastEntry, ['subject' => $this]);
						EntryDAO::instance()->saveEntry($lastEntry);
						$events->fireFilterEvent('timerec_start_postsave', $lastEntry, ['subject' => $this]);

						//refresh list
						$model["entries"]=EntryDAO::instance()->loadEntryListByCustomer($customer);

						$messages->addDisplayMessage('Saved', "Time-Record for  '".$customer->getName()."' started.");
					}
					$model["recordedTime"]=true;	
					$entries = EntryDAO::instance()->loadEntryListByCustomer($customer);
				}				
				
				$processed = [];
				$model["running"] = $model["running"] = EntryDAO::instance()->isRunning($entries);
				
				/** @var Entry $entry */
				foreach ($entries as $entry){
				    if($model["isOwner"] || $entry->getUserId() == XWUserDAO::instance()->getCurrentUser()->getId()){
				        $pro = [
							"model" => $entry, 
							"time" => "running", 
							"date" => EntryDAO::instance()->getDate($entry), 
							"dateEnd" => EntryDAO::instance()->getEndDate($entry), 
							"payed" => $entry->isPayed() ? 'exported' : '-'
						];
				        if(EntryDAO::instance()->isStopped($entry)){
				            $pro['time'] = EntryDAO::instance()->getTime($entry);
				        }
				        
				        $pro['username'] = XWUserDAO::instance()->getCurrentUser()->getName();
				        if(XWUserDAO::instance()->getCurrentUser()->getId() != $entry->getUserId()){
				            $user = XWUserManagmentDAO::instance()->loadUser($entry->getUserId());
				            if($user !== null && $user->getId() > 0){
				                if(preg_match("/\@/", $user->getName())){
				                    $parts = preg_split("/\@/", $user->getName());
                                    $pro['username'] = $parts[0];
                                }
				                else{
                                    $pro['username'] = $user->getName();
                                }
				            }
				        }
				        
				        $processed[] = $pro;
				    }				    
				}
				$model["entries"] = $processed;
				
				$result->setModel($model);
			}
			else{
				$messages->addDisplayMessage('Session timeout', 'You have to loggin one more time.');
				
				$indexController = new IndexController();
			    $result = $indexController->result();
			    $result->setAlternativeTemplate('index.html');
			}
		}	
		
		return $result;
	}
}
