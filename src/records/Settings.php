<?php

namespace nilsenpaul\cookieconsent\records;

use Craft;

use yii\db\ActiveRecord;

class Settings extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%completecookieconsent_settings}}';
    }
}
