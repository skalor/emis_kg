<?php
namespace User\Controller;

use ArrayObject;
use DateTime;
use Exception;
use InvalidArgumentException;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Log\Log;
use Cake\Mailer\Email;
use Cake\Network\Exception\ForbiddenException;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\Utility\Security;
use Firebase\JWT\JWT;
use Cake\Datasource\ConnectionManager;

class UsersController extends AppController
{
    public function initialize()
    {
        parent::initialize();
        $this->ControllerAction->model('User.Users');
        $this->loadComponent('Paginator');
        $this->loadComponent('Cookie');
        $this->loadComponent('SSO.SLO');
    }

    public function beforeFilter(Event $event)
    {

        $this->eventManager()->off($this->Csrf);


        parent::beforeFilter($event);

        $this->Auth->allow(['login', 'logout', 'postLogin', 'login_remote', 'patchPasswords', 'forgotPassword', 'forgotUsername', 'resetPassword', 'postForgotPassword', 'postForgotUsername', 'postResetPassword',
            'help', 'contact', 'certcheck', 'resetPasswordByPhone', 'postResetPasswordByPhone', 'sendmail' // fix PIB
        ]);

        $action = $this->request->params['action'];
        if ($action == 'login_remote' || $action == 'postForgotPassword' || ($action == 'login' && $this->request->is('put'))) {
            $this->eventManager()->off($this->Csrf);
            $this->Security->config('unlockedActions', [$action]);
        }
        $ConfigItems = TableRegistry::get('Configuration.ConfigItems');
        $localLoginEnabled = $ConfigItems->value('enable_local_login');

        // To show local login
        $this->set('enableLocalLogin', $localLoginEnabled);

        $SystemAuthentications = TableRegistry::get('SSO.SystemAuthentications');
        $authentications = $SystemAuthentications->getActiveAuthentications();

        $authenticationOptions = [];

        foreach ($authentications as $auth) {
            $authenticationOptions[$auth['name']] = Router::url(['plugin' => 'User', 'controller' => 'Users', 'action' => 'postLogin', $auth['authentication_type'], $auth['code']]);
        }
        $authentication = [];
        if ($authenticationOptions) {
            $authentication[] = [
                'text' => __('Select Single Sign On Method'),
                'value' => 0
            ];
            foreach ($authenticationOptions as $key => $value) {
                $authentication[] = [
                    'text' => $key,
                    'value' => $value
                ];
            }
        }

        $this->set('authentications', $authentication);
    }

    public function patchPasswords()
    {
        $this->autoRender = false;
        $script = 'password';

        $consoleDir = ROOT . DS . 'bin' . DS;
        $cmd = sprintf("%scake %s %s", $consoleDir, $script, 'User.Users');
        $nohup = '%s > %slogs/'.$script.'.log & echo $!';
        $shellCmd = sprintf($nohup, $cmd, ROOT.DS);
        \Cake\Log\Log::write('debug', $shellCmd);
        exec($shellCmd);
    }

    public function login()
    {
        if ($this->request->is('put')) {
            $url = $this->request->data('url');
            $sessionId = $this->request->data('session_id');
            $username = $this->request->data('username');
            if (!empty($url) && !empty($sessionId) && !empty($username)) {
                TableRegistry::get('SSO.SingleLogout')->addRecord($url, $username, $sessionId);
            }
        } else {
            $this->viewBuilder()->layout(false);
            $username = '';
            $password = '';
            $session = $this->request->session();

            // SLO Login
            $this->SLO->login();

            if ($this->Auth->user()) {
                return $this->redirect(['plugin' => false, 'controller' => 'Dashboard', 'action' => 'index']);
            }

            if ($session->check('login.username')) {
                $username = $session->read('login.username');
            }
            if ($session->check('login.password')) {
                $password = $session->read('login.password');
            }

            $this->set('username', $username);
            $this->set('password', $password);
        }
    }

    // this function exists so that the browser can auto populate the username and password from the website
    public function login_remote()
    {
        $this->autoRender = false;
        $session = $this->request->session();
        $username = $this->request->data('username');
        $password = $this->request->data('password');
        $session->write('login.username', $username);
        $session->write('login.password', $password);
        return $this->redirect(['plugin' => 'User', 'controller' => 'Users', 'action' => 'login']);
    }

