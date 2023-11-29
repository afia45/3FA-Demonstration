<?php
namespace Classes;
use PragmaRX\Google2FA\Google2FA;

class GoogleAuth 
{
    public $google2fa;
    public $userKey;
    public $otp;

    public function __construct($post = null)
    {
        $this->google2fa = new Google2FA();

        if(!isset($_SESSION["userKey"]))
        {
            // get key from database if exists.

            $this->userKey = (new User)->getKeyFromUserAccount($post);
            
            if(!$this->userKey)
            {
                $this->userKey = $this->google2fa->generateSecretKey();

                (new User)->addKeyToUserAccount($post, $this->userKey);
            }

            $_SESSION["userKey"] = $this->userKey;

        }
        else
        {
            $this->userKey = $_SESSION["userKey"];
        }

        return $this->userKey;
    }

    public function verifyFromGoogle($code)
    {
        $window = 8;

        $valid = $this->google2fa->verifyKey($_SESSION["userKey"], $code, $window);

        if($valid)
        {
            $_SESSION['message'] = 'Google Authentication Completed Successfully';
            $_SESSION['status'] = 'success';

            header("location: success.php");
            exit;
        }

        $_SESSION['message'] = '3-Factor Authentication Failed';
        $_SESSION['status'] = 'danger';

        header("location: index.php");
        exit;
    }

    public function getOtp()
    {
        return $this->google2fa->getCurrentOtp($this->userKey);
    }
}
