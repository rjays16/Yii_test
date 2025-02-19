<?php

namespace app\controllers;

use yii\web\Controller;
use yii\web\Response;
use app\models\User;
use yii\filters\Cors;
use DOMDocument;
use SimpleXMLElement;
use yii\helpers\Html;
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
    
        // Validate JSON data
    
        if (!$data) {
            return [
                'success' => false,
                'message' => 'Invalid JSON data'
            ];
        }

        // Sanitize and validate input
        $name = Html::encode(trim($data['name'] ?? ''));
        $birthday = strip_tags(trim($data['birthday'] ?? ''));

        // Comprehensive input validation
        if (empty($name)) {
            return [
                'success' => false,
                'message' => 'Name is required'
            ];
        }

        // Name validation (allows letters, spaces, and hyphens)
        if (!preg_match('/^[a-zA-Z\s\-\']+$/', $name)) {
            return [
                'success' => false,
                'message' => 'Invalid name format. Only letters, spaces, and hyphens allowed.'
            ];
        }

        // Validate birthday format (YYYY-MM-DD)
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthday)) {
            return [
                'success' => false,
                'message' => 'Invalid date format. Use YYYY-MM-DD'
            ];
        }

        // Additional birthday validation
        try {
            $birthdayDate = new \DateTime($birthday);
            $now = new \DateTime();

            // Check if birthday is in the future
            if ($birthdayDate > $now) {
                return [
                    'success' => false,
                    'message' => 'Birthday cannot be in the future'
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Invalid date'
            ];
        }

        // Create new user model
        $user = new User();
    
        // Set sanitized data
        $user->name = $name;
        $user->birthday = $birthday;

        // Attempt to save user
        try {
            if ($user->save()) {
                // Save to XML file
                $this->saveToXml($user);

                return [
                    'success' => true,
                    'message' => 'User saved successfully',
                    'data' => $user->attributes
                ];
            } else {
            
                // Collect validation errors
                $errors = $user->errors;
                $errorMessages = [];
                foreach ($errors as $attribute => $messages) {
                    $errorMessages[] = implode(', ', $messages);
                }

                return [
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $errorMessages
                ];
            }
        } catch (\Exception $e) {
            
            // Log the full error for server-side debugging
            Yii::error('User save error: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'An unexpected error occurred'
            ];
        }
    }

    private function saveToXml($user)
    {
        try {
        
            $xmlFile = Yii::getAlias('@app/runtime/users.xml');
        
            // If file doesn't exist, create initial XML structure
            if (!file_exists($xmlFile)) {
                $xml = new \SimpleXMLElement('<community></community>');
            } else {
                $xml = simplexml_load_file($xmlFile);
            }

            // Add new member
            $member = $xml->addChild('member');
            $member->addChild('name', htmlspecialchars($user->name));
            $member->addChild('birthday', $user->birthday);

            // Ensure directory exists
            $directory = dirname($xmlFile);
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            // Save XML file with proper permissions
            $xml->asXML($xmlFile);
        } catch (\Exception $e) {
            // Log XML save error
            Yii::error('XML save error: ' . $e->getMessage());
        }
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