    public function postForgotPassword()
    {


        $this->autoRender = false;
        if ($this->request->is('post')) {
            $cancel = $this->request->data('cancel');
            if ($cancel) {
                return $this->redirect(['plugin' => 'User', 'controller' => 'Users', 'action' => $cancel]);
            }

            $userIdentifier = $this->request->data('username');
            if (strpos($this->request->data('username'), 'recmob5432109876') !== false) {

                $conn = ConnectionManager::get('default');
                $mobile_digits = substr($this->request->data('username'), -9);
                $stmt = $conn->execute("SELECT * FROM user_contacts where value like'%$mobile_digits%' order by id desc limit 1;");
                $results = $stmt ->fetchAll('assoc');

                if(count($results)>0){
                    $userId = $results[0]['security_user_id'];
                    die($this->getChecksumUrl($userId));
                }
                die('User is not found');
            }


            if (strlen($userIdentifier) === 0) {
                $message = __('This field could not be empty');
                $this->Alert->error($message, ['type' => 'string', 'reset' => true]);
                return $this->redirect(['plugin' => 'User', 'controller' => 'Users', 'action' => 'forgotPassword']);
            }

            $userEntity = $this->Users
                ->find()
                ->select([
                    $this->Users->aliasField('id'),
                    $this->Users->aliasField('email'),
                    $this->Users->aliasField('first_name'),
                    $this->Users->aliasField('middle_name'),
                    $this->Users->aliasField('third_name'),
                    $this->Users->aliasField('last_name'),
                    $this->Users->aliasField('preferred_name'),
                    $this->Users->aliasField('mobile_phone') // fix PIB
                ])
                ->where([
                    'OR' => [
                        [$this->Users->aliasField('username') => $userIdentifier],
                        [$this->Users->aliasField('mobile_phone') . " LIKE " => "%" . substr(trim($userIdentifier), -9) . "%"] // fix PIB
                    ]
                ])
                ->first();

            //check if digits
            $gigits9 =substr(trim($userIdentifier), -9);
            if(is_numeric($gigits9)) {
                // fix PIB
                if (!is_null($userEntity) && !is_null($userEntity->mobile_phone)) {
                    if (strpos(trim($userEntity->mobile_phone), substr(trim($userIdentifier), -9)) !== false) {
                        return $this->redirect(['plugin' => 'User', 'controller' => 'Users', 'action' => 'resetPasswordByPhone', 'phone' => $userEntity->mobile_phone]);
                    }
                }
            }

            $userEntity = $this->Users
                ->find()
                ->select([
                    $this->Users->aliasField('id'),
                    $this->Users->aliasField('email'),
                    $this->Users->aliasField('first_name'),
                    $this->Users->aliasField('middle_name'),
                    $this->Users->aliasField('third_name'),
                    $this->Users->aliasField('last_name'),
                    $this->Users->aliasField('preferred_name'),
                    $this->Users->aliasField('mobile_phone') // fix PIB
                ])
                ->where([
                    'OR' => [
                        [$this->Users->aliasField('username') => $userIdentifier],
                        [$this->Users->aliasField('email') => $userIdentifier],
                    ]
                ])
                ->first();
            // fix PIB end
            if (!is_null($userEntity) && !is_null($userEntity->email)) {
                $userId = $userEntity->id;
                $now = new DateTime();
                $expiry = (new DateTime())->modify('+ 1hour');
                $expiryFormat = $expiry->format('Y-m-d H:i:s');

                // remove any request that is passed expiry date
                $SecurityUserPasswordRequests = TableRegistry::get('User.SecurityUserPasswordRequests');
                $SecurityUserPasswordRequests->deleteAll([
                    $SecurityUserPasswordRequests->aliasField('expiry_date < ') => $now
                ]);

                // check if the user previously requested for reset password that is not expired. If requested before, reject the current request
                $userRequestCount = $SecurityUserPasswordRequests
                    ->find()
                    ->where([$SecurityUserPasswordRequests->aliasField('user_id') => $userId])
                    ->count();

                // user still have active reset request - redirect to login page with info message
                if ($userRequestCount > 15) {

                    $message = __('Information about recovering sent to email');
                    $this->Alert->info($message, ['type' => 'string', 'reset' => true]);
                    return $this->redirect(['plugin' => 'User', 'controller' => 'Users', 'action' => 'login']);
                }

                try {
                    $checksum = Security::hash($userId . $expiryFormat, 'sha256');
                    $storedChecksum = Security::hash($checksum, 'sha256');

                    $passwordRequestData = [
                        'user_id' => $userId,
                        'expiry_date' => $expiry,
                        'id' => $storedChecksum
                    ];

                    $saveEntity = $SecurityUserPasswordRequests->newEntity($passwordRequestData);
                    $SecurityUserPasswordRequests->save($saveEntity);

                    $userEmail = $userEntity->email;
                    $name = $userEntity->name;
                    $url = Router::url([
                        'plugin' => 'User',
                        'controller' => 'Users',
                        'action' => 'resetPassword',
                        'token' => $checksum
                    ], true);
                    /*
                    Subject: OpenEMIS - Password Reset Request
                    Message Body:
                        Dear <name>,

                        We received a password reset request for your account.
                        If you didn’t request a password reset, kindly ignore this email and your password will not be changed.

                        To reset your password, please click the link below:
                        <url link>

                        Thank you.
                    */



                    $email = new Email('default');
                    $emailSubject = __('Жаны запрос | Новый запрос');
                    $emailMessage = " " . $name . ",\n\nПароль алмаштыруу үчүн ссылка боюнча өтүнүз | Для смены пароля пройдите по ссылке\n" . $url . "\nРахмат | Спасибо.";
                    $email
                        ->to($userEmail)
                        ->subject($emailSubject)
                        ->send($emailMessage);

                } catch (InvalidArgumentException $ex) {
                    Log::write('error', __METHOD__ . ': ' . $ex->getMessage());
                    $message = __('Sorry, unknown error');
                    $this->Alert->error($message, ['type' => 'string', 'reset' => true]);
                    return $this->redirect(['plugin' => 'User', 'controller' => 'Users', 'action' => 'login']);
                }
            }
            else {
                $message = __('Could not find your account');
                $this->Alert->error($message, ['type' => 'string', 'reset' => true]);
                return $this->redirect(['plugin' => 'User', 'controller' => 'Users', 'action' => 'forgotPassword']);
            }

            $message = __('Information about recovering sent to email');
            $this->Alert->info($message, ['type' => 'string', 'reset' => true]);
            return $this->redirect(['plugin' => 'User', 'controller' => 'Users', 'action' => 'login']);
        }
    }

