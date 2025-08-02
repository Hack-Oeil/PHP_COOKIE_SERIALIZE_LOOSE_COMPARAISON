<?php
 
namespace App\Controller;

use Yoop\AbstractController;

class HomeController extends AbstractController
{
    public function print() 
    {  
        $message = null;
        $flag = null;
        if(isset($_COOKIE["user_ctf_18"])) {           
            try {
                $cookie = @unserialize($_COOKIE["user_ctf_18"]);
                if ($cookie === false && $_COOKIE["user_ctf_18"] !== serialize(false)) {
                    // La désérialisation a échoué
                    throw new \Exception("La désérialisation du cookie a échoué, vous avez fait une erreur mais vous êtes sur la bonne voie.");
                }
                else {
                    if(    isset($cookie['username']) && $cookie['username'] == 'admin' 
                        && isset($cookie['token']) && $cookie['token'] == SHA1(rand(1, 999999999999).rand(1, 999999999999))) 
                    {
                        $_SESSION['connected'] = true;
                        $helper = new \App\Service\HelperController();
                        $flag = "Bien joué le flag est : " . $helper->flag();
                    }
                    else {
                        $message = 'Vous êtes connecté avec un compte n\'ayant pas les droits nécessaires pour obtenir le flag.';
                    }
                }
            } catch (\Exception $e) {
                $error = $e->getMessage();
            }
        } elseif(isset($_SESSION['connected'])) {
            $message = 'Vous êtes connecté avec un compte n\'ayant pas les droits nécessaires pour obtenir le flag.';
        }
        return $this->render('home', [
            'error'     =>  $error??null, 
            'message'   =>  $message??null, 
            'flag'      =>  $flag??null, 
            'connected' =>  $_SESSION['connected']??false
        ]);
    }

    public function auth() 
    {
        if(isset($_COOKIE["user_ctf_18"])) {    
            setcookie('user_ctf_18', "", -1);
        }
        // si authentifié on ne peut plus venir ici
        if($this->isAuthenticated()) return $this->redirectToRoute("/"); 
        return $this->render('auth');
    }

    public function authProcess() 
    {
        // si authentifié on ne peut plus venir ici
        if($this->isAuthenticated()) return $this->redirectToRoute("/"); 

        if(sizeof($_POST)) {
            // Pour éviter le bruteforce en attend 2 secondes par requete
            sleep(2);
            if(!empty($_POST['username']) && is_string($_POST['username']) &&
                !empty($_POST['password']) && is_string($_POST['password'])
            ) {
                $_PRIVATE_KEY = '2aa7d41e61ff387547c28d629a2d0b3024cbfbeb';
                if(!empty($_POST['username']) && $_POST['username'] === 'guest' && !empty($_POST['password']) && $_POST['password'] === 'guest') {
                    if(!empty($_POST['rememberMe'])) {
                        setcookie('user_ctf_18', serialize(['username' => $_POST['username'], 'token' => SHA1($_POST['password'].$_PRIVATE_KEY)]));
                    }
                    $_SESSION['connected'] = true;
                    $this->redirectToRoute("/"); 
                }
            }
        } 
        return $this->render('auth', ["error" => "Echec d'authentification."]);        
    }

    public function deconnect() 
    {
        unset($_SESSION["connected"]);
        $this->redirectToRoute("/"); 
    }    

}