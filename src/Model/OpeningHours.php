<?php

namespace Innoweb\SocialMeta\Model;

use SilverStripe\Forms\CheckboxSetField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\ORM\DataObject;
use SilverStripe\Versioned\Versioned;

class OpeningHours extends DataObject
{
    private static $singular_name = 'Opening Hours';
    private static $plural_name = 'Opening Hours';

    private static $table_name = 'SocialMetaOpeningHours';

    private static $extensions = [
        Versioned::class
    ];

    private static $db = [
        'Days'      =>  'Varchar(255)',
        'TimeOpen'  =>  "Enum(',00:00,01:00,02:00,03:00,04:00,05:00,06:00,07:00,08:00,09:00,10:00,11:00,12:00,13:00,14:00,15:00,16:00,17:00,18:00,19:00,20:00,21:00,22:00,23:00,24:00', '')",
        'TimeClose' =>  "Enum(',00:00,01:00,02:00,03:00,04:00,05:00,06:00,07:00,08:00,09:00,10:00,11:00,12:00,13:00,14:00,15:00,16:00,17:00,18:00,19:00,20:00,21:00,22:00,23:00,24:00', '')",
    ];

    private static $has_one = [
        'BusinessLocation'      =>  BusinessLocation::class,
        'SocialMetaConfigOf'    =>  DataObject::class
    ];

    private static $searchable_fields = [];

    private static $summary_fields = [
        'Days'  =>  'Days',
        'Hours' =>  'Hours'
    ];

    private static $casting = [
        'Hours' =>  'Varchar(255)'
    ];

    public function getParentConfig()
    {
        $class = $this->SocialMetaConfigOfClass;
        return $class::get()->byID($this->SocialMetaConfigOfID);
    }

    public function getHours()
    {
        if ($this->TimeOpen && $this->TimeClose) {
            return $this->TimeOpen . '-' . $this->TimeClose;
        }
        return null;
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName('BusinessLocationID');

        $fields->addFieldsToTab(
            'Root.Main',
            [
                CheckboxSetField::create(
                    'Days',
                    'Days Open',
                    [
                        'Mo' => 'Monday',
                        'Tu' => 'Tuesday',
                        'We' => 'Wednesday',
                        'Th' => 'Thursday',
                        'Fr' => 'Friday',
                        'Sa' => 'Saturday',
                        'So' => 'Sunday'
                    ]
                ),
                DropdownField::create(
                    'TimeOpen',
                    'Opening Time',
                    singleton(self::class)->dbObject('TimeOpen')->enumValues()
                ),
                DropdownField::create(
                    'TimeClose',
                    'Closing Time',
                    singleton(self::class)->dbObject('TimeClose')->enumValues()
                )
            ]
        );

        return $fields;
    }
}