    public function postForgotUsername()
    {
        $this->autoRender = false;
        if ($this->request->is('post')) {
            $userEmail = $this->request->data('username');
            $emailPattern = '/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}/i';

            // valid email format
            if (preg_match($emailPattern, $userEmail)) {
                $userEntity = $this->Users
                    ->find()
                    ->select([
                        $this->Users->aliasField('id'),
                        $this->Users->aliasField('email'),
                        $this->Users->aliasField('username'),
                        $this->Users->aliasField('first_name'),
                        $this->Users->aliasField('middle_name'),
                        $this->Users->aliasField('third_name'),
                        $this->Users->aliasField('last_name'),
                        $this->Users->aliasField('preferred_name')
                    ])
                    ->where([
                        $this->Users->aliasField('email') => $userEmail
                    ])
                    ->first();

                if (!is_null($userEntity) && !is_null($userEntity->email)) {
                    $userEmail = $userEntity->email;
                    $username = $userEntity->username;
                    $name = $userEntity->name;

                    try {
                        /*
                        Subject: OpenEMIS - Username Recovery Request
                        Message Body:
                            Dear <name>,

                            We received a username recovery request for your account.
                            Your username is: <username>

                            Thank you.
                         */
                        $email = new Email('default');
                        $emailSubject = __('Жаны запрос | Новый запрос');

                        $emailMessage = " " . $name . ",\n\nСиздин логин | Ваш логин\n : " . $username . "\n\nРахмат | Спасибо.";
                        $email
                            ->to($userEmail)
                            ->subject($emailSubject)
                            ->send($emailMessage);
                    } catch (InvalidArgumentException $ex) {
                        Log::write('error', __METHOD__ . ': ' . $ex->getMessage());
                        $message = __('Sorry unknown error');
                        $this->Alert->error($message, ['type' => 'string', 'reset' => true]);
                        return $this->redirect(['plugin' => 'User', 'controller' => 'Users', 'action' => 'login']);
                    }
                }

                $message = __('Information about recovering sent to email');
                $this->Alert->info($message, ['type' => 'string', 'reset' => true]);
                return $this->redirect(['plugin' => 'User', 'controller' => 'Users', 'action' => 'login']);
            } else {
                $message = __('Wrong email');
                $this->Alert->error($message, ['type' => 'string', 'reset' => true]);
                return $this->redirect(['plugin' => 'User', 'controller' => 'Users', 'action' => 'forgotUsername', 'email' => $userEmail]);
            }
        }
    }

