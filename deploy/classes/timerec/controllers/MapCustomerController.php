<?php
namespace timerec\controllers;

use core\modules\controllers\XWModulePageController;
use core\modules\controllers\XWModulePageRenderingResult;
use core\net\XWRequest;
use timerec\daos\CustomerDAO;
use timerec\daos\GroupDAO;
use xw\entities\users\XWUserDAO;

class MapCustomerController extends XWModulePageController{
    public function result(): XWModulePageRenderingResult{
        $result=new XWModulePageRenderingResult();
        $model=[];

        if(XWUserDAO::instance()->isCurrentUserValid() && XWRequest::instance()->exists("customerId")){
            $customer = CustomerDAO::instance()->loadCustomer(XWRequest::instance()->getInt("customerId"));
            $group = GroupDAO::instance()->loadGroup($customer->getGroupId());
            $model["isOwner"]=$group->getOwnerId()==$_SESSION["XWUSER"]->getId();
            $model["group"]=$group;
            $model["customer"]=$customer;
            $model["recordedTime"]=false;
            if($group->getOwnerId()==$_SESSION["XWUSER"]->getId() || GroupDAO::instance()->isMemberOfGroup($group, $_SESSION["XWUSER"])){
                $model["query"] = preg_replace("/[^a-zA-Z0-9.-äÄüÜöÖß]/", "+", $customer->getAddress());
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