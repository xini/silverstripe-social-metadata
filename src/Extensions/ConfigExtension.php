<?php

namespace Innoweb\SocialMeta\Extensions;

use BetterBrief\GoogleMapField;
use BurnBright\ExternalURLField\ExternalURLField;
use Innoweb\InternationalPhoneNumberField\Forms\InternationalPhoneNumberField;
use Innoweb\SocialMeta\Model\BusinessLocation;
use Innoweb\SocialMeta\Model\OpeningHours;
use Sheadawson\DependentDropdown\Forms\DependentDropdownField;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\Image;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\CheckboxSetField;
use SilverStripe\Forms\DatetimeField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\EmailField;
use SilverStripe\Forms\FieldGroup;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\ORM\DataExtension;
use Symbiote\MultiValueField\Fields\MultiValueTextField;
use UncleCheese\DisplayLogic\Forms\Wrapper;

class ConfigExtension extends DataExtension
{
    private static $socialmeta_images_folder;

    private static $db = [
        'SocialMetaSiteName'            =>  'Varchar(255)',
        'SocialMetaSiteDescription'     =>  'Text',

        'SocialMetaFacebookPage'        =>  'ExternalURL',
        'SocialMetaFacebookAppID'       =>  'Varchar(20)',
        'SocialMetaFacebookAdminIDs'    =>  'MultiValueField',
        'SocialMetaTwitterAccount'      =>  'Varchar(20)',

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
        'MicroDataGoogleMapsAPIKey'     =>  'Varchar(255)',
        'MicroDataLocationLongitude'    =>  'Varchar',
        'MicroDataLocationLatitude'     =>  'Varchar',

        'HasMicroDataAdditionalLocations'                   =>  'Boolean',
        'IsMicroDataAdditionalLocationsSeparateEntities'    =>  'Boolean',

        'MicroDataEventLocationName'    =>  'Varchar(255)',
        'MicroDataEventLocationWebsite' =>  'ExternalURL',
        'MicroDataEventStart'           =>  'Datetime',
        'MicroDataEventEnd'             =>  'Datetime',

        'SocialMetaSameAsLinks'         =>  'MultiValueField',
    ];

    private static $has_one = [
        'MicroDataSiteLogo'             =>  Image::class,
        'SocialMetaSiteImage'           =>  Image::class
    ];

    private static $has_many = [
        'MicroDataAdditionalLocations'  =>  BusinessLocation::class . '.SocialMetaConfigOf',
        'MicroDataOpeningHours'         =>  OpeningHours::class . '.SocialMetaConfigOf'
    ];

    private static $owns = [
        'MicroDataSiteLogo',
        'SocialMetaSiteImage',
        'MicroDataAdditionalLocations',
        'MicroDataOpeningHours'
    ];

    private static $cascade_deletes = [
        'MicroDataAdditionalLocations',
        'MicroDataOpeningHours'
    ];

