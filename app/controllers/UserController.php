<?php

namespace serge2300\MVCTest\Controllers;

use serge2300\MVCTest\Core\Controller;
use serge2300\MVCTest\Core\Router;
use serge2300\MVCTest\Core\Database;
use serge2300\MVCTest\Helpers\Alert;

class UserController extends Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->insertAfter('layouts/header');
        $this->insertBefore('layouts/footer');
    }
    
    public function indexAction() 
    {
        Router::redirectTo('user/login');
    }

    /**
     * Login
     */
    public function loginAction()
    {
        $this->setTitle('Войти');

        if (isset($_SESSION['user']['id']))
            Router::redirectTo("user/profile/{$_SESSION['user']['id']}");

        if (!empty($_POST)) {
            $login = filter_var($_POST['login'], FILTER_SANITIZE_STRING);
            $password = filter_var($_POST['password'], FILTER_SANITIZE_STRING);
            $user = Database::getConnection()->query("SELECT * FROM users WHERE login='$login' LIMIT 1")->fetch(\PDO::FETCH_ASSOC);
            // Check if a user exists
            if (!$user) {
                Alert::set("Пользователь не найден!", "danger");
                Router::redirectTo("user/login");
            }
            // Check if password matches
            if (!password_verify($password, $user['password'])) {
                Alert::set("Пароль неправильный!", "danger");
                Router::redirectTo("user/login");
            }
            // Log user in
            $_SESSION['user']['id'] = $user['id'];
            Router::redirectTo("user/profile/{$user['id']}");
        }
    }

    /**
     * Logout
     */
    public function logoutAction()
    {
        unset($_SESSION['user']);
        Router::redirectTo('');
    }

    /**
     * Show user profile
     *
     * @param $user_id
     */
    public function profileAction($user_id)
    {
        $this->setTitle('Профиль пользователя');
        
        if (!$user_id or !isset($_SESSION['user']['id']) or $_SESSION['user']['id'] != $user_id)
            Router::redirectTo('user/login');
        // Get user info
        $sql = "SELECT * FROM users WHERE id=$user_id LIMIT 1";
        $this->view([
            'user' => Database::getConnection()->query($sql)->fetch(\PDO::FETCH_ASSOC)
        ]);
    }

    /**
     * Verify user's phone number
     *
     * @param $user_id
     */
    public function verifyPhoneAction($action = null)
    {
        // Send SMS
        if ($action == 'send') {
            global $config;
            if (!isset($_SESSION['user']['id'])) {
                echo false;
                exit;
            }
            
            $user_id = $_SESSION['user']['id'];
            $user = Database::getConnection()->query("SELECT * FROM users WHERE id=$user_id LIMIT 1")->fetch(\PDO::FETCH_ASSOC);
            $phone = ltrim($user['phone'], '+');

            $fields = http_build_query([
                'login'   => $config['sms']['login'],
                'psw'     => $config['sms']['psw'],
                'sender'  => $config['sms']['sender'],
                'phones'  => $phone,
                'mes'     => $code = rand(1111, 9999)
            ]);
            curl_setopt_array($ch = curl_init(), [
                CURLOPT_URL            => $config['sms']['url'],
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $fields,
                CURLOPT_RETURNTRANSFER => true
            ]);
            curl_exec($ch);
            curl_close($ch);

            $_SESSION['verification_code'] = $code;
            echo true;
            exit;
        // Verify code
        } elseif ($action == null and !empty($_POST)) {
            if (!isset($_SESSION['user']['id']))
                Router::redirectTo('');
            $user_id = $_SESSION['user']['id'];
            if (isset($_SESSION['verification_code']) and $_POST['code'] == $_SESSION['verification_code']) {
                Alert::set("Вы успешно подтвердили номер телефона!", "success");
                Database::getConnection()->query("UPDATE users SET verified=1 WHERE id=$user_id LIMIT 1");
                unset($_SESSION['verification_code']);
            } else {
                Alert::set("Неправильный код!", "danger");
            }
            Router::redirectTo("user/profile/$user_id");
        }
    }

}