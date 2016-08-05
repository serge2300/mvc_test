<?php

namespace serge2300\MVCTest\Controllers;

use serge2300\MVCTest\Core\Controller;
use serge2300\MVCTest\Core\Router;
use serge2300\MVCTest\Core\Database;
use serge2300\MVCTest\Helpers\Alert;

class RegisterController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->insertAfter('layouts/header');
        $this->insertBefore('layouts/footer');
    }
    
    public function indexAction() 
    {
        Router::redirectTo('register/step1');
    }

    /**
     * Step 1
     */
    public function step1Action()
    {
        $this->setTitle('Регистрация > Шаг 1');

        if (isset($_SESSION['user']['id']))
            Router::redirectTo("user/profile/{$_SESSION['user']['id']}");
        
        // Set remembered values
        $this->view(['register' => isset($_SESSION['register']) ? $_SESSION['register'] : false]);

        if (!empty($_POST)) {
            $login = filter_var($_POST['login'], FILTER_SANITIZE_STRING);
            $password = password_hash($_POST['password'], PASSWORD_BCRYPT, ['cost' => 12]);
            $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
            $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
            // Add values to session
            foreach ($_POST as $field => $value)
                $_SESSION['register'][$field] = $$field;
            // Check if user with this login is already in the db
            if (Database::getConnection()->query("SELECT * FROM users WHERE login='$login' LIMIT 1")->fetch()) {
                Alert::set("Пользователь с таким логином уже существует!", "danger");
                Router::redirectTo("register/step1");
            }
            // Check if user with this email is already in the db
            if (Database::getConnection()->query("SELECT * FROM users WHERE email='$email' LIMIT 1")->fetch()) {
                Alert::set("Пользователь с таким e-mail уже существует!", "danger");
                Router::redirectTo("register/step1");
            }
            // Set the step the list of completed steps
            $_SESSION['register']['steps_completed'][] = 1;
            // Move to the next step
            Router::redirectTo("register/step2");
        }
    }

    /**
     * Step 2
     */
    public function step2Action()
    {
        $this->setTitle('Регистрация > Шаг 2');

        // Set remembered values
        $this->view(['register' => $_SESSION['register']]);

        // Check if the first step has been completed
        if (!in_array(1, $_SESSION['register']['steps_completed']))
            Router::redirectTo("register/step1");
        if (!empty($_POST)) {
            $country = filter_var($_POST['country'], FILTER_SANITIZE_STRING);
            $city = filter_var($_POST['city'], FILTER_SANITIZE_STRING);
            $phone = filter_var($_POST['phone'], FILTER_SANITIZE_STRING);
            // Add values to session
            foreach ($_POST as $field => $value)
                $_SESSION['register'][$field] = $$field;
            // Phone validation
            if (!preg_match('/\+/', $phone) or strlen($phone) < 11) {
                Alert::set("Введите правильный номер телефона!", "danger");
                Router::redirectTo("register/step2");
            }
            // Set the step the list of completed steps
            $_SESSION['register']['steps_completed'][] = 2;
            // Move to the next step
            Router::redirectTo("register/step3");
        }
    }

    /**
     * Step 3
     */
    public function step3Action()
    {
        $this->setTitle('Регистрация > Шаг 3');

        // Check if the second step has been completed
        if (!in_array(2, $_SESSION['register']['steps_completed']))
            Router::redirectTo("register/step2");
        if (!empty($_POST)) {
            if (empty($_FILES) or $_FILES['avatar']['error'] > 0) {
                Alert::set("Загрузите фотографию!", "danger");
                Router::redirectTo("register/step3");
            }

            // Photo upload
            $avatars_dir = __DIR__ . '/../../public/avatars';
            $avatar = $_FILES['avatar'];
            if (!file_exists($avatars_dir))
                mkdir($avatars_dir, 0777, true);
            // Generate filename
            $filename = $_SESSION['register']['avatar'] = md5($avatar['tmp_name'] . time()) . '.' . pathinfo($avatar['name'])['extension'];
            if (!move_uploaded_file($avatar['tmp_name'], $avatars_dir . '/' . $filename)) {
                Alert::set("Произошла ошибка!", "danger");
                Router::redirectTo("register/step3");
            }

            // Verify CAPTCHA
            if (!$this->verifyCaptcha()) {
                Alert::set("Вы не прошли проверку на CAPTCHA!", "danger");
                Router::redirectTo("register/step3");
            }

            // Add a user to the database
            $columns = [];
            $columns_bind = [];
            foreach ($_SESSION['register'] as $k => $v) {
                if (is_scalar($v)) {
                    $columns[] = $k;
                    $columns_bind[] = ":$k";
                }
            }
            
            $sql = "INSERT INTO users (" . join($columns, ', ') . ") VALUES (" . join($columns_bind, ', ') . ")";
            $query = Database::getConnection()->prepare($sql);
            foreach ($columns as $column) {
                $query->bindValue(":$column", $_SESSION['register'][$column]);
            }
            if ($query->execute()) {
                // Log user in
                $user_id = (int) Database::getConnection()->lastinsertid();
                $_SESSION['user']['id'] = $user_id;
                // Unset registration data
                unset($_SESSION['register']);
                Alert::set("Вы успешно зарегистрировались!", "success");
                // Redirect a user to his profile
                Router::redirectTo("user/profile/$user_id");
            } else {
                Alert::set("Произошла ошибка!", "danger");
            }
        }
    }

    /**
     * Verify if a user has passed CAPTCHA test
     *
     * @return bool
     */
    private function verifyCaptcha() {
        global $config;
        if (!isset($_POST['g-recaptcha-response']))
            return false;
        $response = $_POST['g-recaptcha-response'];

        $fields = http_build_query([
            'secret'   => $config['captcha']['secret_key'],
            'response' => $response
        ]);
        curl_setopt_array($ch = curl_init(), [
            CURLOPT_URL            => $config['captcha']['url'],
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $fields,
            CURLOPT_RETURNTRANSFER => true
        ]);
        $result = json_decode(curl_exec($ch));
        curl_close($ch);

        return $result->success == true;
    }
}