    public function getMicroDataSchemaData()
    {
        $currentPage = Director::get_current_page();

        $data = [
            '@context'          =>  'http://schema.org',
            '@type'             =>  $this->getOwner()->getMicroDataSchemaType(),
            '@id'               =>  $this->getOwner()->getSocialMetaValue('SchemaSiteID'),
            'mainEntityOfPage'  =>  $this->getOwner()->getSocialMetaValue('SiteURL'),
        ];

        if ($this->getOwner()->getSocialMetaValue('SiteName')) {
            $data['name'] = $this->getOwner()->getSocialMetaValue('SiteName');
        }

        if ($this->getOwner()->getSocialMetaValue('SiteDescription')) {
            $data['description'] = $this->getOwner()->getSocialMetaValue('SiteDescription');
        }

        $logo = $this->getOwner()->getSocialMetaValue('SiteLogo');
        if ($logo && $logo->exists()) {
            $data['logo'] = [
                '@type'     =>  'ImageObject',
                'url'       =>  $logo->getAbsoluteURL(),
                'width'     =>  $logo->getWidth() . 'px',
                'height'    =>  $logo->getHeight() . 'px'
            ];
        }

        if ($currentPage) {
            $image = $currentPage->getSocialMetaValue('Image');
            if ($image && $image->exists()) {
                $data['image'] = [
                    '@type'     =>  'ImageObject',
                    'url'       =>  $image->getAbsoluteURL(),
                    'width'     =>  $image->getWidth() . 'px',
                    'height'    =>  $image->getHeight() . 'px'
                ];
            }
        }

        if ($this->getOwner()->getSocialMetaValue('SiteURL')) {
            $data['url'] = $this->getOwner()->getSocialMetaValue('SiteURL');
        }

        $addresses = [];
        if (
            $this->getOwner()->MicroDataStreetAddress
            || $this->getOwner()->MicroDataPOBoxNumber
            || $this->getOwner()->MicroDataCity
            || $this->getOwner()->MicroDataPostCode
        ) {
            $address = [
                '@type'     =>  'PostalAddress'
            ];

            if ($this->getOwner()->MicroDataCountry) {
                $address['addressCountry'] = $this->getOwner()->MicroDataCountry;
            }
            if ($this->getOwner()->MicroDataCity) {
                $address['addressLocality'] = $this->getOwner()->MicroDataCity;
            }
            if ($this->getOwner()->MicroDataRegion) {
                $address['addressRegion'] = $this->getOwner()->MicroDataRegion;
            }
            if ($this->getOwner()->MicroDataPostCode) {
                $address['postalCode'] = $this->getOwner()->MicroDataPostCode;
            }
            if ($this->getOwner()->MicroDataPOBoxNumber) {
                $address['postOfficeBoxNumber'] = $this->getOwner()->MicroDataPOBoxNumber;
            }
            if ($this->getOwner()->MicroDataStreetAddress) {
                $address['streetAddress'] = $this->getOwner()->MicroDataStreetAddress;
            }
        }

        $additionalLocations = $this->getOwner()->MicroDataAdditionalLocations();
        if ($this->getOwner()->HasMicroDataAdditionalLocations && $additionalLocations) {

            if (!$this->getOwner()->IsMicroDataAdditionalLocationsSeparateEntities) {

                if (isset($address) && $this->getOwner()->getSocialMetaValue('SiteName')) {
                    $address['name'] = $this->getOwner()->getSocialMetaValue('SiteName');
                }

                if (isset($address)) {
                    $addresses[] = $address;
                }

                foreach($additionalLocations as $location) {
                    if (
                        $location->MicroDataStreetAddress
                        || $location->MicroDataPOBoxNumber
                        || $location->MicroDataCity
                        || $location->MicroDataPostCode
                    ) {
                        $address = [
                            '@type'     =>  'PostalAddress'
                        ];

                        if ($location->MicroDataCountry) {
                            $address['addressCountry'] = $location->MicroDataCountry;
                        }
                        if ($location->MicroDataCity) {
                            $address['addressLocality'] = $location->MicroDataCity;
                        }
                        if ($location->MicroDataRegion) {
                            $address['addressRegion'] = $location->MicroDataRegion;
                        }
                        if ($location->MicroDataPostCode) {
                            $address['postalCode'] = $location->MicroDataPostCode;
                        }
                        if ($location->MicroDataPOBoxNumber) {
                            $address['postOfficeBoxNumber'] = $location->MicroDataPOBoxNumber;
                        }
                        if ($location->MicroDataStreetAddress) {
                            $address['streetAddress'] = $location->MicroDataStreetAddress;
                        }

                        $addresses[] = $address;
                    }
                }
            } else {

                if (isset($address)) {
                    $addresses = $address;
                }

                $subOrganisations = [];
                foreach($additionalLocations as $location) {

                    $organisation = [
                        '@type'     =>  $location->getMicroDataSchemaType(),
                        'parentOrganization'    =>  [
                            '@type' =>  $this->getOwner()->getMicroDataSchemaType(),
                            'name'  =>  $this->getOwner()->getSocialMetaValue('SiteName')
                        ]
                    ];

                    if ($location->MicroDataTitle) {
                        $organisation['name'] = $location->MicroDataTitle;
                    }

                    if (
                        $location->MicroDataStreetAddress
                        || $location->MicroDataPOBoxNumber
                        || $location->MicroDataCity
                        || $location->MicroDataPostCode
                    ) {
                        $address = [
                            '@type'     =>  'PostalAddress'
                        ];

                        if ($location->MicroDataCountry) {
                            $address['addressCountry'] = $location->MicroDataCountry;
                        }
                        if ($location->MicroDataCity) {
                            $address['addressLocality'] = $location->MicroDataCity;
                        }
                        if ($location->MicroDataRegion) {
                            $address['addressRegion'] = $location->MicroDataRegion;
                        }
                        if ($location->MicroDataPostCode) {
                            $address['postalCode'] = $location->MicroDataPostCode;
                        }
                        if ($location->MicroDataPOBoxNumber) {
                            $address['postOfficeBoxNumber'] = $location->MicroDataPOBoxNumber;
                        }
                        if ($location->MicroDataStreetAddress) {
                            $address['streetAddress'] = $location->MicroDataStreetAddress;
                        }

                        $organisation['address'] = $address;
                    }

                    if ($location->MicroDataPhone) {
                        $organisation['telephone'] = $location->MicroDataPhone;
                    }
                    if ($location->MicroDataFax) {
                        $organisation['faxNumber'] = $location->MicroDataFax;
                    }
                    if ($location->MicroDataEmail) {
                        $organisation['email'] = $location->MicroDataEmail;
                    }

                    if ($location->getMicroDataSchemaType(true) === 'LocalBusiness' && $location->getMicroDataMapLink()) {
                        $organisation['hasMap'] = $location->getMicroDataMapLink();
                    }

                    if ($location->IsMicroDataCoordinatesEnabled && $location->MicroDataLocationLatitude && $location->MicroDataLocationLongitude) {
                        $coordinates = array(
                            '@type'     =>  'GeoCoordinates',
                            'latitude'  =>  $location->MicroDataLocationLatitude,
                            'longitude' =>  $location->MicroDataLocationLongitude,
                        );
                        $organisation['geo'] = $coordinates;
                    }

                    $openingHours = $location->MicroDataOpeningHours();
                    if ($openingHours && $openingHours->exists()) {
                        $hours = [];
                        foreach ($openingHours as $hour) {
                            if (($days = json_decode($hour->Days)) && count($days)) {
                                $dayIdentifiers = [
                                    'Mo' => 'http://schema.org/Monday',
                                    'Tu' => 'http://schema.org/Tuesday',
                                    'We' => 'http://schema.org/Wednesday',
                                    'Th' => 'http://schema.org/Thursday',
                                    'Fr' => 'http://schema.org/Friday',
                                    'Sa' => 'http://schema.org/Saturday',
                                    'So' => 'http://schema.org/Sunday',
                                ];
                                foreach ($days as $day) {
                                    $row = [];
                                    $row['@type'] = 'OpeningHoursSpecification';
                                    $row['DayOfWeek'] = $dayIdentifiers[$day];
                                    if ($hour->TimeOpen) {
                                        $row['opens'] = $hour->TimeOpen;
                                    }
                                    if ($hour->TimeClose) {
                                        $row['closes'] = $hour->TimeClose;
                                    }
                                    $hours[] = $row;
                                }
                            }
                        }
                        if (count($hours)) {
                            $organisation['openingHoursSpecification'] = $hours;
                        }
                    }
                    if ($location->MicroDataPaymentAccepted) {
                        $organisation['paymentAccepted'] = json_decode($location->MicroDataPaymentAccepted);
                    }

                    $subOrganisations[] = $organisation;
                }

                if (count($subOrganisations)) {
                    $data['subOrganization'] = $subOrganisations;
                }
            }

        } else {
            if (isset($address)) {
                $addresses[] = $address;
            }
        }

        if ($this->getOwner()->IsMicroDataCoordinatesEnabled && $this->getOwner()->MicroDataLocationLongitude && $this->getOwner()->MicroDataLocationLatitude) {
            $coordinates = array(
                '@type'     =>  'GeoCoordinates',
                'latitude'  =>  $this->getOwner()->MicroDataLocationLatitude,
                'longitude' =>  $this->getOwner()->MicroDataLocationLongitude,
            );
        }

        if ($this->getOwner()->getMicroDataSchemaType(true) === 'Event') {

            if ($this->getOwner()->MicroDataEventStart) {
                $data['startDate'] = $this->getOwner()->dbObject('MicroDataEventStart')->Rfc3339();
            }
            if ($this->getOwner()->MicroDataEventEnd) {
                $data['endDate'] = $this->getOwner()->dbObject('MicroDataEventEnd')->Rfc3339();
            }

            $location = array(
                '@type' =>  'Place'
            );
            if ($this->getOwner()->MicroDataEventLocationName) {
                $location['name'] = $this->getOwner()->MicroDataEventLocationName;
            }
            if ($this->getOwner()->MicroDataEventLocationWebsite) {
                $location['sameAs'] = $this->getOwner()->MicroDataEventLocationWebsite;
            }

            if (isset($addresses) && count($addresses)) {
                $location['address'] = $addresses;
            }

            if ($this->getOwner()->MicroDataPhone) {
                $location['telephone'] = $this->getOwner()->MicroDataPhone;
            }
            if ($this->getOwner()->MicroDataFax) {
                $location['faxNumber'] = $this->getOwner()->MicroDataFax;
            }
            if ($this->getOwner()->MicroDataEmail) {
                $location['email'] = $this->getOwner()->MicroDataEmail;
            }

            if (isset($coordinates)) {
                $location['geo'] = $coordinates;
            }

            $data['location'] = $location;

        } else {

            if (isset($addresses) && count($addresses)) {
                $data['address'] = $addresses;
            }

            if ($this->getOwner()->MicroDataPhone) {
                $data['telephone'] = $this->getOwner()->MicroDataPhone;
            }
            if ($this->getOwner()->MicroDataFax) {
                $data['faxNumber'] = $this->getOwner()->MicroDataFax;
            }
            if ($this->getOwner()->MicroDataEmail) {
                $data['email'] = $this->getOwner()->MicroDataEmail;
            }

            if (isset($coordinates)) {
                $data['geo'] = $coordinates;
            }
        }

        if ($this->getOwner()->getMicroDataSchemaType(true) === 'LocalBusiness') {

            if ($this->getOwner()->getMicroDataMapLink()) {
                $data['hasMap'] = $this->getOwner()->getMicroDataMapLink();
            }
        }

        $openingHours = $this->getOwner()->MicroDataOpeningHours();
        if ($openingHours && $openingHours->exists()) {
            $hours = [];
            foreach ($openingHours as $hour) {
                if (($days = json_decode($hour->Days)) && count($days)) {
                    $dayIdentifiers = [
                        'Mo' => 'http://schema.org/Monday',
                        'Tu' => 'http://schema.org/Tuesday',
                        'We' => 'http://schema.org/Wednesday',
                        'Th' => 'http://schema.org/Thursday',
                        'Fr' => 'http://schema.org/Friday',
                        'Sa' => 'http://schema.org/Saturday',
                        'So' => 'http://schema.org/Sunday',
                    ];
                    foreach ($days as $day) {
                        $row = [];
                        $row['@type'] = 'OpeningHoursSpecification';
                        $row['DayOfWeek'] = $dayIdentifiers[$day];
                        if ($hour->TimeOpen) {
                            $row['opens'] = $hour->TimeOpen;
                        }
                        if ($hour->TimeClose) {
                            $row['closes'] = $hour->TimeClose;
                        }
                        $hours[] = $row;
                    }
                }
            }
            if (count($hours)) {
                $data['openingHoursSpecification'] = $hours;
            }
        }
        if ($this->getOwner()->MicroDataPaymentAccepted) {
            $data['paymentAccepted'] = json_decode($this->getOwner()->MicroDataPaymentAccepted);
        }

        $sameAsLinksField = $this->getOwner()->obj('SocialMetaSameAsLinks');
        $sameAsLinks = $sameAsLinksField->getValues();
        if ($sameAsLinks && count($sameAsLinks) > 0) {
            $sameAs = [];
            foreach ($sameAsLinks as $sameAsLink) {
                $sameAs[] = $sameAsLink;
            }
            $data['sameAs'] = $sameAs;
        }

        $this->getOwner()->invokeWithExtensions('updateSchemaData', $data);

        return $data;
    }

