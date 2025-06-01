<?php
require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/../config/database.php';

use Helpers\Session;
use Controllers\AuthController;
use Controllers\VariantController;
use Controllers\ResourceController;
use Controllers\EvaluationController;
use Controllers\SubscriptionController;



Session::start();
$path = $_GET['path']??''; // Obtiene el endpoint de la URL solicitado
$authController = new AuthController($connection);
$variantController = new VariantController($connection);
//$resController=new ResourceController($connection);
//$evalController=new EvaluationController($connection);
$subController=new SubscriptionController($connection);

(
    match($path){
      'api/register' => fn()=>$authController->register(),
      'api/login' => fn()=>$authController->login(),
      'api/logout' => fn()=>$authController->logout(),
      'api/variants/getAll' => fn()=>$variantController->getAll(),
      'api/variants/getBy' => fn()=>$variantController->getBy(),
      'api/subscriptions' => fn()=>$subController->getAll(),
      'api/createsubscription' => fn()=>$subController->create(),
      'api/deletesubscription' => fn()=>$subController->delete(),
      'api/getsubscriptionbyid' => fn()=>$subController->getById(),
      //'api/resources/list'=>fn()=>$resCtrl->list(),
      //'api/resources/upload'=>fn()=>$resCtrl->upload(),
      //'api/eval/create'=>fn()=>$evalCtrl->create(),
      //'api/eval/get'   =>fn()=>$evalCtrl->get(),
      //'api/eval/submit'=>fn()=>$evalCtrl->submit(),
      default=>function(){
        http_response_code(404);
        echo 'Not found';
        }
    }
)();