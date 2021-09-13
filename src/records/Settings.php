<?php

namespace nilsenpaul\cookieconsent\records;

use Craft;

use craft\db\ActiveRecord;

class Settings extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%completecookieconsent_settings}}';
    }
}