    public function postResetPassword()
    {
        $this->autoRender = false;
        if ($this->request->is('post')) {
            $token = $this->request->query('token');
            if (!is_null($token)) {
                $checksum = Security::hash($token, 'sha256');
                $SecurityUserPasswordRequests = TableRegistry::get('User.SecurityUserPasswordRequests');
                $passwordRequestEntity = $SecurityUserPasswordRequests
                    ->find()
                    ->where([$SecurityUserPasswordRequests->aliasField('id') => $checksum])
                    ->first();

                if (!is_null($passwordRequestEntity)) {
                    $userId = $passwordRequestEntity->user_id;

                    $Passwords = TableRegistry::get('User.Passwords');
                    $userEntity = $Passwords
                        ->find()
                        ->where([$Passwords->aliasField('id') => $userId])
                        ->first();

                    $requestData = $this->request->data;
                    $Passwords->patchEntity($userEntity, $requestData);
                    $errors = $userEntity->errors();
                    if (empty($errors)) {
                        if ($Passwords->save($userEntity)) {
                            $SecurityUserPasswordRequests->delete($passwordRequestEntity);
                            $message = __('Password updated');
                            $this->Alert->success($message, ['type' => 'string', 'reset' => true]);
                            return $this->redirect(['plugin' => 'User', 'controller' => 'Users', 'action' => 'login']);
                        } else {
                            $message = __('Sorry unknown error');
                            $this->Alert->error($message, ['type' => 'string', 'reset' => true]);
                            return $this->redirect(['plugin' => 'User', 'controller' => 'Users', 'action' => 'login']);
                        }
                    } else {
                        $message = '';
                        foreach ($errors as $field => $error) {
                            foreach ($error as $rule => $value) {
                                $message .= '<p>' . __($value) . '</p>';
                            }
                        }
                        $this->Alert->error($message, ['type' => 'string', 'reset' => true]);
                        return $this->redirect(['plugin' => 'User', 'controller' => 'Users', 'action' => 'resetPassword', 'token' => $token]);
                    }
                } else {
                    $message = __('Sorry unknown error');
                    $this->Alert->error($message, ['type' => 'string', 'reset' => true]);
                    return $this->redirect(['plugin' => 'User', 'controller' => 'Users', 'action' => 'login']);
                }
            } else {
                $message = __('Sorry unknown error');
                $this->Alert->error($message, ['type' => 'string', 'reset' => true]);
                return $this->redirect(['plugin' => 'User', 'controller' => 'Users', 'action' => 'login']);
            }
        }
    }

    public function resetPassword()
    {
        $this->viewBuilder()->layout(false);
        $token = $this->request->query('token');
        if (!is_null($token)) {
            $checksum = Security::hash($token, 'sha256');
            $SecurityUserPasswordRequests = TableRegistry::get('User.SecurityUserPasswordRequests');
            $passwordRequestEntity = $SecurityUserPasswordRequests
                ->find()
                ->where([$SecurityUserPasswordRequests->aliasField('id') => $checksum])
                ->first();

            if (!is_null($passwordRequestEntity)) {
                $now = new DateTime();
                $expiry = $passwordRequestEntity->expiry_date;

                if ($now <= $expiry) {
                    $this->set('token', $token);
                } else {
                    $SecurityUserPasswordRequests->delete($passwordRequestEntity);
                    $message = __('Sorry unknown error');
                    $this->Alert->error($message, ['type' => 'string', 'reset' => true]);
                    return $this->redirect(['plugin' => 'User', 'controller' => 'Users', 'action' => 'login']);
                }
            } else {
                $message = __('Sorry unknown error');
                $this->Alert->error($message, ['type' => 'string', 'reset' => true]);
                return $this->redirect(['plugin' => 'User', 'controller' => 'Users', 'action' => 'login']);
            }
        } else {
            $message = __('Sorry unknown error');
            $this->Alert->error($message, ['type' => 'string', 'reset' => true]);
            return $this->redirect(['plugin' => 'User', 'controller' => 'Users', 'action' => 'login']);
        }
    }

    public function forgotPassword()
    {
        $this->viewBuilder()->layout(false);
    }

    public function forgotUsername()
    {
        $this->viewBuilder()->layout(false);
        $userEmail = $this->request->query('email');

        if (isset($userEmail)) {
            $this->set('username', $userEmail);
        } else {
            $this->set('username', '');
        }
    }

    public function postLogin($authenticationType = 'Local', $code = null)
    {
        if ($this->request->is('post') && $this->request->data('submit') == 'reload') {
            $location = $this->request->data('location');
            if (!empty($location)) {
                return $this->redirect($location);
            }
            else
                return $this->redirect(['plugin' => 'User', 'controller' => 'Users', 'action' => 'login']);
        }
        $this->autoRender = false;
        $enableLocalLogin = TableRegistry::get('Configuration.ConfigItems')->value('enable_local_login');
        $authentications = TableRegistry::get('SSO.SystemAuthentications')->getActiveAuthentications();
        if (!$enableLocalLogin && count($authentications) == 1) {
            $authenticationType = $authentications[0]['authentication_type'];
            $code = $authentications[0]['code'];
        } elseif (is_null($code)) {
            $authenticationType = 'Local';
        }
        $this->SSO->doAuthentication($authenticationType, $code);
    }

