<?php

namespace Innoweb\SocialMeta\Model;

use BetterBrief\GoogleMapField;
use Innoweb\SocialMeta\Extensions\ConfigExtension;
use Override;
use Sheadawson\DependentDropdown\Forms\DependentDropdownField;
use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\CheckboxSetField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldGroup;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\Forms\LiteralField;
use SilverStripe\ORM\DataObject;
use SilverStripe\Versioned\Versioned;
use UncleCheese\DisplayLogic\Forms\Wrapper;

class BusinessLocation extends DataObject
{
    private static $singular_name = 'Business Location';

    private static $plural_name = 'Business Locations';

    private static $table_name = 'SocialMetaBusinessLocation';

    private static $extensions = [
        Versioned::class
    ];

    private static $db = [
        'MicroDataType'                 =>  'Varchar(255)',
        'MicroDataTypeSpecific'         =>  'Varchar(255)',

        'MicroDataStreetAddress'        =>  'Varchar(255)',
        'MicroDataPOBoxNumber'          =>  'Varchar(255)',
        'MicroDataCity'                 =>  'Varchar(255)',
        'MicroDataPostCode'             =>  'Varchar(255)',
        'MicroDataRegion'               =>  'Varchar(255)',
        'MicroDataCountry'              =>  'Varchar(255)',
        'MicroDataPhone'                =>  'Phone',
        'MicroDataFax'                  =>  'Phone',
        'MicroDataEmail'                =>  'Varchar(255)',

        'MicroDataPaymentAccepted'      =>  'Varchar(255)',

        'IsMicroDataCoordinatesEnabled' =>  'Boolean',
        'MicroDataLocationLongitude'    =>  'Varchar',
        'MicroDataLocationLatitude'     =>  'Varchar'
    ];

    private static $has_one = [
        'SocialMetaConfigOf'    =>  DataObject::class
    ];

    private static $has_many = [
        'MicroDataOpeningHours' =>  OpeningHours::class
    ];

    private static $searchable_fields = [];

    private static $summary_fields = [
        'MicroDataTitle'    =>  'Name/Title',
        'AddressAsString'   =>  'Address'
    ];

    private static $casting = [
        'AddressAsString'   =>  'Varchar(255)'
    ];

    public function getParentConfig()
    {
        $class = $this->SocialMetaConfigOfClass;
        return ($class)
            ? $class::get()->byID($this->SocialMetaConfigOfID)
            : null;
    }

    public function getMicroDataSchemaType($baseTypeOnly = false)
    {
        return ($this->MicroDataTypeSpecific && !$baseTypeOnly)
            ? $this->MicroDataTypeSpecific
            : $this->MicroDataType;
    }

    public function getAddressAsString()
    {
        $address = [];
        if ($this->MicroDataStreetAddress) {
            $address[] = $this->MicroDataStreetAddress . ',';
        }

        if ($this->MicroDataPOBoxNumber) {
            $address[] = $this->MicroDataPOBoxNumber . ',';
        }

        if ($this->MicroDataRegion) {
            $address[] = $this->MicroDataRegion;
        }

        if ($this->MicroDataPostCode) {
            $address[] = $this->MicroDataPostCode;
        }

        if ($this->MicroDataCountry) {
            $address[] = ', ' . $this->MicroDataCountry;
        }

        $address = implode(' ', $address);
        return $address;
    }

    public function getMicroDataMapLink()
    {
        if ($this->MicroDataStreetAddress) {
            $address = [];
            $address[] = $this->MicroDataStreetAddress . ',';
            if ($this->MicroDataCity) {
                $address[] = $this->MicroDataCity;
            }

            if ($this->MicroDataRegion) {
                $address[] = $this->MicroDataRegion;
            }

            if ($this->MicroDataPostCode) {
                $address[] = $this->MicroDataPostCode;
            }

            $address = implode(' ', $address);
            return 'https://www.google.com.au/maps/place/' . urlencode($address);
        }

        return null;
    }

    #[Override]
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $separateEntity = $this->getParentConfig()
            ? $this->getParentConfig()->IsMicroDataAdditionalLocationsSeparateEntities
            : false;

        $fields->removeByName('MicroDataType');
        $fields->removeByName('MicroDataTypeSpecific');
        $fields->removeByName('MicroDataPaymentAccepted');
        $fields->removeByName('IsMicroDataCoordinatesEnabled');
        $fields->removeByName('MicroDataLocationLatitude');
        $fields->removeByName('MicroDataLocationLongitude');

        if (!$separateEntity) {
            $fields->removeByName('MicroDataPhone');
            $fields->removeByName('MicroDataFax');
            $fields->removeByName('MicroDataEmail');
        }

