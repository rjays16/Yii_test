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
            ['birthday', 'date', 'format' => 'yyyy-MM-dd']
        ];
    }
}