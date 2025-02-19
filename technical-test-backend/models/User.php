<?php

namespace app\models;

use yii\db\ActiveRecord;

class User extends ActiveRecord
{
    public static function tableName()
    {
        return 'users';
    }

    public function rules()
    {
        return [
            [['name', 'birthday'], 'required'],
            ['name', 'string', 'max' => 255],
            ['name', 'match', 'pattern' => '/^[a-zA-Z\s\-\']+$/'],
            ['birthday', 'date', 'format' => 'php:Y-m-d'],
            ['birthday', 'validateBirthday']
        ];
    }

    public function validateBirthday($attribute, $params)
{
    $today = new \DateTime();
    $birthday = new \DateTime($this->$attribute);

    if ($birthday > $today) {
        $this->addError($attribute, 'Birthday cannot be in the future');
    }
}
}