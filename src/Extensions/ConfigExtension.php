<?php

namespace Innoweb\SocialMeta\Extensions;

use BetterBrief\GoogleMapField;
use Innoweb\SocialMeta\Model\BusinessLocation;
use Innoweb\SocialMeta\Model\OpeningHours;
use Sheadawson\DependentDropdown\Forms\DependentDropdownField;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\Image;
use SilverStripe\CMS\Controllers\RootURLController;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\CheckboxSetField;
use SilverStripe\Forms\DatetimeField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldGroup;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataExtension;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\View\ArrayData;
use Symbiote\MultiValueField\Fields\MultiValueTextField;
use UncleCheese\DisplayLogic\Forms\Wrapper;

class ConfigExtension extends DataExtension {

    private static $db = array(
        'MetaFacebookPage' => 'Varchar(255)',
        'MetaFacebookAppId' => 'Varchar(255)',
        'MetaFacebookAdmins' => 'MultiValueField',
        'MetaTwitterHandle' => 'Varchar(50)',

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
        "MicroDataGoogleMapsAPIKey" => 'Varchar(255)',
        "MicroDataLocationLongitude" => "Varchar",
        "MicroDataLocationLatitude" => "Varchar",

        'MicroDataAdditionalLocations' => 'Boolean',
        'MicroDataAdditionalLocationsSeparateEntities' => 'Boolean',

        "MicroDataEventLocationName" => "Varchar(255)",
        "MicroDataEventLocationWebsite" => "Varchar(255)",
        "MicroDataEventStart" => "Datetime",
        "MicroDataEventEnd" => "Datetime",
    );

    private static $has_one = array(
        'MicroDataLogo' => Image::class,
    );
    
    private static $owns = [
        'MicroDataLogo'
    ];

    private static $has_many = array(
        'OpeningHours' => OpeningHours::class,
        'AdditionalLocations' => BusinessLocation::class
    );