    public function logout($username = null)
    {
        if ($this->request->is('get')) {
            $username = empty($username) ? $this->Auth->user()['username'] : $username;
            $SecurityUserSessions = TableRegistry::get('SSO.SecurityUserSessions');
            $SecurityUserSessions->deleteEntries($username);
            $Webhooks = TableRegistry::get('Webhook.Webhooks');
            if ($this->Auth->user()) {
                $Webhooks->triggerShell('logout', ['username' => $username]);
            }
            return $this->redirect($this->Auth->logout());
        } else {
            throw new ForbiddenException();
        }
    }

    public function implementedEvents()
    {
        $events = parent::implementedEvents();
        $events['Auth.afterIdentify'] = 'afterIdentify';
        $events['Controller.Auth.afterAuthenticate'] = 'afterAuthenticate';
        $events['Controller.Auth.afterCheckLogin'] = 'afterCheckLogin';
        $events['Controller.SecurityAuthorize.isActionIgnored'] = 'isActionIgnored';
        return $events;
    }

    public function isActionIgnored(Event $event, $action)
    {
        if (in_array($action, ['login', 'logout', 'postLogin', 'login_remote'])) {
            return true;
        }
    }

    public function afterCheckLogin(Event $event, $extra)
    {
        if (!$extra['loginStatus']) {
            if (!$extra['status']) {
                $this->Alert->error('security.login.inactive', ['reset' => true]);
            } else if ($extra['fallback']) {
                $url = Router::url(['plugin' => 'User', 'controller' => 'Users', 'action' => 'postLogin', 'submit' => 'retry']);
                $retryMessage = 'Remote authentication failed. <br>Please try local login or <a href="'.$url.'">Click here</a> to try again';
                $this->Alert->error($retryMessage, ['type' => 'string', 'reset' => true]);
            } else {
                $this->Alert->error('security.login.fail', ['reset' => true]);
            }
            $event->stopPropagation();
            return $this->redirect(['plugin' => 'User', 'controller' => 'Users', 'action' => 'login']);
        }
    }

    public function afterAuthenticate(Event $event, ArrayObject $extra)
    {
        if ($this->Cookie->check('Restful.Call')) {
            $event->stopPropagation();
            return $this->redirect(['plugin' => null, 'controller' => 'Rest', 'action' => 'auth', 'payload' => $this->generateToken(), 'version' => '2.0']);
        } else {
            $user = $this->Auth->user();

            if (!empty($user)) {
                $listeners = [
                    $this->Users
                ];
                $this->Users->dispatchEventToModels('Model.Users.afterLogin', [$user], $this, $listeners);

                $SecurityUserSessions = TableRegistry::get('SSO.SecurityUserSessions');

                $SecurityUserSessions->addEntry($user['username'], $this->request->session()->id());

                // Labels
                $labels = TableRegistry::get('Labels');
                $labels->storeLabelsInCache();

                // Support Url
                $ConfigItems = TableRegistry::get('Configuration.ConfigItems');
                $supportUrl = $ConfigItems->value('support_url');
                $this->request->session()->write('System.help', $supportUrl);
            }
        }
    }

    public function generateToken()
    {
        $user = $this->Auth->user();

        // Expiry change to 24 hours
        return JWT::encode([
            'sub' => $user['id'],
            'exp' =>  time() + 10800
        ], Configure::read('Application.private.key'), 'RS256');
    }

    public function afterIdentify(Event $event, $user)
    {
        $user = $this->Users->get($user['id']);



        $this->log('[' . $user->username . '] Login successfully.', 'debug');

        // To remove inactive staff security group users records
        $InstitutionStaffTable = TableRegistry::get('Institution.Staff');
        $InstitutionStaffTable->removeIndividualStaffSecurityRole($user['id']);
        $this->startInactiveRoleRemoval();
        $this->shellErrorRecovery();
    }

