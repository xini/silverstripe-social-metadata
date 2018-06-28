<?php

namespace Innoweb\SocialMeta\Model;

use BetterBrief\GoogleMapField;
use Innoweb\SocialMeta\Extensions\ConfigExtension;
use Sheadawson\DependentDropdown\Forms\DependentDropdownField;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\CheckboxSetField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldGroup;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\Tab;
use SilverStripe\Forms\TabSet;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataObject;
use SilverStripe\SiteConfig\SiteConfig;
use UncleCheese\DisplayLogic\Forms\Wrapper;

class BusinessLocation extends DataObject {

    private static $singular_name = "Business Location";
    private static $plural_name = "Business Locations";

    private static $table_name = 'SocialMetaBusinessLocation';

    private static $db = array(
        'MicroDataTitle' => 'Varchar(255)',

        'MicroDataType' => 'Varchar(255)',
        'MicroDataTypeSpecific' => 'Varchar(255)',

        'MicroDataStreetAddress' => 'Varchar(255)',
        'MicroDataPOBoxNumber' => 'Varchar(255)',
        'MicroDataCity' => 'Varchar(255)',
        'MicroDataPostCode' => 'Varchar(255)',
        'MicroDataRegion' => 'Varchar(255)',
        'MicroDataCountry' => 'Varchar(255)',
        'MicroDataPhone' => 'Varchar(255)',
        'MicroDataFax' => 'Varchar(255)',
        'MicroDataEmail' => 'Varchar(255)',

        'MicroDataPaymentAccepted' => 'Varchar(255)',

        "MicroDataEnableCoordinates" => 'Boolean',
        "MicroDataLocationLongitude" => "Varchar",
        "MicroDataLocationLatitude" => "Varchar",
    );

    private static $has_one = array(
        'SiteConfig' => SiteConfig::class,
        'Site' => SiteTree::class,
    );

    private static $has_many = array(
        'OpeningHours' => OpeningHours::class
    );

    private static $searchable_fields = array(
    );

    private static $summary_fields = array(
        'MicroDataTitle' => 'Name/Title',
        'AddressAsString' => 'Address',
    );

    private static $casting = array(
        'AddressAsString' => 'Varchar(255)',
    );

    public function getCMSFields() {

        $fields = FieldList::create();

        $fields->push(new TabSet("Root", $mainTab = new Tab("Main")));
        $mainTab->setTitle(_t('SiteTree.TABMAIN', "Main"));

        $separateEntity = $this->getParentConfig()->MicroDataAdditionalLocationsSeparateEntities;

        if ($separateEntity) {

            $typeSpecificSource = function($type) {
                if ($type === 'Organization') {
                    $key = 'organization_types';
                } else if ($type === 'LocalBusiness') {
                    $key = 'localbusiness_types';
                } else {
                    return array();
                }
                return Config::inst()->get(ConfigExtension::class, $key);
            };

            $fields->addFieldsToTab(
                "Root.Main",
                array(
                    $typeField = DropdownField::create(
                        "MicroDataType",
                        "Type",
                        array(
                            'Organization' => 'Organisation',
                            'LocalBusiness' => 'Local Business'
                        )
                    ),
                    DependentDropdownField::create(
                        'MicroDataTypeSpecific',
                        'More specific type',
                        $typeSpecificSource
                    )->setDepends($typeField)->setEmptyString('- select -')
                )
            );
        }

        // address fields
        $fields->addFieldsToTab(
            "Root.Main",
            array(
                TextField::create('MicroDataTitle', 'Name/Title'),
                TextField::create('MicroDataStreetAddress', 'Street Address'),
                TextField::create('MicroDataPOBoxNumber', 'PO Box Number'),
                TextField::create('MicroDataCity', 'City'),
                TextField::create('MicroDataPostCode', 'Post Code'),
                TextField::create('MicroDataRegion', 'State/Region'),
                TextField::create('MicroDataCountry', 'Country'),
            )
        );

        if ($separateEntity) {

            // contact details, opening hours, payment methods
            $fields->addFieldsToTab(
                "Root.Main",
                array(
                    TextField::create('MicroDataPhone', 'Phone'),
                    TextField::create('MicroDataFax', 'Fax'),
                    TextField::create('MicroDataEmail', 'Email'),

                    FieldGroup::create(
                        CheckboxField::create('MicroDataEnableCoordinates', '')
                    )
                        ->setTitle('Enable Location Coordinates')
                        ->setName('EnableLocationCoordinatesGroup'),

                    $paymentAcceptedField = CheckboxSetField::create(
                        "MicroDataPaymentAccepted",
                        "Payment Accepted",
                        array(
                            'cash' => 'Cash',
                            'cheque' => 'Cheque',
                            'credit card' => 'Credit Card',
                            'eftpos' => 'EFTPos',
                            'invoice' => 'Invoice',
                            'paypal' => 'PayPal',
                        )
                    ),

                    $openingHoursField = Wrapper::create(
                        GridField::create(
                            'OpeningHours',
                            'Opening Hours',
                            $this->OpeningHours(),
                            GridFieldConfig_RecordEditor::create()
                        )
                    )
                )
            );

            $openingHoursField->displayIf('MicroDataType')->isEqualTo('LocalBusiness');
            $paymentAcceptedField->displayIf('MicroDataType')->isEqualTo('LocalBusiness');

            // get final api key, including local one
            $mapsApiKey = $this->getParentConfig()->getGoogleMapsAPIKey();
            // add map field
            if ($mapsApiKey) {

                $fields->insertAfter(
                    'EnableLocationCoordinatesGroup',
                    $coordinatesField = Wrapper::create(
                        GoogleMapField::create(
                            $this,
                            'Coordinates',
                            array(
                                "field_names" => array(
                                    "Longitude" => "MicroDataLocationLongitude",
                                    "Latitude" => "MicroDataLocationLatitude",
                                ),
                                "api_key" => $mapsApiKey,
                            )
                        )
                    )
                );

                $coordinatesField->displayIf('MicroDataEnableCoordinates')->isChecked();

            } else {

                $fields->insertAfter(
                    'EnableLocationCoordinatesGroup',
                    $coordinatesInfoField = Wrapper::create(
                        LiteralField::create(
                            'CoordinatesInfo',
                            '<p>'._t('SocialMetaBusinessLocation.AddGoogleMapsAPIKey', 'Please add a Google Maps API key to the main config in order to enable coordinates.').'</p>'
                        )
                    )
                );

                $coordinatesInfoField->displayIf('MicroDataEnableCoordinates')->isChecked();
            }
        }

        return $fields;
    }

