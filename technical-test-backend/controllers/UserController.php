<?php

namespace app\controllers;

use yii\web\Controller;
use yii\web\Response;
use app\models\User;
use yii\filters\Cors;
use DOMDocument;
use SimpleXMLElement;
use Yii;

class UserController extends Controller
{
    public function behaviors()
    {
        return [
            'corsFilter' => [
                'class' => Cors::class,
                'cors' => [
                    'Origin' => ['http://localhost:3000'], 
                    'Access-Control-Request-Method' => ['GET', 'POST'],
                    'Access-Control-Request-Headers' => ['*'],
                    'Access-Control-Allow-Credentials' => true,
                ],
            ],
        ];
    }

    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionSignup()
{
    Yii::$app->response->format = Response::FORMAT_JSON;
    
    $data = Yii::$app->request->getRawBody();
    $data = json_decode($data, true);
    
    if (!$data) {
        return [
            'success' => false,
            'message' => 'Invalid JSON data'
        ];
    }

    $user = new User();
    $user->name = $data['name'];
    $user->birthday = $data['birthday'];

    if ($user->save()) {
        // Save to XML file
        $this->saveToXml($user);

        return [
            'success' => true,
            'message' => 'User saved successfully',
            'data' => $user->attributes
        ];
    }

    return [
        'success' => false,
        'errors' => $user->errors
    ];
}

private function saveToXml($user)
{
    $xmlFile = Yii::getAlias('@app/runtime/users.xml');
    
    // If file doesn't exist, create initial XML structure
    if (!file_exists($xmlFile)) {
        $xml = new SimpleXMLElement('<community></community>');
    } else {
        $xml = simplexml_load_file($xmlFile);
    }

    // Add new member
    $member = $xml->addChild('member');
    $member->addChild('name', $user->name);
    $member->addChild('birthday', $user->birthday);

    // Save XML file
    $xml->asXML($xmlFile);
}

    public function actionUsers()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $users = User::find()
            ->select(['name', 'birthday', 'created_at'])
            ->orderBy(['created_at' => SORT_DESC])
            ->asArray()
            ->all();

        return $users;
    }
}