    public function updateCMSFields(FieldList $fields) {

        if (
            !class_exists('Symbiote\Multisites\Multisites')
            || (Config::inst()->get(ConfigExtension::class, 'multisites_enable_global_settings') && $this->owner instanceof SiteConfig)
            || (!Config::inst()->get(ConfigExtension::class, 'multisites_enable_global_settings') && $this->owner instanceof \Symbiote\Multisites\Model\Site)
        ) {

            // facebook
            $fields->addFieldsToTab("Root.SocialMetadata", array(
                new HeaderField("facebookheader", _t("SocialMetaConfigExtension.FACEBOOK", 'Facebook'), 2),
                new TextField("MetaFacebookAppId", _t("SocialMetaConfigExtension.FACEBOOKAPPID", 'Facebook App ID')),
                new MultiValueTextField('MetaFacebookAdmins',_t('SocialMetaConfigExtension.FACEBOOKADMINIDS','Facebook Admin IDs')),
                new TextField("MetaFacebookPage", _t("SocialMetaConfigExtension.FACEBOOKPAGE", 'Facebook Page (full URL)')),
            ));

            // twitter
            $fields->addFieldsToTab("Root.SocialMetadata", array(
                new HeaderField("twitterheader", _t("SocialMetaConfigExtension.TWITTER", 'Twitter'), 2),
                new TextField("MetaTwitterHandle", _t("SocialMetaConfigExtension.TWITTERHANDLE", 'Twitter Handle (@xyz)')),
            ));

            $typeSpecificSource = function($type) {
                if ($type === 'Organization') {
                    $key = 'organization_types';
                } else if ($type === 'LocalBusiness') {
                    $key = 'localbusiness_types';
                } else if ($type === 'Event') {
                    $key = 'event_types';
                } else {
                    return array();
                }
                return Config::inst()->get(ConfigExtension::class, $key);
            };

            // micro data
            $fields->addFieldsToTab(
                "Root.SocialMetadata",
                array(
                    new HeaderField("microdataheader", _t("SocialMetaConfigExtension.MicroData", 'Micro Data'), 2),

                    $typeField = DropdownField::create(
                        "MicroDataType",
                        "Type",
                        array(
                            'Organization' => 'Organisation',
                            'LocalBusiness' => 'Local Business',
                            'Event' => 'Event',
                        )
                    ),

                    DependentDropdownField::create(
                        'MicroDataTypeSpecific',
                        'More specific type',
                        $typeSpecificSource
                    )->setDepends($typeField)->setEmptyString('- select -'),

                    UploadField::create("MicroDataLogo", _t("SocialMetaConfigExtension.Logo", 'Logo'))
                        ->setFolderName('social')
                        ->setAllowedExtensions(array('jpg', 'gif', 'png')),

                    $microEventLocationField = TextField::create('MicroDataEventLocationName', 'Event Location Name'),
                    $microEventLocationWebsiteField = TextField::create('MicroDataEventLocationWebsite', 'Event Location Website'),
                    $microEventStartField = DatetimeField::create('MicroDataEventStart', 'Event Start'),
                    $microEventEndField = DatetimeField::create('MicroDataEventEnd', 'Event End'),

                    TextField::create('MicroDataStreetAddress', 'Street Address'),
                    TextField::create('MicroDataPOBoxNumber', 'PO Box Number'),
                    TextField::create('MicroDataCity', 'City'),
                    TextField::create('MicroDataPostCode', 'Post Code'),
                    TextField::create('MicroDataRegion', 'State/Region'),
                    TextField::create('MicroDataCountry', 'Country'),
                    TextField::create('MicroDataPhone', 'Phone'),
                    TextField::create('MicroDataFax', 'Fax'),
                    $microEmailField = TextField::create('MicroDataEmail', 'Email'),

                    FieldGroup::create(
                        CheckboxField::create("MicroDataEnableCoordinates", "")
                    )
                        ->setTitle("Enable Location Coordinates")
                        ->setName('EnableLocationCoordinatesGroup'),

                    $microPaymentAcceptedField = CheckboxSetField::create(
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
                            $this->owner->OpeningHours(),
                            GridFieldConfig_RecordEditor::create()
                        )
                    ),

                    FieldGroup::create(
                        CheckboxField::create("MicroDataAdditionalLocations", "This organisation/business has additional locations"),
                        $locationsSeperateEntitiesField = CheckboxField::create("MicroDataAdditionalLocationsSeparateEntities", "The additional locations are separate businesses/departments (e.g. they have separate contact numbers, opening hours, etc.)")
                    )->setTitle("Business structure")
                    ->addExtraClass(''),

                    $additionalLocationsField = Wrapper::create(
                        GridField::create(
                            'AdditionalLocations',
                            'Additional Locations',
                            $this->owner->AdditionalLocations(),
                            GridFieldConfig_RecordEditor::create()
                        )
                    )
                )
            );

            $microEmailField
                ->displayIf('MicroDataType')->isEqualTo('Organization')
                ->orIf('MicroDataType')->isEqualTo('LocalBusiness');
            $openingHoursField->displayIf('MicroDataType')->isEqualTo('LocalBusiness');
            $microPaymentAcceptedField->displayIf('MicroDataType')->isEqualTo('LocalBusiness');
            $additionalLocationsField->displayIf('MicroDataAdditionalLocations')->isChecked();
            $locationsSeperateEntitiesField->displayIf('MicroDataAdditionalLocations')->isChecked();

            $microEventLocationField->displayIf('MicroDataType')->isEqualTo('Event');
            $microEventLocationWebsiteField->displayIf('MicroDataType')->isEqualTo('Event');
            $microEventStartField->displayIf('MicroDataType')->isEqualTo('Event');
            $microEventEndField->displayIf('MicroDataType')->isEqualTo('Event');

            // get maps api key from external sources
            $mapsApiKey = $this->owner->getGoogleMapsAPIKey(false);
            // add api key field if not already configured elsewhere
            if (!$mapsApiKey) {

                $fields->insertAfter(
                    'EnableLocationCoordinatesGroup',
                    $mapsAPIKeyField = TextField::create('MicroDataGoogleMapsAPIKey', 'Google Maps API key')
                );
                $mapsAPIKeyField->displayIf('MicroDataEnableCoordinates')->isChecked();
            }
            // get final api key, including local one
            $mapsApiKey = $this->owner->getGoogleMapsAPIKey();

            // add map field
            if ($mapsApiKey) {

                $fields->insertAfter(
                    'EnableLocationCoordinatesGroup',
                    $coordinatesField = Wrapper::create(
                        GoogleMapField::create(
                            $this->owner,
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
                            '<p>'._t('SocialMetaConfigExtension.AddGoogleMapsAPIKey', 'Please add a Google Maps API key and save the config in order to set the coordinates.').'</p>'
                        )
                    )
                );
                $coordinatesInfoField->displayIf('MicroDataEnableCoordinates')->isChecked();
            }

            // set tab titles
            $fields->fieldByName("Root.SocialMetadata")->setTitle(_t('SocialMetaConfigExtension.MetadataTab', 'Social Metadata'));

        }
    }

    public function getGoogleMapsAPIKey($includeLocal = true) {
        $mapsApiKey = null;
        // check if API key already exists somewhere else
        if (($siteConfig = SiteConfig::current_site_config()) && $siteConfig->APIKey) {
            // goggle maps module
            $mapsApiKey = $siteConfig->APIKey;
        } else if (Config::inst()->get(GoogleMapField::class, 'default_options.api_key')) {
            // default config google maps field
            $mapsApiKey = Config::inst()->get(GoogleMapField::class, 'default_options.api_key');
        }
        // check if configured here
        if (!$mapsApiKey && $includeLocal) {
            $mapsApiKey = $this->owner->MicroDataGoogleMapsAPIKey;
        }
        return $mapsApiKey;
    }

    public function updateSiteCMSFields(FieldList $fields) {
        $this->updateCMSFields($fields);
    }

    public function onBeforeWrite() {
        parent::onBeforeWrite();

        $this->owner->MicroDataEventLocationWebsite = $this->updateLinkURL($this->owner->MicroDataEventLocationWebsite);

        // clean up data
        if ($this->owner->MicroDataType == "Organization") {

            $this->owner->MicroDataPaymentAccepted = "";
            $this->owner->MicroDataEventLocationName = "";
            $this->owner->MicroDataEventLocationWebsite = "";
            $this->owner->MicroDataEventStart = "";
            $this->owner->MicroDataEventEnd = "";

            $items = $this->owner->OpeningHours();
            if ($items && $items->Count() > 0) {
                foreach($items as $item) {
                    $item->delete();
                }
            }

        } else if ($this->owner->MicroDataType == "LocalBusiness") {

            $this->owner->MicroDataEventLocationName = "";
            $this->owner->MicroDataEventLocationWebsite = "";
            $this->owner->MicroDataEventStart = "";
            $this->owner->MicroDataEventEnd = "";

        } else if ($this->owner->MicroDataType == "Event") {

            $this->owner->MicroDataEmail = "";
            $this->owner->MicroDataPaymentAccepted = "";

            $items = $this->owner->OpeningHours();
            if ($items && $items->Count() > 0) {
                foreach($items as $item) {
                    $item->delete();
                }
            }

        }

        // remove coordinates
        if (!$this->owner->MicroDataEnableCoordinates) {
            $this->owner->MicroDataLocationLongitude = "";
            $this->owner->MicroDataLocationLatitude = "";
        }

        // remove locations
        if (!$this->owner->MicroDataAdditionalLocations || $this->owner->MicroDataType == "Event") {
            $items = $this->owner->AdditionalLocations();
            if ($items && $items->Count() > 0) {
                foreach($items as $item) {
                    $item->delete();
                }
            }
        }

    }

    private function updateLinkURL($url) {
        if($url) {
            if(
                substr($url, 0, 8) != 'https://'
                && substr($url, 0, 7) != 'http://'
                && substr($url, 0, 6) != 'ftp://'
            ) {
                $url = 'http://' . $url;
            }
        }
        return $url;
    }

    public function getSchemaData() {
        $currentPage = Director::get_current_page();
        // setup data array
        $data = array(
            "@context" => "http://schema.org",
            "@type" => $this->owner->getSocialMetaSchemaType(),
        );

        // generic properties
        if ($this->owner->getSocialMetaSiteName()) {
            $data["name"] = $this->owner->getSocialMetaSiteName();
        }
        if ($this->owner->getSocialMetaSiteDescription()) {
            $data["description"] = $this->owner->getSocialMetaSiteDescription();
        }
        if (($logo = $this->owner->MicroDataLogo()) && $logo->exists()) {
            $data["logo"] = array(
                "@type" => 'ImageObject',
                "url" => $logo->AbsoluteLink(),
                "width" => $logo->getWidth().'px',
                "height" => $logo->getHeight().'px',
            );
        }
        if ($currentPage && $currentPage->hasMethod('getSocialMetaImage') && ($image = $currentPage->getSocialMetaImage()) && $image->exists()) {
            $data["image"] = array(
                "@type" => 'ImageObject',
                "url" => $image->AbsoluteLink(),
                "width" => $image->getWidth().'px',
                "height" => $image->getHeight().'px',
            );
        }
        if ($this->owner->getSocialMetaSiteURL()) {
            $data["url"] = $this->owner->getSocialMetaSiteURL();
        }
        if ($this->owner->getSocialMetaProfilePages() && $this->owner->getSocialMetaProfilePages()->exists()) {
            $sameAs = array();
            foreach ($this->owner->getSocialMetaProfilePages() as $profile) {
                $sameAs[] = $profile->URL;
            }
            if (count($sameAs)) {
                $data["sameAs"] = $sameAs;
            }
        }

        // build main addresses
        $addresses = array();
        if ($this->owner->MicroDataStreetAddress || $this->owner->MicroDataPOBoxNumber || $this->owner->MicroDataCity || $this->owner->MicroDataPostCode) {
            $address = array(
                "@type" => "PostalAddress"
            );
            if ($this->owner->MicroDataCountry) {
                $address["addressCountry"] = $this->owner->MicroDataCountry;
            }
            if ($this->owner->MicroDataCity) {
                $address["addressLocality"] = $this->owner->MicroDataCity;
            }
            if ($this->owner->MicroDataRegion) {
                $address["addressRegion"] = $this->owner->MicroDataRegion;
            }
            if ($this->owner->MicroDataPostCode) {
                $address["postalCode"] = $this->owner->MicroDataPostCode;
            }
            if ($this->owner->MicroDataPOBoxNumber) {
                $address["postOfficeBoxNumber"] = $this->owner->MicroDataPOBoxNumber;
            }
            if ($this->owner->MicroDataStreetAddress) {
                $address["streetAddress"] = $this->owner->MicroDataStreetAddress;
            }
        }
        if ($this->owner->MicroDataAdditionalLocations && ($locations = $this->owner->AdditionalLocations())) {

            // check if separate entities
            if (!$this->owner->MicroDataAdditionalLocationsSeparateEntities) {

                // add title to main address and save
                if (isset($address) && $this->owner->getSocialMetaSiteName()) {
                    $address["name"] = $this->owner->getSocialMetaSiteName();
                }
                if (isset($address)) {
                    $addresses[] = $address;
                }

                // load additional addresses
                foreach ($locations as $location) {
                    if ($location->MicroDataStreetAddress || $location->MicroDataPOBoxNumber || $location->MicroDataCity || $location->MicroDataPostCode) {
                        $address = array(
                            "@type" => "PostalAddress"
                        );
                        if ($location->MicroDataTitle) {
                            $address["name"] = $location->MicroDataTitle;
                        }
                        if ($location->MicroDataCountry) {
                            $address["addressCountry"] = $location->MicroDataCountry;
                        }
                        if ($location->MicroDataCity) {
                            $address["addressLocality"] = $location->MicroDataCity;
                        }
                        if ($location->MicroDataRegion) {
                            $address["addressRegion"] = $location->MicroDataRegion;
                        }
                        if ($location->MicroDataPostCode) {
                            $address["postalCode"] = $location->MicroDataPostCode;
                        }
                        if ($location->MicroDataPOBoxNumber) {
                            $address["postOfficeBoxNumber"] = $location->MicroDataPOBoxNumber;
                        }
                        if ($location->MicroDataStreetAddress) {
                            $address["streetAddress"] = $location->MicroDataStreetAddress;
                        }
                        $addresses[] = $address;
                    }
                }

            } else {

                // save main address
                if (isset($address)) {
                    $addresses = $address;
                }

                // load additional locations
                $subOrganisations = array();
                foreach ($locations as $location) {
                    // setup type
                    $organisation = array(
                        "@type" => $location->getSocialMetaSchemaType(),
                        "parentOrganization" => array(
                            "@type" => $this->owner->getSocialMetaSchemaType(),
                            "name" => $this->owner->getSocialMetaSiteName(),
                        )
                    );
                    // add name
                    if ($location->MicroDataTitle) {
                        $organisation["name"] = $location->MicroDataTitle;
                    }
                    // build address
                    if ($location->MicroDataStreetAddress || $location->MicroDataPOBoxNumber || $location->MicroDataCity || $location->MicroDataPostCode) {
                        $address = array(
                            "@type" => "PostalAddress"
                        );
                        if ($location->MicroDataTitle) {
                            $address["name"] = $location->MicroDataTitle;
                        }
                        if ($location->MicroDataCountry) {
                            $address["addressCountry"] = $location->MicroDataCountry;
                        }
                        if ($location->MicroDataCity) {
                            $address["addressLocality"] = $location->MicroDataCity;
                        }
                        if ($location->MicroDataRegion) {
                            $address["addressRegion"] = $location->MicroDataRegion;
                        }
                        if ($location->MicroDataPostCode) {
                            $address["postalCode"] = $location->MicroDataPostCode;
                        }
                        if ($location->MicroDataPOBoxNumber) {
                            $address["postOfficeBoxNumber"] = $location->MicroDataPOBoxNumber;
                        }
                        if ($location->MicroDataStreetAddress) {
                            $address["streetAddress"] = $location->MicroDataStreetAddress;
                        }
                        $organisation["address"] = $address;
                    }
                    // contact details
                    if ($location->MicroDataPhone) {
                        $organisation["telephone"] = $location->MicroDataPhone;
                    }
                    if ($location->MicroDataFax) {
                        $organisation["faxNumber"] = $location->MicroDataFax;
                    }
                    if ($location->MicroDataEmail) {
                        $organisation["email"] = $location->MicroDataEmail;
                    }
                    // map link
                    if ($location->getSocialMetaSchemaType(true) == "LocalBusiness" && $location->getSocialMetaMapLink()) {
                        $organisation["hasMap"] = $location->getSocialMetaMapLink();
                    }
                    // build coordinates
                    if ($location->MicroDataEnableCoordinates && $location->MicroDataLocationLatitude && $location->MicroDataLocationLongitude) {
                        $coordinates = array(
                            "@type" => "GeoCoordinates",
                            "latitude" => $location->MicroDataLocationLatitude,
                            "longitude" => $location->MicroDataLocationLongitude,
                        );
                        $organisation["geo"] = $coordinates;
                    }
                    // business properties
                    if (($objects = $location->OpeningHours()) && $objects->exists()) {
                        $hours = array();
                        foreach ($objects as $object) {
                            $row = $object->Days;
                            if ($object->TimeOpen && $object->TimeClose) {
                                $row .= ' ' . $object->TimeOpen . '-' . $object->TimeClose;
                            }
                            $hours[] = $row;
                        }
                        if (count($hours)) {
                            $organisation["openingHours"] = $hours;
                        }
                    }
                    if ($location->MicroDataPaymentAccepted) {
                        $organisation["paymentAccepted"] = $location->MicroDataPaymentAccepted;
                    }
                    // add location to sub organisations
                    $subOrganisations[] = $organisation;
                }
                if (count($subOrganisations)) {
                    $data["subOrganization"] = $subOrganisations;
                }
            }

        } else {
            // save main address
            if (isset($address)) {
                $addresses[] = $address;
            }
        }

        // build coordinates
        if ($this->owner->MicroDataEnableCoordinates && $this->owner->MicroDataLocationLongitude && $this->owner->MicroDataLocationLatitude) {
            $coordinates = array(
                "@type" => "GeoCoordinates",
                "latitude" => $this->owner->MicroDataLocationLatitude,
                "longitude" => $this->owner->MicroDataLocationLongitude,
            );
        }

        // event data
        if ($this->owner->getSocialMetaSchemaType == "Event") {

            // add data to event and event location

            if ($this->owner->MicroDataEventStart) {
                $data["startDate"] = $this->owner->dbObject('MicroDataEventStart')->Rfc3339();
            }
            if ($this->owner->MicroDataEventEnd) {
                $data["endDate"] = $this->owner->dbObject('MicroDataEventEnd')->Rfc3339();
            }
            // event location
            $location = array(
                "@type" => "Place",
            );
            if ($this->owner->MicroDataEventLocationName) {
                $location["name"] = $this->owner->MicroDataEventLocationName;
            }
            if ($this->owner->MicroDataEventLocationWebsite) {
                $location["sameAs"] = $this->owner->MicroDataEventLocationWebsite;
            }
            // address
            if (isset($addresses) && count($addresses)) {
                $location["address"] = $addresses;
            }
            // contact details
            if ($this->owner->MicroDataPhone) {
                $location["telephone"] = $this->owner->MicroDataPhone;
            }
            if ($this->owner->MicroDataFax) {
                $location["faxNumber"] = $this->owner->MicroDataFax;
            }
            if ($this->owner->MicroDataEmail) {
                $location["email"] = $this->owner->MicroDataEmail;
            }
            // coordinates
            if (isset($coordinates)) {
                $location["geo"] = $coordinates;
            }

            $data["location"] = $location;

        } else {

            // add address and contact data to main data if not an event

            // address
            if (isset($addresses) && count($addresses)) {
                $data["address"] = $addresses;
            }
            // contact details
            if ($this->owner->MicroDataPhone) {
                $data["telephone"] = $this->owner->MicroDataPhone;
            }
            if ($this->owner->MicroDataFax) {
                $data["faxNumber"] = $this->owner->MicroDataFax;
            }
            if ($this->owner->MicroDataEmail) {
                $data["email"] = $this->owner->MicroDataEmail;
            }
            // coordinates
            if (isset($coordinates)) {
                $data["geo"] = $coordinates;
            }

        }

        if ($this->owner->getSocialMetaSchemaType(true) == "LocalBusiness") {

            // map link
            if ($this->owner->getSocialMetaMapLink()) {
                $data["hasMap"] = $this->owner->getSocialMetaMapLink();
            }

        }

        // business properties
        if ($objects = $this->owner->OpeningHours()) {
            $hours = array();
            foreach ($objects as $object) {
                $row = $object->Days;
                if ($object->TimeOpen && $object->TimeClose) {
                    $row .= ' ' . $object->TimeOpen . '-' . $object->TimeClose;
                }
                $hours[] = $row;
            }
            if (count($hours)) {
                $data["openingHours"] = $hours;
            }
        }
        if ($this->owner->MicroDataPaymentAccepted) {
            $data["paymentAccepted"] = $this->owner->MicroDataPaymentAccepted;
        }

        // return ld+json string
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public function getSocialMetaSchemaType($baseTypeOnly = false) {
        if ($this->owner->MicroDataTypeSpecific && $baseTypeOnly == false) {
            return $this->owner->MicroDataTypeSpecific;
        } else {
            return $this->owner->MicroDataType;
        }
    }

    /**
     * using data added by innoweb/silverstripe-social-profiles if available
     */
    public function getSocialMetaProfilePages() {
        $profiles = array();
        if ($this->owner->ProfilesFacebookPage) {
            $profiles[] = new ArrayData(array("URL" => $this->owner->ProfilesFacebookPage));
        }
        if ($this->owner->ProfilesTwitterPage) {
            $profiles[] = new ArrayData(array("URL" => $this->owner->ProfilesTwitterPage));
        }
        if ($this->owner->ProfilesGooglePage) {
            $profiles[] = new ArrayData(array("URL" => $this->owner->ProfilesGooglePage));
        }
        if ($this->owner->ProfilesLinkedinPage) {
            $profiles[] = new ArrayData(array("URL" => $this->owner->ProfilesLinkedinPage));
        }
        if ($this->owner->ProfilesPinterestPage) {
            $profiles[] = new ArrayData(array("URL" => $this->owner->ProfilesPinterestPage));
        }
        if ($this->owner->ProfilesInstagramPage) {
            $profiles[] = new ArrayData(array("URL" => $this->owner->ProfilesInstagramPage));
        }
        if ($this->owner->ProfilesYoutubePage) {
            $profiles[] = new ArrayData(array("URL" => $this->owner->ProfilesYoutubePage));
        }
        return new ArrayList($profiles);
    }

    public function getSocialMetaSiteName() {
        if ($this->owner->DefaultSharingTitle) {
            return $this->owner->DefaultSharingTitle;
        } else if ($this->owner->Title) {
            return $this->owner->Title;
        }
        return null;
    }

    public function getSocialMetaSiteDescription() {
        if ($this->owner->DefaultSharingDescription) {
            return $this->owner->DefaultSharingDescription;
        } else if (($homelink = RootURLController::get_homepage_link()) && $home = SiteTree::get_by_link($homelink)) {
            return $home->getSocialMetaDescription();
        }
        return null;
    }

    public function getSocialMetaSiteURL() {
        return Director::absoluteBaseURL();
    }

    public function getSocialMetaMapLink() {
        if ($this->owner->MicroDataStreetAddress) {
            $address = array();
            $address[] = $this->owner->MicroDataStreetAddress . ',';
            if ($this->owner->MicroDataCity) {
                $address[] = $this->owner->MicroDataCity;
            }
            if ($this->owner->MicroDataRegion) {
                $address[] = $this->owner->MicroDataRegion;
            }
            if ($this->owner->MicroDataPostCode) {
                $address[] = $this->owner->MicroDataPostCode;
            }
            $address = implode(' ', $address);
            return "https://www.google.com.au/maps/place/".urlencode($address);
        }
        return null;
    }

    public function getSocialMetaAdditionalLocations() {
        if (($locations = $this->owner->AdditionalLocations()) && $locations->exists()) {
            return $locations;
        }
        return null;
    }

}