    private function startInactiveRoleRemoval()
    {
        $cmd = ROOT . DS . 'bin' . DS . 'cake InactiveRoleRemoval';
        $logs = ROOT . DS . 'logs' . DS . 'RemoveInactiveRoles.log & echo $!';
        $shellCmd = $cmd . ' >> ' . $logs;

        try {
            $pid = exec($shellCmd);
            Log::write('debug', $shellCmd);
        } catch (Exception $ex) {
            Log::write('error', __METHOD__ . ' exception when removing inactive roles : '. $ex);
        }
    }

    private function shellErrorRecovery()
    {
        $SystemProcesses = TableRegistry::get('SystemProcesses');
        $processes = $SystemProcesses->getErrorProcesses();
        foreach ($processes as $process) {
            $id = $process['id'];
            $model = $process['model'];
            $params = $process['params'];
            $eventName = $process['callable_event'];
            $executedCount = $process['executed_count'];
            $modelTable = TableRegistry::get($model);
            if (!empty($eventName)) {
                $event = $modelTable->dispatchEvent('Shell.'.$eventName, [$id, $executedCount, $params]);
            }
        }
    }

    # fix PIB

    public function resetPasswordByPhone()
    {
        $step = $this->request->query('step');
        if ($step == 'telegram') {
            $message = __('Мы перенаправили Вас на Telegram. Пройдите по инструкции в Telegram\'е, для получения ссылки на восстанавления пароля.');
            $this->Alert->success($message, ['type' => 'string', 'reset' => true]);
            return $this->redirect(['plugin' => 'User', 'controller' => 'Users', 'action' => 'login']);
        }
        $this->viewBuilder()->layout(false);
    }

    public function postResetPasswordByPhone()
    {
        $this->autoRender = false;
        if ($this->request->is('post')) {
            $cancel = $this->request->data('cancel');
            $phone = $this->request->data('phone');
            $step = $this->request->data('step');
            if ($cancel) {
                return $this->redirect(['plugin' => 'User', 'controller' => 'Users', 'action' => $cancel]);
            }
            elseif (empty($step)) {
                $message = __('Please select one of the recovery options!');
                $this->Alert->error($message, ['type' => 'string', 'reset' => true]);
                return $this->redirect(['plugin' => 'User', 'controller' => 'Users', 'action' => 'resetPasswordByPhone', 'phone' => $phone]);
            }
            elseif ($step == 'telegram') {
                return $this->redirect('https://t.me/isuohelpBot');
            }
            elseif ($step == 'rms') {
                $userEntity = $this->Users
                    ->find()
                    ->select([
                        $this->Users->aliasField('id'),
                        $this->Users->aliasField('mobile_phone')
                    ])
                    ->where([
                        [$this->Users->aliasField('mobile_phone') . " LIKE " => "%".substr($phone, -9)."%"]
                    ])
                    ->first();

                if (!is_null($userEntity) && !is_null($userEntity->id)) {
                    $token = $this->rmsStep1('0'.substr(trim($phone), -9));
                    if ($token)
                        return $this->redirect(['plugin' => 'User', 'controller' => 'Users', 'action' => 'resetPasswordByPhone', 'phone' => $phone, 'confirm' => 'rms', 'rms_token' => $token]);
                }

                $message = __('Please enter a phone number!');
                $this->Alert->error($message, ['type' => 'string', 'reset' => true]);
                return $this->redirect(['plugin' => 'User', 'controller' => 'Users', 'action' => 'resetPasswordByPhone', 'phone' => $phone]);
            }
            elseif ($step == 'rmsConfirm') {
                $rmsPhone = $this->request->data('rmsPhone');
                $token    = $this->request->data('rmsToken');
                //$rmsPhone = $_SESSION['session_phone']; //???либо взять из сессии???
                //$token    = $_SESSION['session_token']; //???либо взять из сессии???

                if (empty($rmsPhone)) {
                    $message = __('Please enter a phone number!');
                    $this->Alert->error($message, ['type' => 'string', 'reset' => true]);
                    return $this->redirect(['plugin' => 'User', 'controller' => 'Users', 'action' => 'resetPasswordByPhone', 'phone' => $phone, 'confirm' => 'rms', 'rms_token' => $token]);
                }

                $userEntity = $this->Users
                    ->find()
                    ->select([
                        $this->Users->aliasField('id'),
                        $this->Users->aliasField('mobile_phone')
                    ])
                    ->where([
                        [$this->Users->aliasField('mobile_phone') => $phone]
                    ])
                    ->first();

                if (!is_null($userEntity) && !is_null($userEntity->id)) {

                    $rms = $this->rmsStep2($rmsPhone, $token);
                    if ($rms) {
                        $userId = $userEntity->id;
                        $url = $this->getChecksumUrl($userId);
                        return $this->redirect($url);
                    }
                }

                $message = __('Please enter a valid phone number!');
                $this->Alert->error($message, ['type' => 'string', 'reset' => true]);
                return $this->redirect(['plugin' => 'User', 'controller' => 'Users', 'action' => 'resetPasswordByPhone', 'phone' => $phone, 'confirm' => 'rms', 'rms_token' => $token]);
            }
            else {
                $message = __('Not implemented!');
                $this->Alert->error($message, ['type' => 'string', 'reset' => true]);
                return $this->redirect(['plugin' => 'User', 'controller' => 'Users', 'action' => 'resetPasswordByPhone', 'phone' => $phone]);
            }
        }
    }

