<?php
namespace timerec\controllers;

use core\net\XWRequest;
use xw\entities\users\XWUserDAO;
use core\modules\controllers\XWModulePageRenderingResult;
use core\modules\controllers\XWModulePageController;
use xw\entities\users\XWUserManagmentDAO;
use xw\entities\users\XWUser;
use core\utils\displayMessages\DisplayMessageFactory;
use core\addons\XWAddonManager;
use core\mail\SMTPMailerFactory;

class RegisterController extends XWModulePageController{
    private function isInputValid(){
        $result = false;
        if(XWRequest::instance()->exists("username") && XWRequest::instance()->exists("captcha")){
            if(isset($_SESSION["TIMEREC_CAPTCHA"]) && $_SESSION["TIMEREC_CAPTCHA"] == XWRequest::instance()->get("captcha")){
                if(XWRequest::instance()->get("userpassword") == XWRequest::instance()->get("userpassword2") && strlen(XWRequest::instance()->get("userpassword"))>0){
                    $user = XWUserManagmentDAO::instance()->loadUserByName(XWRequest::instance()->get("username"));
                    if($user->getId() == 0){
                        if(filter_var(XWRequest::instance()->get("username"), FILTER_VALIDATE_EMAIL)){
                            $result = true;  
                        }
                        else{
                            DisplayMessageFactory::instance()->addDisplayMessage("Error", "Not a valid email-address");
                        }
                    }
                    else{
                        DisplayMessageFactory::instance()->addDisplayMessage("Error", "User already exists!");
                    }
                }
                else{
                    DisplayMessageFactory::instance()->addDisplayMessage("Error", "Password are not equal!");
                }
            }
            else{
                DisplayMessageFactory::instance()->addDisplayMessage("Error", "Wrong Captcha-value!");
            }
        }
        return $result;
    }
    
    private function sendMail(XWUser $user){
        try{
            $mailSubject = "Thank you for using " . $_SERVER["HTTP_HOST"];
            $mailText = "If you have any problems or other ideas to improve this application, please send us an email.";
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
        
        if(!XWUserDAO::instance()->isCurrentUserValid()){            
            if($this->isInputValid()){
                $user = new XWUser();
                $user->setName(XWRequest::instance()->get("username"));
                $user->setEmail(XWRequest::instance()->get("username"));
                $user->save(XWRequest::instance()->get("userpassword"));
                
                DisplayMessageFactory::instance()->addDisplayMessage("Saved", "New user-account created successfully.");
                
                $this->sendMail($user);
                
                $user = XWUserManagmentDAO::instance()->loadUserByName($user->getName());
                $_SESSION["XWUSER"] = $user;
                
                $indexController = new IndexController();
                $result = $indexController->result();
                $result->setAlternativeTemplate("index.html");
            }
            else{
               
                $captchaValue1=rand(1,15);
                $captchaValue2=rand(1,15);
                $captchaResult=$captchaValue1 + $captchaValue2;
                $_SESSION["TIMEREC_CAPTCHA"] = $captchaValue2;
            
                $model["captchaValue1"]=$captchaValue1;
                $model["captchaResult"]=$captchaResult;
            
                $model["username"] = "";
                if(XWRequest::instance()->exists("username")){
                    /** @var \XWParserToolKit $ptk */
                    $ptk = XWAddonManager::instance()->getAddonByName("XWParserToolKit");
                    $model["username"] = $ptk->disableHtml(XWRequest::instance()->get("username"));
                }
            
                $result->setModel($model);
            }
        }
        else{
            $indexController = new IndexController();
            $result = $indexController->result();
            $result->setAlternativeTemplate("index.html");
        }        
        
        return $result;
    }
}