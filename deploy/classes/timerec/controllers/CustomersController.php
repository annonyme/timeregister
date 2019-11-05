<?php
namespace timerec\controllers;

use core\modules\controllers\XWModulePageController;
use core\modules\controllers\XWModulePageRenderingResult;
use core\net\XWRequest;
use timerec\daos\GroupDAO;
use timerec\daos\CustomerDAO;

class CustomersController extends XWModulePageController{
    public function result(): XWModulePageRenderingResult{
		$result=new XWModulePageRenderingResult();
		$model=[];
	
		if(isset($_SESSION["XWUSER"]) && $_SESSION["XWUSER"]->getId() > 0 && XWRequest::instance()->exists("groupId")){
			$group = GroupDAO::instance()->loadGroup(XWRequest::instance()->getInt("groupId"));
			$model["isOwner"]=$group->getOwnerId()==$_SESSION["XWUSER"]->getId();
			$model["group"]=$group;
			if($group->getOwnerId()==$_SESSION["XWUSER"]->getId() || GroupDAO::instance()->isMemberOfGroup($group, $_SESSION["XWUSER"])){
				$model["customers"]=CustomerDAO::instance()->loadCustomerListByGroup($group);
				$model["fullCount"]=count($model["customers"]);
				$result->setModel($model);
			}
			else{
			    $indexController = new IndexController();
			    $result = $indexController->result();
			    $result->setAlternativeTemplate("index.html");
			}
		}		
		
		return $result;
	}
}