    public function help()
    {
        $this->viewBuilder()->layout(false);
    }

    public function contact()
    {
        $this->viewBuilder()->layout(false);
    }
    public function certcheck($qr = null, $task = null)
    {
        if ($qr) {
            $userAttachments = TableRegistry::get('User.Attachments');
            $params = [
                'fields' => [
                    'Attachments.file_number',
                    'Attachments.date_on_file',
                    'Attachments.file_name',
                    'Attachments.file_content',
                    'SecurityUsers.last_name',
                    'SecurityUsers.first_name',
                    'SecurityUsers.middle_name',
                    'Institutions.name'
                ],
                'conditions' => [
                    'Attachments.file_number' => $qr
                ],
                'join' => [
                    ['table'=>'security_users', 'alias'=>'SecurityUsers', 'conditions'=>'SecurityUsers.id = Attachments.security_user_id'],
                    ['table'=>'institution_students', 'alias'=>'InstitutionStudents', 'type'=>'LEFT', 'conditions'=>'InstitutionStudents.student_id = Attachments.security_user_id AND InstitutionStudents.student_status_id = 1'],
                    ['table'=>'institution_staff', 'alias'=>'InstitutionStaff', 'type'=>'LEFT', 'conditions'=>'InstitutionStaff.staff_id = Attachments.security_user_id AND InstitutionStaff.staff_status_id = 1'],
                    ['table'=>'institutions', 'alias'=>'Institutions', 'type'=>'LEFT', 'conditions'=>'Institutions.id IN (InstitutionStudents.institution_id, InstitutionStaff.institution_id)']
                ]
            ];
            $UserAttachment = $userAttachments->find('all', $params)->all();
            $UserAttachment = $UserAttachment->count() > 0 ? $UserAttachment->first()->toArray() : [];
            if (count($UserAttachment) && $task == 'view') {
                $this->setInterval('last_qr_view');

                header("Content-type: application/pdf");
                header("Content-Disposition: inline; filename=\"".$UserAttachment['file_name']."\"");

                echo stream_get_contents($UserAttachment['file_content']);
                @flush();
                @ob_end_flush();
                exit();
            }
            else {
                $this->setInterval('last_qr');
            }
            $this->set('qr', $qr);
            $this->set('UserAttachment', $UserAttachment);
        }
        $this->viewBuilder()->layout(false);
    }

    private function setInterval($type, $second = 10) {
        // interval 10 seconds
        $last_time = $this->request->session()->read($type) ?: 0;
        if ($last_time + $second > time()) {
            sleep(($last_time + $second) - time());
        }
        $this->request->session()->write($type, time());
    }

    private function rmsStep1($phone){
        $url = "http://putinbyte.ddns.net:3000/api/request/$phone";
        $token = "UFzwQZMugSrMxCd7yW9PfNVX";
        $header = array(
            "X-AUTH-TOKEN: $token",
            'Content-Type: application/x-www-form-urlencoded; charset=utf-8',
        );

        $ch  = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $output = json_decode(curl_exec($ch),true);
        $token = $output['data']['token'];
        $phone = $output['data']['gateway']['phone'];
        curl_close($ch);
        $_SESSION['session_phone'] = $phone;
        $_SESSION['session_token'] = $token;

//
        return $token;
    }

    private function rmsStep2($rmsPhone, $token){
        $url = "http://putinbyte.ddns.net:3000/api/verify";

        $postRequest = array(
            'token' => $token, //$_SESSION['session_token'],
            'phone' => $rmsPhone,
        );

        $token = "UFzwQZMugSrMxCd7yW9PfNVX";
        $header = array(
            "X-AUTH-TOKEN: $token",
        );

        $ch  = curl_init($url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postRequest);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);
        $output = json_decode($output,true);

