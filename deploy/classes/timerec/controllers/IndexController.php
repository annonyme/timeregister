<?php
namespace timerec\controllers;

use core\modules\controllers\XWModulePageController;
use core\modules\controllers\XWModulePageRenderingResult;
use timerec\daos\GroupDAO;
use core\addons\XWAddonManager;
use core\net\XWRequest;
use xw\entities\users\XWUserDAO;
use core\utils\displayMessages\DisplayMessageFactory;

class IndexController extends XWModulePageController{
    public function result(): XWModulePageRenderingResult{
		$result=new XWModulePageRenderingResult();
		$model=[];
		
		if(isset($_SESSION["XWUSER"]) && $_SESSION["XWUSER"]->getId() > 0){
		    $model["ownGroups"]=GroupDAO::instance()->loadGroupListByOwner($_SESSION["XWUSER"]);
		    $model["memberGroups"]=GroupDAO::instance()->loadGroupListByUser($_SESSION["XWUSER"]);
		    $model["fullCount"]=count($model["ownGroups"])+count($model["memberGroups"]);
		    if($this->getRequest()->exists("invitationcode")){
		        $group = GroupDAO::instance()->loadGroupByInvitationCode($this->getRequest()->getString("invitationcode"));
		        
		        $found = false;
		        foreach ($model["memberGroups"] as $g){
		            if($g->getId() == $group->getId()){
		                $found = true;
		            }
		        }
		        
		        if(!$found){
		            $user = XWUserDAO::instance()->getCurrentUser();
		            
		            if($user && $user->getId() > 0){
						GroupDAO::instance()->addUserToGroup($user, $group);
						
						/** @var $messages DisplayMessageFactory */
						$messages = Services::getContainer()->get('messages');
		                $messages->addDisplayMessage("Added User", "User '".$user->getName()."' added to " . $group->getName() . ".");
		                
		                $model["ownGroups"]=GroupDAO::instance()->loadGroupListByOwner($_SESSION["XWUSER"]);
		                $model["memberGroups"]=GroupDAO::instance()->loadGroupListByUser($_SESSION["XWUSER"]);
		                $model["fullCount"]=count($model["ownGroups"])+count($model["memberGroups"]);
		            }
		        }		        
		    }	    
		}
		else{
			$model["secToken"] = XWAddonManager::instance()->getAddonByName("XWUserSession")->getSessionSecToken();
			$result->setAlternativeTemplate("login.html");
		}
		
		$result->setModel($model);
		return $result;
	}
}