    public function getMicroDataSchemaType($baseTypeOnly = false)
    {
        return ($this->getOwner()->MicroDataTypeSpecific && !$baseTypeOnly)
            ? $this->getOwner()->MicroDataTypeSpecific
            : $this->getOwner()->MicroDataType;
    }

    public function getMicroDataMapLink()
    {
        if ($this->getOwner()->MicroDataStreetAddress) {
            $address = [];
            $address[] = $this->getOwner()->MicroDataStreetAddress . ',';
            if ($this->getOwner()->MicroDataCity) {
                $address[] = $this->getOwner()->MicroDataCity;
            }
            if ($this->getOwner()->MicroDataRegion) {
                $address[] = $this->getOwner()->MicroDataRegion;
            }
            if ($this->getOwner()->MicroDataPostCode) {
                $address[] = $this->getOwner()->MicroDataPostCode;
            }
            $address = implode(' ', $address);
            return 'https://www.google.com.au/maps/place/' . urlencode($address);
        }
        return null;
    }

    public function getMicroDataMapsAPIKey($includeLocal = true)
    {
        $localAPIKey = $this->getOwner()->MicroDataGoogleMapsAPIKey;
        if ($includeLocal && $localAPIKey) {
            return $localAPIKey;
        }

        if ($this->getOwner()->hasMethod('getGoogleMapsAPIKey')) {
            $globalMapsAPIKey = $this->getOwner()->getGoogleMapsAPIKey();
            if ($globalMapsAPIKey) {
                return $globalMapsAPIKey;
            }
        }

        // Google Map Field Module
        $googleMapsFieldDefaultOptions = Config::inst()->get(GoogleMapField::class, 'default_options');
        if ($googleMapsFieldDefaultOptions && isset($googleMapsFieldDefaultOptions['api_key'])) {
            return $googleMapsFieldDefaultOptions['api_key'];
        }

        return null;
    }

