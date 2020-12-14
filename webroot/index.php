<?php
/**
 * The Front Controller for handling every request
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         0.2.9
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
 //echo '<h1>На сервере технические работы до 13:30</h1>';die;
 $visitor = $_SERVER['REMOTE_ADDR'];
//echo $visitor ;
/*
if (preg_match("/46.251.193/",$visitor)) {
      header("Location: https://mirror.edu.gov.kg");
      //$this->redirect('http://217.29.19.73/core/');
    exit();
}  
if (preg_match("/146.120.212/",$visitor)) {
      header("Location: https://mirror.edu.gov.kg");
      //$this->redirect('http://217.29.19.73/core/');
    exit();
} 
if (preg_match("/185.117.148/",$visitor)) {
      header("Location: https://mirror.edu.gov.kg");
      //$this->redirect('http://217.29.19.73/core/');
    exit();
} 

if (preg_match("/194.152.36/",$visitor)) {
      header("Location: https://mirror.edu.gov.kg");
      //$this->redirect('http://217.29.19.73/core/');
    exit();
} 

if (preg_match("/195.216.237/",$visitor)) {
      header("Location: https://mirror.edu.gov.kg");
      //$this->redirect('http://217.29.19.73/core/');
    exit();
} 

  if (preg_match("/212.24/",$visitor)) { 
      header("Location: https://mirror.edu.gov.kg");
      //$this->redirect('http://217.29.19.73/core/');
    exit();
}

  if (preg_match("/213.145/",$visitor)) { 
      header("Location: https://mirror.edu.gov.kg");
      //$this->redirect('http://217.29.19.73/core/');
    exit();
}
  if (preg_match("/185.54.253/",$visitor)) { 
      header("Location: https://mirror.edu.gov.kg");
      //$this->redirect('http://217.29.19.73/core/');
    exit();
}

  if (preg_match("/109.71.224/",$visitor)) { 
      header("Location: https://mirror.edu.gov.kg");
      //$this->redirect('http://217.29.19.73/core/');
    exit();
}
*/
// for built-in server
if (php_sapi_name() === 'cli-server') {
    $_SERVER['PHP_SELF'] = '/' . basename(__FILE__);

    $url = parse_url(urldecode($_SERVER['REQUEST_URI']));
    $file = __DIR__ . $url['path'];
    if (strpos($url['path'], '..') === false && strpos($url['path'], '.') !== false && is_file($file)) {
        return false;
    }
}

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Application;
use Cake\Http\Server;
use Cake\ORM\TableRegistry;
use Cake\Log\Log;

// Bind your application to the server.
$server = new Server(new Application(dirname(__DIR__) . '/config'));

// Run the request/response through the application
// and emit the response.
try {
    $server->emit($server->run());
} catch (Exception $ex) {
    $ErrorTable = TableRegistry::get('System.SystemErrors');
    $ErrorTable->insertError($ex);
    Log::write('error', $ex);
    throw $ex;
}
