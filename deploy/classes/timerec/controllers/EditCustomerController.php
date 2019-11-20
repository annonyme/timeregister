<?php
namespace timerec\controllers;

use core\modules\controllers\XWModulePageController;
use core\modules\controllers\XWModulePageRenderingResult;
use core\net\XWRequest;
use timerec\daos\GroupDAO;
use timerec\daos\CustomerDAO;
use core\addons\XWAddonManager;
use core\utils\displayMessages\DisplayMessageFactory;

class EditCustomerController extends XWModulePageController{
    public function result(): XWModulePageRenderingResult{
        $result=new XWModulePageRenderingResult();
        $model=[];

        if(isset($_SESSION["XWUSER"]) && $_SESSION["XWUSER"]->getId() > 0 && XWRequest::instance()->exists("customerId")){
            $customer = CustomerDAO::instance()->loadCustomer(XWRequest::instance()->getInt("customerId"));
            $group = null;
            if(XWRequest::instance()->exists("groupId") && $customer->getGroupId() == 0){
                $group = GroupDAO::instance()->loadGroup(XWRequest::instance()->getInt("groupId"));
            }
            else{
                $group = GroupDAO::instance()->loadGroup($customer->getGroupId());
            }
            
            $model["isOwner"]=$group->getOwnerId()==$_SESSION["XWUSER"]->getId();
            $model["group"]=$group;
            if($group->getOwnerId()==$_SESSION["XWUSER"]->getId()){
                if(XWRequest::instance()->exists("customerName")){
                    /** @var \XWParserToolKit $ptk */
                    $ptk = XWAddonManager::instance()->getAddonByName("XWParserToolKit");
                    $customer->setName($ptk->disableHtml(XWRequest::instance()->getString("customerName")));
                    $customer->setEmail($ptk->disableHtml(XWRequest::instance()->getString("customerEmail")));
                    $customer->setPhone($ptk->disableHtml(XWRequest::instance()->getString("customerPhone")));
                    $customer->setAddress($ptk->disableHtml(XWRequest::instance()->getString("customerAddress")));
                    $customer->setGroupId($group->getId());
                    $customer = CustomerDAO::instance()->saveCustomer($customer);
                    
                    $msg = \sprintf($this->getDictionary()->get('saved_customer_message'), $customer->getName());
                    DisplayMessageFactory::instance()->addDisplayMessage($this->getDictionary()->get('saved_label'), $msg);
                }                
                
                $model["customer"] = $customer;
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