    public function getSocialMetaValue($value)
    {
        if ($this->getOwner()->hasMethod('getSocialMeta' . $value)) {
            return $this->getOwner()->{'getSocialMeta' . $value}();
        }

        if ($this->getOwner()->hasMethod('getDefaultSocialMeta' . $value)) {
            return $this->getOwner()->{'getDefaultSocialMeta' . $value}();
        }

        return null;
    }

    public function getDefaultSocialMetaSiteName()
    {
        if ($this->getOwner()->SocialMetaSiteName) {
            return $this->getOwner()->SocialMetaSiteName;
        }

        if ($this->getOwner()->hasMethod('getTitle')) {
            return $this->getOwner()->getTitle();
        }

        if ($this->getOwner()->Title) {
            return $this->getOwner()->Title;
        }

        return null;
    }

    public function getDefaultSocialMetaSiteDescription()
    {
        return $this->getOwner()->SocialMetaSiteDescription;
    }

    public function getDefaultSocialMetaSiteURL()
    {
        return Director::absoluteBaseURL();
    }

    public function getDefaultSocialMetaSiteLogo()
    {
        return $this->getOwner()->MicroDataSiteLogo();
    }

    public function getDefaultSocialMetaSiteImage()
    {
        return $this->getOwner()->SocialMetaSiteImage();
    }

