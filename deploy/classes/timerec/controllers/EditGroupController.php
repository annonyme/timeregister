<?php
namespace timerec\controllers;

use core\addons\XWAddonManager;
use core\modules\controllers\XWModulePageController;
use core\modules\controllers\XWModulePageRenderingResult;
use core\net\XWRequest;
use core\utils\displayMessages\DisplayMessageFactory;
use timerec\daos\GroupDAO;
use xw\entities\users\XWUserManagmentDAO;
use xw\entities\users\XWUser;
use core\mail\SMTPMailerFactory;
use core\utils\XWCodeGenerator;

class EditGroupController extends XWModulePageController{
    private function sendMail(XWUser $user, $group){
        try{
            $mailSubject = "You was added to a group " . $_SERVER["HTTP_HOST"];
            $mailText = "You are now a member of '" . $group->getName() . "'.";
            $mailer = SMTPMailerFactory::instance()->createMailer();
            if($mailer->isValid()){
                $mailer->send("info@" . $_SERVER["HTTP_HOST"], null, [$user->getEmail()], $mailSubject, $mailText, false);
            }            
        }
        catch(\Exception $e){
            echo $e->getMessage();
        }
    }

    public function result(): XWModulePageRenderingResult{
        $result=new XWModulePageRenderingResult();
        $model=[];

        if(isset($_SESSION["XWUSER"]) && $_SESSION["XWUSER"]->getId() > 0 && XWRequest::instance()->exists("groupId")){
            $group = GroupDAO::instance()->loadGroup(XWRequest::instance()->getInt("groupId"));
            
            if($group->getInvitationCode() == ""){
                $group->setInvitationCode(strtoupper(XWCodeGenerator::instance()->generate(8)));
                if($group->getId() > 0){
                    $group = GroupDAO::instance()->saveGroup($group);
                }                
            }
            
            $model["isOwner"]=$group->getOwnerId()==$_SESSION["XWUSER"]->getId();
            $model["group"]=$group;
            $model["exists"]=$group->getId() > 0;
            if($group->getOwnerId()==$_SESSION["XWUSER"]->getId() || $group->getId() == 0){
                if(XWRequest::instance()->exists("groupName")){
                    /** @var \XWParserToolKit $ptk */
                    $ptk = XWAddonManager::instance()->getAddonByName("XWParserToolKit");
                    $group->setName($ptk->disableHtml(XWRequest::instance()->get("groupName")));
                    $group->setDescription($ptk->disableHtml(XWRequest::instance()->get("groupDescription")));
                    $group->setOwnerId($_SESSION["XWUSER"]->getId());
                    
                    $group = GroupDAO::instance()->saveGroup($group);

                    DisplayMessageFactory::instance()->addDisplayMessage("Saved", "Group '".$group->getName()."' saved.");
                }
                else if(XWRequest::instance()->exists("userName")){
                    $user = XWUserManagmentDAO::instance()->loadUserByName(XWRequest::instance()->getString("userName"));
                    if($user && $user->getId() > 0){
                        GroupDAO::instance()->addUserToGroup($user, $group);
                        $this->sendMail($user, $group);
                        DisplayMessageFactory::instance()->addDisplayMessage("Added User", "User '".$user->getName()."' added.");
                    }
                    else{
                        DisplayMessageFactory::instance()->addDisplayMessage("Error", "User doesn't exists");
                    }
                }
                else if(XWRequest::instance()->exists("memberId")){
                    $user = XWUserManagmentDAO::instance()->loadUser(XWRequest::instance()->getInt("memberId"));
                    if($user && $user->getId() > 0){
                        GroupDAO::instance()->removeUserToGroup($user, $group);
                        DisplayMessageFactory::instance()->addDisplayMessage("Removed User", "User '".$user->getName()."' removed.");
                    }
                    else{
                        DisplayMessageFactory::instance()->addDisplayMessage("Error", "User doesn't exists");
                    }
                }
                
                $model["members"] = GroupDAO::instance()->loadUserListByGroup($group);
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