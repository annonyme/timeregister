<?php
namespace timerec\controllers;

use core\modules\controllers\XWModulePageController;
use core\modules\controllers\XWModulePageRenderingResult;
use core\net\XWRequest;
use timerec\daos\CustomerDAO;
use timerec\daos\EntryDAO;
use timerec\daos\GroupDAO;
use timerec\entities\Entry;
use xw\entities\users\XWUserDAO;
use xw\entities\users\XWUserManagmentDAO;

class ExportController extends XWModulePageController{
    public function result(): XWModulePageRenderingResult{
        $result=new XWModulePageRenderingResult();
        //$model=[];
    
        if(XWUserDAO::instance()->isCurrentUserValid() && XWRequest::instance()->exists("customerId")){
            $customer = CustomerDAO::instance()->loadCustomer(XWRequest::instance()->getInt("customerId"));
            $group = GroupDAO::instance()->loadGroup($customer->getGroupId());
            if($group->getOwnerId()==$_SESSION["XWUSER"]->getId()){
                $entries = EntryDAO::instance()->loadEntryListByCustomer($customer);
                
                $header = ["customer","user","date","time","from","to","comment",];
                $rows = [];
                $unexported = [];
                /** @var \timerec\entities\Entry $entry */
                foreach($entries as $entry){
                    if(!$entry->isPayed() && EntryDAO::instance()->isNotStopped($entry)){
                        $unexported[] = $entry;
                        $user = XWUserManagmentDAO::instance()->loadUser($entry->getUserId());
                        
                        $row = [
                            $customer->getName(),
                            $user->getName(),
                            EntryDAO::instance()->getDate($entry),
                            EntryDAO::instance()->getTime($entry),
                            $entry->getStart(),
                            $entry->getStop(),
                            $entry->getComment(),
                        ];
                        $rows[] = $row;
                    }
                }
                
                foreach($unexported as $exported){
                    $exported->setPayed(true);
                    EntryDAO::instance()->saveEntry($exported);
                }
                
                //CSV Export
                
                header("Content-type: text/csv");
                header("Content-Disposition: attachment; filename=timerec-".$customer->getId()."-".time().".csv");
                header("Pragma: no-cache");
                header("Expires: 0");
                echo implode(";", $header)."\n";
                foreach ($rows as $row){
                    echo implode(";", $row)."\n";
                }
                
                exit();
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