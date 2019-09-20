<?php

namespace Innoweb\SocialMeta\Model;

use SilverStripe\Forms\CheckboxSetField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\ORM\DataObject;
use SilverStripe\Versioned\Versioned;
use SilverStripe\Forms\TimeField;

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
        'TimeOpen'  =>  'Time',
        'TimeClose' =>  'Time',
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
                TimeField::create(
                    'TimeOpen',
                    'Opening Time'
                ),
                TimeField::create(
                    'TimeClose',
                    'Closing Time'
                )
            ]
        );

        return $fields;
    }
}