    protected function onBeforeDelete() {
        parent::onBeforeDelete();

        $items = $this->OpeningHours();
        if ($items && $items->Count() > 0) {
            foreach($items as $item) {
                $item->delete();
            }
        }
    }

    protected function onBeforeWrite() {
        parent::onBeforeWrite();

        $separateEntity = $this->getParentConfig()->MicroDataAdditionalLocationsSeparateEntities;
        if (!$separateEntity) {

            $this->MicroDataType = "";
            $this->MicroDataTypeSpecific = "";

            $this->MicroDataPhone = "";
            $this->MicroDataFax = "";
            $this->MicroDataEmail = "";

            $this->MicroDataPaymentAccepted = "";

            $this->MicroDataEnableCoordinates = false;
            $this->MicroDataLocationLongitude = "";
            $this->MicroDataLocationLatitude = "";

            $items = $this->OpeningHours();
            if ($items && $items->Count() > 0) {
                foreach($items as $item) {
                    $item->delete();
                }
            }

        } else {

            if ($this->MicroDataType == "Organization") {

                $this->MicroDataPaymentAccepted = "";

                $items = $this->OpeningHours();
                if ($items && $items->Count() > 0) {
                    foreach($items as $item) {
                        $item->delete();
                    }
                }

            }

            // remove coordinates
            if (!$this->MicroDataEnableCoordinates) {
                $this->MicroDataLocationLongitude = "";
                $this->MicroDataLocationLatitude = "";
            }

        }

    }

    public function getParentConfig() {
        if ($this->SiteConfigID > 0) {
            return $this->SiteConfig();
        } else {
            return $this->Site();
        }
    }

    public function getAddressAsString() {
        $address = array();
        if ($this->MicroDataStreetAddress) {
            $address[] = $this->MicroDataStreetAddress . ',';
        }
        if ($this->MicroDataPOBoxNumber) {
            $address[] = 'PO Box ' . $this->MicroDataPOBoxNumber . ',';
        }
        if ($this->MicroDataCity) {
            $address[] = $this->MicroDataCity;
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

    public function getSocialMetaSchemaType($baseTypeOnly = false) {
        if ($this->MicroDataTypeSpecific && $baseTypeOnly == false) {
            return $this->MicroDataTypeSpecific;
        } else {
            return $this->MicroDataType;
        }
    }

    public function getSocialMetaMapLink() {
        if ($this->MicroDataStreetAddress) {
            $address = array();
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
            return "https://www.google.com.au/maps/place/".urlencode($address);
        }
        return null;
    }


}