        if ($separateEntity) {
            $typeSpecificSource = function ($type) {
                if ($type === 'Organization') {
                    $key = 'organization_types';
                } elseif ($type === 'LocalBusiness') {
                    $key = 'localbusiness_types';
                } else {
                    return [];
                }

                return Config::inst()->get(ConfigExtension::class, $key);
            };

            $typeField = DropdownField::create(
                'MicroDataType',
                _t('BusinessLocation.Type', 'Type'),
                [
                    'Organization'  =>  _t('BusinessLocation.Organisation', 'Organisation'),
                    'LocalBusiness' =>  _t('BusinessLocation.LocalBusiness', 'Local Business')
                ]
            );

            $typeSpecificField = DependentDropdownField::create(
                'MicroDataTypeSpecific',
                _t('BusinessLocation.MoreSpecificType', 'More specific type'),
                $typeSpecificSource
            )
                ->setDepends($typeField)
                ->setEmptyString(_t('BusinessLocation.Select', '- select -'));

            $fields->insertBefore('MicroDataStreetAddress', $typeField);
            $fields->insertBefore('MicroDataStreetAddress', $typeSpecificField);

            $fields->addFieldToTab(
                'Root.MapCoordinates',
                FieldGroup::create(
                    CheckboxField::create('IsMicroDataCoordinatesEnabled', _t('BusinessLocation.EnableLocationCoordinates', 'Enable Location Coordinates'))
                )
                    ->setTitle(_t('BusinessLocation.LocationCoordinates', 'Location Coordinates'))
                    ->setName('EnableLocationCoordinatesGroup')
            );

            if ($this->getParentConfig()) {
                $mapsAPIKey = $this->getParentConfig()->getMicroDataMapsAPIKey();
                if ($mapsAPIKey) {
                    $fields->addFieldToTab(
                        'Root.MapCoordinates',
                        $mapField = Wrapper::create(
                            GoogleMapField::create(
                                $this,
                                _t('BusinessLocation.Coordinates', 'Coordinates'),
                                [
                                    'field_names'   =>  [
                                        'Longitude'     =>  'MicroDataLocationLongitude',
                                        'Latitude'      =>  'MicroDataLocationLatitude'
                                    ],
                                    'api_key'       =>  $mapsAPIKey
                                ]
                            )
                        )
                    );
                } else {
                    $fields->addFieldToTab(
                        'Root.MapCoordinates',
                        $mapField = Wrapper::create(
                            LiteralField::create(
                                'CoordinatesInfo',
                                '<p>' . _t('BusinessLocation.AddGoogleMapsAPIKey', 'Please add a Google Maps API key to the main config in order to enable coordinates.') . '</p>'
                            )
                        )
                    );
                }

                $mapField->displayIf('IsMicroDataCoordinatesEnabled')->isChecked();
            }

            $fields->addFieldToTab(
                'Root.Main',
                $paymentAcceptedField = Wrapper::create(
                    CheckboxSetField::create(
                        'MicroDataPaymentAccepted',
                        _t('BusinessLocation.PaymentAccepted', 'Payment Accepted'),
                        [
                            'cash'          =>  _t('BusinessLocation.Cash', 'Cash'),
                            'cheque'        =>  _t('BusinessLocation.Cheque', 'Cheque'),
                            'credit card'   =>  _t('BusinessLocation.CreditCard', 'Credit Card'),
                            'eftpos'        =>  _t('BusinessLocation.EFTPos', 'EFTPos'),
                            'invoice'       =>  _t('BusinessLocation.Invoice', 'Invoice'),
                            'paypal'        =>  _t('BusinessLocation.PayPal', 'PayPal')
                        ]
                    )
                )
            );

            $paymentAcceptedField->displayIf('MicroDataType')->isEqualTo('LocalBusiness');

            $fields->removeByName('MicroDataOpeningHours');
            $fields->addFieldsToTab(
                'Root.OpeningHours',
                [
                    LiteralField::create(
                        'OpeningHoursInfoField',
                        '<p>' . _t('BusinessLocation.OpeningHoursLocalBusinessOnly', 'Opening hours are only applicable to locations of type <em>Local Business</em>') . '</p><br><br>'
                    ),
                    GridField::create(
                        'MicroDataOpeningHours',
                        _t('BusinessLocation.OpeningHours', 'Opening Hours'),
                        $this->MicroDataOpeningHours(),
                        GridFieldConfig_RecordEditor::create()
                    )
                ]
            );
        }

        return $fields;
    }

    #[Override]
    protected function onBeforeDelete()
    {
        parent::onBeforeDelete();

        $items = $this->MicroDataOpeningHours();
        if ($items && $items->Count() > 0) {
            foreach ($items as $item) {
                $item->delete();
            }
        }
    }

    #[Override]
    protected function onBeforeWrite()
    {
        parent::onBeforeWrite();

        $separateEntity = ($this->getParentConfig())
            ? $this->getParentConfig()->IsMicroDataAdditionalLocationsSeparateEntities
            : false;

        if (!$separateEntity) {
            $this->MicroDataType = '';
            $this->MicroDataTypeSpecific = '';
            $this->MicroDataPhone = '';
            $this->MicroDataFax = '';
            $this->MicroDataEmail = '';
            $this->MicroDataPaymentAccepted = '';
            $this->IsMicroDataCoordinatesEnabled = false;
            $this->MicroDataLocationLongitude = '';
            $this->MicroDataLocationLatitude = '';

            $items = $this->MicroDataOpeningHours();
            if ($items && $items->Count() > 0) {
                foreach ($items as $item) {
                    $item->delete();
                }
            }
        } else {
            if ($this->MicroDataType === 'Organization') {
                $this->MicroDataPaymentAccepted = '';

                $items = $this->MicroDataOpeningHours();
                if ($items && $items->Count() > 0) {
                    foreach ($items as $item) {
                        $item->delete();
                    }
                }
            }

            if (!$this->IsMicroDataCoordinatesEnabled) {
                $this->MicroDataLocationLongitude = '';
                $this->MicroDataLocationLatitude = '';
            }
        }
    }
}
