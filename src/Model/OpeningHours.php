<?php

namespace Innoweb\SocialMeta\Model;

use Override;
use SilverStripe\Forms\CheckboxSetField;
use SilverStripe\Forms\TimeField;
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

    #[Override]
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName('BusinessLocationID');

        $fields->addFieldsToTab(
            'Root.Main',
            [
                CheckboxSetField::create(
                    'Days',
                    _t('OpeningHours.DaysOpen', 'Days Open'),
                    [
                        'Mo' => _t('OpeningHours.Monday', 'Monday'),
                        'Tu' => _t('OpeningHours.Tuesday', 'Tuesday'),
                        'We' => _t('OpeningHours.Wednesday', 'Wednesday'),
                        'Th' => _t('OpeningHours.Thursday', 'Thursday'),
                        'Fr' => _t('OpeningHours.Friday', 'Friday'),
                        'Sa' => _t('OpeningHours.Saturday', 'Saturday'),
                        'So' => _t('OpeningHours.Sunday', 'Sunday')
                    ]
                ),
                TimeField::create(
                    'TimeOpen',
                    _t('OpeningHours.OpeningTime', 'Opening Time')
                ),
                TimeField::create(
                    'TimeClose',
                    _t('OpeningHours.ClosingTime', 'Closing Time')
                )
            ]
        );

        return $fields;
    }
}