    public function getDefaultSocialMetaFacebookAppID()
    {
        return $this->getOwner()->SocialMetaFacebookAppID;
    }

    public function getDefaultSocialMetaFacebookAdminIDs()
    {
        $adminIDsField = $this->getOwner()->obj('SocialMetaFacebookAdminIDs');
        return $adminIDsField->getValues();
    }

    public function getDefaultSocialMetaFacebookPage()
    {
        return $this->getOwner()->SocialMetaFacebookPage;
    }

    public function getDefaultSocialMetaTwitterAccount()
    {
        return $this->getOwner()->SocialMetaTwitterAccount;
    }

    public function getDefaultSocialMetaSchemaSiteID()
    {
        return Controller::join_links(
            $this->getOwner()->getSocialMetaValue('SiteURL'),
            '#schema-site'
        );
    }

    public function updateCMSFields(FieldList $fields)
    {
        $fields->addFieldsToTab(
            $this->getOwner()->getSocialMetaTabName('Defaults'),
            [
                $titleField = TextField::create('SocialMetaSiteName', _t("SocialMetaConfigExtension.SiteName", 'Site Name')),
                $descriptionField = TextareaField::create('SocialMetaSiteDescription', _t("SocialMetaConfigExtension.SiteDescription", 'Site Description')),
                UploadField::create(
                    'SocialMetaSiteImage',
                    _t("SocialMetaConfigExtension.SiteImage", 'Default Image')
                )
                    ->setFolderName('Meta')
                    ->setAllowedFileCategories('image'),
            ]
        );

        if (!$this->getOwner()->SocialMetaSiteName) {
            $titleField->setAttribute('placeholder', $this->getOwner()->getSocialMetaValue('SiteName'));
        }

        if (!$this->getOwner()->SocialMetaSiteDescription) {
            $descriptionField->setAttribute('placeholder', $this->getOwner()->getSocialMetaValue('SiteDescription'));
        }

        $fields->addFieldsToTab(
            $this->getOwner()->getSocialMetaTabName('Social'),
            [
                HeaderField::create(
                    'SocialMetaFacebookHeader',
                    _t("SocialMetaConfigExtension.FACEBOOK", 'Facebook'),
                    2
                ),
                TextField::create(
                    'SocialMetaFacebookAppID',
                    _t("SocialMetaConfigExtension.FACEBOOKAPPID", 'Facebook App ID')
                ),
                MultiValueTextField::create(
                    'SocialMetaFacebookAdminIDs',
                    _t('SocialMetaConfigExtension.FACEBOOKADMINIDS','Facebook Admin IDs')
                ),
                ExternalURLField::create(
                    'SocialMetaFacebookPage',
                    _t("SocialMetaConfigExtension.FACEBOOKPAGE", 'Facebook Page (full URL)')
                ),
                HeaderField::create(
                    'SocialMetaTwitterHeader',
                    _t("SocialMetaConfigExtension.X", 'X (Twitter)'),
                    2
                ),
                TextField::create(
                    'SocialMetaTwitterAccount',
                    _t("SocialMetaConfigExtension.XHANDLE", 'X (Twitter) Handle (@xyz)')
                )
            ]
        );

        $typeSpecificSource = function($type) {
            if ($type === 'Organization') {
                $key = 'organization_types';
            } else if ($type === 'LocalBusiness') {
                $key = 'localbusiness_types';
            } else if ($type === 'Event') {
                $key = 'event_types';
            } else {
                return [];
            }
            return Config::inst()->get(ConfigExtension::class, $key);
        };

        $fields->addFieldsToTab(
            $this->getOwner()->getSocialMetaTabName('MicroData.Main'),
            [
                $typeField = DropdownField::create(
                    'MicroDataType',
                    'Type',
                    [
                        'Organization'  =>  _t("SocialMetaConfigExtension.Organisation", 'Organisation'),
                        'LocalBusiness' =>  _t("SocialMetaConfigExtension.LocalBusiness", 'Local Business'),
                        'Event'         =>  _t("SocialMetaConfigExtension.Event", 'Event'),
                    ]
                ),
                DependentDropdownField::create(
                    'MicroDataTypeSpecific',
                    _t("SocialMetaConfigExtension.MoreSpecificType", 'More specific type'),
                    $typeSpecificSource
                )
                    ->setDepends($typeField)
                    ->setEmptyString(_t("SocialMetaConfigExtension.Select", '- select -')),
                $logoField = UploadField::create(
                    'MicroDataSiteLogo',
                    _t("SocialMetaConfigExtension.Logo", 'Logo')
                )
                    ->setAllowedFileCategories('image'),
                $microDataEventField = Wrapper::create(
                    TextField::create('MicroDataEventLocationName', _t("SocialMetaConfigExtension.EventLocationName", 'Event Location Name')),
                    ExternalURLField::create('MicroDataEventLocationWebsite', _t("SocialMetaConfigExtension.EventLocationWebsite", 'Event Location Website')),
                    DatetimeField::create('MicroDataEventStart', _t("SocialMetaConfigExtension.EventStart", 'Event Start')),
                    DatetimeField::create('MicroDataEventEnd', _t("SocialMetaConfigExtension.EventEnd", 'Event End'))
                ),
                TextField::create('MicroDataStreetAddress', _t("SocialMetaConfigExtension.StreetAddress", 'Street Address')),
                TextField::create('MicroDataPOBoxNumber', _t("SocialMetaConfigExtension.POBoxNumber", 'PO Box Number')),
                TextField::create('MicroDataCity', _t("SocialMetaConfigExtension.City", 'City')),
                TextField::create('MicroDataPostCode', _t("SocialMetaConfigExtension.PostCode", 'Post Code')),
                TextField::create('MicroDataRegion', _t("SocialMetaConfigExtension.StateRegion", 'State/Region')),
                TextField::create('MicroDataCountry', _t("SocialMetaConfigExtension.Country", 'Country')),
                InternationalPhoneNumberField::create('MicroDataPhone', _t("SocialMetaConfigExtension.Phone", 'Phone')),
                InternationalPhoneNumberField::create('MicroDataFax', _t("SocialMetaConfigExtension.Fax", 'Fax')),
                $microDataEmailField = Wrapper::create(
                    EmailField::create('MicroDataEmail', _t("SocialMetaConfigExtension.Email", 'Email'))
                ),
                $microDataPaymentAcceptedField = Wrapper::create(
                    CheckboxSetField::create(
                        'MicroDataPaymentAccepted',
                        _t("SocialMetaConfigExtension.PaymentAccepted", 'Payment Accepted'),
                        [
                            'cash'          =>  _t("SocialMetaConfigExtension.Cash", 'Cash'),
                            'cheque'        =>  _t("SocialMetaConfigExtension.Cheque", 'Cheque'),
                            'credit card'   =>  _t("SocialMetaConfigExtension.CreaditCard", 'Credit Card'),
                            'eftpos'        =>  _t("SocialMetaConfigExtension.EFTPos", 'EFTPos'),
                            'invoice'       =>  _t("SocialMetaConfigExtension.Invoice", 'Invoice'),
                            'paypal'        =>  _t("SocialMetaConfigExtension.PayPal", 'PayPal')
                        ]
                    )
                )
            ]
        );

        $logoFolder = Config::inst()->get(ConfigExtension::class, 'socialmeta_images_folder');
        if ($logoFolder) {
            $logoField->setFolderName($logoFolder);
        }

        $microDataEventField
            ->displayIf('MicroDataType')->isEqualTo('Event');

        $microDataEmailField
            ->displayIf('MicroDataType')->isEqualTo('Organization')
            ->orIf('MicroDataType')->isEqualTo('LocalBusiness');

        $microDataPaymentAcceptedField
            ->displayIf('MicroDataType')->isEqualTo('LocalBusiness');

        $fields->addFieldsToTab(
            $this->getOwner()->getSocialMetaTabName('MicroData.MapCoordinates'),
            [
                FieldGroup::create(
                    CheckboxField::create('IsMicroDataCoordinatesEnabled', _t("SocialMetaConfigExtension.EnableLocationCoordinates", 'Enable Location Coordinates'))
                )
                    ->setTitle(_t("SocialMetaConfigExtension.LocationCoordinates", 'Location Coordinates'))
                    ->setName('EnableLocationCoordinatesGroup')
            ]
        );

        $mapsAPIKey = $this->getOwner()->getMicroDataMapsAPIKey(false);

        if (!$mapsAPIKey) {
            $fields->addFieldToTab(
                $this->getOwner()->getSocialMetaTabName('MicroData.MapCoordinates'),
                $mapsAPIKeyField = Wrapper::create(
                    TextField::create(
                        'MicroDataGoogleMapsAPIKey',
                        _t("SocialMetaConfigExtension.GoogleMapsAPIKey", 'Google Maps API key')
                    )
                )
            );

            $mapsAPIKeyField
                ->displayIf('IsMicroDataCoordinatesEnabled')->isChecked();
        }

        $mapsAPIKey = $this->getOwner()->getMicroDataMapsAPIKey();

        if ($mapsAPIKey) {

            $fields->addFieldToTab(
                $this->getOwner()->getSocialMetaTabName('MicroData.MapCoordinates'),
                $mapField = Wrapper::create(
                    GoogleMapField::create(
                        $this->getOwner(),
                        _t("SocialMetaConfigExtension.Coordinates", 'Coordinates'),
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
                $this->getOwner()->getSocialMetaTabName('MicroData.MapCoordinates'),
                $mapField = Wrapper::create(
                    LiteralField::create(
                        'CoordinatesInfo',
                        '<p>'._t('SocialMetaBusinessLocation.AddGoogleMapsAPIKey', 'Please add a Google Maps API key in order to enable coordinates.').'</p>'
                    )
                )
            );
        }

        $mapField->displayIf('IsMicroDataCoordinatesEnabled')->isChecked();

        $fields->addFieldsToTab(
            $this->getOwner()->getSocialMetaTabName('MicroData.OpeningHours'),
            [
                LiteralField::create(
                    'OpeningHoursInfoField',
                    '<p>' . _t("SocialMetaConfigExtension.OpeningHoursOnlyApplicableLocalBusiness", 'Opening hours are only applicable to locations of type <em>Local Business</em>') . '</p><br><br>'
                ),
                GridField::create(
                    'MicroDataOpeningHours',
                    _t("SocialMetaConfigExtension.OpeningHours", 'Opening Hours'),
                    $this->getOwner()->MicroDataOpeningHours(),
                    GridFieldConfig_RecordEditor::create()
                )
            ]
        );

        $fields->addFieldsToTab(
            $this->getOwner()->getSocialMetaTabName('MicroData.Locations'),
            [
                FieldGroup::create(
                    CheckboxField::create(
                        'HasMicroDataAdditionalLocations',
                        _t("SocialMetaConfigExtension.OrganisationHasLocations", 'This organisation/business has additional locations')
                    )
                )->setTitle('Extra Locations'),
                $additionalLocationsFields = Wrapper::create(
                    FieldGroup::create(
                        CheckboxField::create(
                            'IsMicroDataAdditionalLocationsSeparateEntities',
                            _t("SocialMetaConfigExtension.LocationsAreSeparate", 'The additional locations are separate businesses/departments (e.g. they have separate contact numbers, opening hours, etc.)')
                        )
                    )
                        ->setTitle(_t("SocialMetaConfigExtension.BusinessStructure", 'Business structure'))
                        ->addExtraClass(''),
                    GridField::create(
                        'MicroDataAdditionalLocations',
                        _t("SocialMetaConfigExtension.AdditionalLocations", 'Additional Locations'),
                        $this->getOwner()->MicroDataAdditionalLocations(),
                        GridFieldConfig_RecordEditor::create()
                    )
                )
            ]
        );

        $additionalLocationsFields
            ->displayIf('HasMicroDataAdditionalLocations')->isChecked();

        $fields->addFieldsToTab(
            $this->getOwner()->getSocialMetaTabName('MicroData.SameAs'),
            [
                LiteralField::create(
                    'SocialMetaSameAsLinksInfoField',
                    '<p>' . _t("SocialMetaConfigExtension.AddLinksSameAs", 'Add links to be used as <em>SameAs</em> links in the schema data. For example your business profiles on social media, review sites or business registration.') . '</p>'
                ),
                MultiValueTextField::create(
                    'SocialMetaSameAsLinks',
                    _t('SocialMetaConfigExtension.SocialMetaSameAsLinks','Same As Links')
                ),
            ]
        );
    }

    public function updateSiteCMSFields(FieldList $fields)
    {
        $this->updateCMSFields($fields);
    }

    public function getSocialMetaTabName($tabPath = null)
    {
        $tabName = $this->getOwner()->config()->get('socialmeta_root_tab_name');
        if ($tabPath) {
            $tabName .= '.' . $tabPath;
        }
        return $tabName;
    }

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();

        if ($this->getOwner()->MicroDataType === 'Organization') {

            $this->getOwner()->MicroDataPaymentAccepted = '';
            $this->getOwner()->MicroDataEventLocationName = '';
            $this->getOwner()->MicroDataEventLocationWebsite = '';
            $this->getOwner()->MicroDataEventStart = '';
            $this->getOwner()->MicroDataEventEnd = '';

            $items = $this->getOwner()->MicroDataOpeningHours();
            if ($items && $items->Count() > 0) {
                foreach ($items as $item) {
                    $item->delete();
                }
            }

        } else if ($this->getOwner()->MicroDataType === 'LocalBusiness') {

            $this->getOwner()->MicroDataEventLocationName = '';
            $this->getOwner()->MicroDataEventLocationWebsite = '';
            $this->getOwner()->MicroDataEventStart = '';
            $this->getOwner()->MicroDataEventEnd = '';

        } else if ($this->getOwner()->MicroDataType === 'Event') {

            $this->getOwner()->MicroDataEmail = '';
            $this->getOwner()->MicroDataPaymentAccepted = '';

            $items = $this->getOwner()->MicroDataOpeningHours();
            if ($items && $items->Count() > 0) {
                foreach ($items as $item) {
                    $item->delete();
                }
            }
        }

        if (!$this->getOwner()->IsMicroDataCoordinatesEnabled) {
            $this->getOwner()->MicroDataLocationLongitude = '';
            $this->getOwner()->MicroDataLocationLatitude = '';
        }

        if (!$this->getOwner()->HasMicroDataAdditionalLocations || $this->getOwner()->MicroDataType === 'Event') {
            $items = $this->getOwner()->MicroDataAdditionalLocations();
            if ($items && $items->Count() > 0) {
                foreach ($items as $item) {
                    $item->delete();
                }
            }
        }
    }
}