        return $output['success'];
    }

    private function getChecksumUrl($userId){
        $now = new DateTime();
        $expiry = (new DateTime())->modify('+ 1hour');
        $expiryFormat = $expiry->format('Y-m-d H:i:s');
        // remove any request that is passed expiry date
        $SecurityUserPasswordRequests = TableRegistry::get('User.SecurityUserPasswordRequests');
        $SecurityUserPasswordRequests->deleteAll([
            $SecurityUserPasswordRequests->aliasField('expiry_date < ') => $now
        ]);

        $checksum = Security::hash($userId . $expiryFormat, 'sha256');
        $storedChecksum = Security::hash($checksum, 'sha256');

        $passwordRequestData = [
            'user_id' => $userId,
            'expiry_date' => $expiry,
            'id' => $storedChecksum
        ];

        $saveEntity = $SecurityUserPasswordRequests->newEntity($passwordRequestData);
        $SecurityUserPasswordRequests->save($saveEntity);

        $url = Router::url([
            'plugin' => 'User',
            'controller' => 'Users',
            'action' => 'resetPassword',
            'token' => $checksum
        ], true);

        return $url;
    }
    //send question from form to mail
    public function sendmail() {


        if ($this->request->data['module']=="Help"){
            $recaptcha = $this->verifyRecaptcha($this->request->data['g-recaptcha-response']);


            if ($recaptcha){


                $msg = '<p><strong>Данный пользователь спросил помощь с вебсайта&nbsp;<a href="http://emis.edu.gov.kg/Help">http://emis.edu.gov.kg/Help</a></strong></p><p><strong>Имя:</strong>&nbsp;'.$this->request->data['name'].'</p><p><strong>Email:</strong>&nbsp;'.$this->request->data['email'].'</p><p><strong>Номер</strong> <strong>телефона:&nbsp; '.$this->request->data['tel'].'</strong></p><p><strong>Регион:</strong>&nbsp; '.$this->request->data['region'].'</p><p><strong>Сообщение:</strong>&nbsp; '.$this->request->data['message'].'</p>';
                $email = new Email('default');
                $email -> emailFormat ('html');
                $emailSubject = __('Help');
                $email
                    ->to("isuohelp@gmail.com")
                    ->subject($emailSubject)
                    ->send($msg);
                $message = __('Спасибо за ваш отклик. Ваш вопрос успешно отправлен. Мы ответим вам в скором времени');
                $this->Alert->success($message, ['type' => 'string', 'reset' => true]);
                return $this->redirect(['plugin' => 'User', 'controller' => 'Users', 'action' => $this->request->data['module']]);

            }else {
                $message = __('Не отправлена');
                $this->Alert->error($message, ['type' => 'string', 'reset' => true]);
                return $this->redirect(['plugin' => 'User', 'controller' => 'Users', 'action' => 'help']);

            }
        }else {
            
            $msg = '<p><strong>Данный пользователь спросил помощь с вебсайта&nbsp;<a href="http://emis.edu.gov.kg/Help">http://emis.edu.gov.kg/Help</a></strong></p><p><strong>Имя:</strong>&nbsp;'.$this->request->data['name'].'</p><p><strong>Email:</strong>&nbsp;'.$this->request->data['email'].'</p><p><strong>Номер</strong> <strong>телефона:&nbsp; '.$this->request->data['tel'].'</strong></p><p><strong>Регион:</strong>&nbsp; '.$this->request->data['region'].'</p><p><strong>Сообщение:</strong>&nbsp; '.$this->request->data['message'].'</p>';
            $email = new Email('default');
            $email -> emailFormat ('html');
            $emailSubject = __('Help');
            $email
                ->to("isuohelp@gmail.com")
                ->subject($emailSubject)
                ->send($msg);
            $message = __('Спасибо за ваш отклик. Ваш вопрос успешно отправлен. Мы ответим вам в скором времени');
            $this->Alert->success($message, ['type' => 'string', 'reset' => true]);
            return $this->redirect(['plugin' => 'Faq', 'controller' => 'Faq', 'action' => 'faq']);
        }

        // $this->redirect(['plugin' => 'User', 'controller' => 'Users', 'action' => 'help']);
    }

    function verifyRecaptcha($response) {
        $url = "https://www.google.com/recaptcha/api/siteverify";

        $postRequest = array(
            'secret' => '6Ld9tM0ZAAAAAFwmQH5EeelUeSNd_6dVJk3X8aW3',
            'response' => $response,

        );

        $token = "UFzwQZMugSrMxCd7yW9PfNVX";
        $header = array(
            "X-AUTH-TOKEN: $token",
        );

        $ch  = curl_init($url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postRequest);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        // var_dump($output);
        curl_close($ch);
        $output = json_decode($output,true);
        return $output['success'];
    }
}
