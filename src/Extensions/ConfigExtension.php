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
    private static $socialmeta_root_tab_name = 'Root.Metadata';

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
            '@type'             =>  $this->owner->getMicroDataSchemaType(),
            '@id'               =>  $this->owner->getSocialMetaValue('SchemaSiteID'),
            'mainEntityOfPage'  =>  $this->owner->getSocialMetaValue('SiteURL'),
        ];

        if ($this->owner->getSocialMetaValue('SiteName')) {
            $data['name'] = $this->owner->getSocialMetaValue('SiteName');
        }

        if ($this->owner->getSocialMetaValue('SiteDescription')) {
            $data['description'] = $this->owner->getSocialMetaValue('SiteDescription');
        }

        $logo = $this->owner->getSocialMetaValue('SiteLogo');
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

        if ($this->owner->getSocialMetaValue('SiteURL')) {
            $data['url'] = $this->owner->getSocialMetaValue('SiteURL');
        }

        $addresses = [];
        if (
            $this->owner->MicroDataStreetAddress
            || $this->owner->MicroDataPOBoxNumber
            || $this->owner->MicroDataCity
            || $this->owner->MicroDataPostCode
        ) {
            $address = [
                '@type'     =>  'PostalAddress'
            ];

            if ($this->owner->MicroDataCountry) {
                $address['addressCountry'] = $this->owner->MicroDataCountry;
            }
            if ($this->owner->MicroDataCity) {
                $address['addressLocality'] = $this->owner->MicroDataCity;
            }
            if ($this->owner->MicroDataRegion) {
                $address['addressRegion'] = $this->owner->MicroDataRegion;
            }
            if ($this->owner->MicroDataPostCode) {
                $address['postalCode'] = $this->owner->MicroDataPostCode;
            }
            if ($this->owner->MicroDataPOBoxNumber) {
                $address['postOfficeBoxNumber'] = $this->owner->MicroDataPOBoxNumber;
            }
            if ($this->owner->MicroDataStreetAddress) {
                $address['streetAddress'] = $this->owner->MicroDataStreetAddress;
            }
        }

        $additionalLocations = $this->owner->MicroDataAdditionalLocations();
        if ($this->owner->HasMicroDataAdditionalLocations && $additionalLocations) {

            if (!$this->owner->IsMicroDataAdditionalLocationsSeparateEntities) {

                if (isset($address) && $this->owner->getSocialMetaValue('SiteName')) {
                    $address['name'] = $this->owner->getSocialMetaValue('SiteName');
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
                            '@type' =>  $this->owner->getMicroDataSchemaType(),
                            'name'  =>  $this->owner->getSocialMetaValue('SiteName')
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

        if ($this->owner->IsMicroDataCoordinatesEnabled && $this->owner->MicroDataLocationLongitude && $this->owner->MicroDataLocationLatitude) {
            $coordinates = array(
                '@type'     =>  'GeoCoordinates',
                'latitude'  =>  $this->owner->MicroDataLocationLatitude,
                'longitude' =>  $this->owner->MicroDataLocationLongitude,
            );
        }

        if ($this->owner->getMicroDataSchemaType(true) === 'Event') {

            if ($this->owner->MicroDataEventStart) {
                $data['startDate'] = $this->owner->dbObject('MicroDataEventStart')->Rfc3339();
            }
            if ($this->owner->MicroDataEventEnd) {
                $data['endDate'] = $this->owner->dbObject('MicroDataEventEnd')->Rfc3339();
            }

            $location = array(
                '@type' =>  'Place'
            );
            if ($this->owner->MicroDataEventLocationName) {
                $location['name'] = $this->owner->MicroDataEventLocationName;
            }
            if ($this->owner->MicroDataEventLocationWebsite) {
                $location['sameAs'] = $this->owner->MicroDataEventLocationWebsite;
            }

            if (isset($addresses) && count($addresses)) {
                $location['address'] = $addresses;
            }

            if ($this->owner->MicroDataPhone) {
                $location['telephone'] = $this->owner->MicroDataPhone;
            }
            if ($this->owner->MicroDataFax) {
                $location['faxNumber'] = $this->owner->MicroDataFax;
            }
            if ($this->owner->MicroDataEmail) {
                $location['email'] = $this->owner->MicroDataEmail;
            }

            if (isset($coordinates)) {
                $location['geo'] = $coordinates;
            }

            $data['location'] = $location;

        } else {

            if (isset($addresses) && count($addresses)) {
                $data['address'] = $addresses;
            }

            if ($this->owner->MicroDataPhone) {
                $data['telephone'] = $this->owner->MicroDataPhone;
            }
            if ($this->owner->MicroDataFax) {
                $data['faxNumber'] = $this->owner->MicroDataFax;
            }
            if ($this->owner->MicroDataEmail) {
                $data['email'] = $this->owner->MicroDataEmail;
            }

            if (isset($coordinates)) {
                $data['geo'] = $coordinates;
            }
        }

        if ($this->owner->getMicroDataSchemaType(true) === 'LocalBusiness') {

            if ($this->owner->getMicroDataMapLink()) {
                $data['hasMap'] = $this->owner->getMicroDataMapLink();
            }
        }

        $openingHours = $this->owner->MicroDataOpeningHours();
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
        if ($this->owner->MicroDataPaymentAccepted) {
            $data['paymentAccepted'] = json_decode($this->owner->MicroDataPaymentAccepted);
        }

        $sameAsLinksField = $this->owner->obj('SocialMetaSameAsLinks');
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
        return ($this->owner->MicroDataTypeSpecific && !$baseTypeOnly)
            ? $this->owner->MicroDataTypeSpecific
            : $this->owner->MicroDataType;
    }

    public function getMicroDataMapLink()
    {
        if ($this->owner->MicroDataStreetAddress) {
            $address = [];
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
            return 'https://www.google.com.au/maps/place/' . urlencode($address);
        }
        return null;
    }

    public function getMicroDataMapsAPIKey($includeLocal = true)
    {
        $localAPIKey = $this->owner->MicroDataGoogleMapsAPIKey;
        if ($includeLocal && $localAPIKey) {
            return $localAPIKey;
        }

        if ($this->owner->hasMethod('getGoogleMapsAPIKey')) {
            $globalMapsAPIKey = $this->owner->getGoogleMapsAPIKey();
            if ($globalMapsAPIKey) {
                return $globalMapsAPIKey;
            }
        }

        // Google Map Field Module
        $googleMapsFieldModuleAPIKey = Config::inst()->get(GoogleMapField::class, 'default_options.api_key');
        if ($googleMapsFieldModuleAPIKey) {
            return $googleMapsFieldModuleAPIKey;
        }

        return null;
    }

    public function getSocialMetaValue($value)
    {
        if ($this->owner->hasMethod('getSocialMeta' . $value)) {
            return $this->owner->{'getSocialMeta' . $value}();
        }

        if ($this->owner->hasMethod('getDefaultSocialMeta' . $value)) {
            return $this->owner->{'getDefaultSocialMeta' . $value}();
        }

        return null;
    }

    public function getDefaultSocialMetaSiteName()
    {
        if ($this->owner->SocialMetaSiteName) {
            return $this->owner->SocialMetaSiteName;
        }

        if ($this->owner->hasMethod('getTitle')) {
            return $this->owner->getTitle();
        }

        if ($this->owner->Title) {
            return $this->owner->Title;
        }

        return null;
    }

    public function getDefaultSocialMetaSiteDescription()
    {
        return $this->owner->SocialMetaSiteDescription;
    }

    public function getDefaultSocialMetaSiteURL()
    {
        return Director::absoluteBaseURL();
    }

    public function getDefaultSocialMetaSiteLogo()
    {
        return $this->owner->MicroDataSiteLogo();
    }

    public function getDefaultSocialMetaSiteImage()
    {
        return $this->owner->SocialMetaSiteImage();
    }

    public function getDefaultSocialMetaFacebookAppID()
    {
        return $this->owner->SocialMetaFacebookAppID;
    }

    public function getDefaultSocialMetaFacebookAdminIDs()
    {
        $adminIDsField = $this->owner->obj('SocialMetaFacebookAdminIDs');
        return $adminIDsField->getValues();
    }

    public function getDefaultSocialMetaFacebookPage()
    {
        return $this->owner->SocialMetaFacebookPage;
    }

    public function getDefaultSocialMetaTwitterAccount()
    {
        return $this->owner->SocialMetaTwitterAccount;
    }

    public function getDefaultSocialMetaSchemaSiteID()
    {
        return Controller::join_links(
            $this->owner->getSocialMetaValue('SiteURL'),
            '#schema-site'
        );
    }

    public function updateCMSFields(FieldList $fields)
    {
        $fields->addFieldsToTab(
            $this->owner->getSocialMetaTabName('Defaults'),
            [
                $titleField = TextField::create('SocialMetaSiteName', 'Site Name'),
                $descriptionField = TextareaField::create('SocialMetaSiteDescription', 'Site Description'),
                UploadField::create(
                    'SocialMetaSiteImage',
                    _t("SocialMetaConfigExtension.SiteImage", 'Default Image')
                )
                    ->setFolderName('Meta')
                    ->setAllowedFileCategories('image'),
            ]
        );

        if (!$this->owner->SocialMetaSiteName) {
            $titleField->setAttribute('placeholder', $this->owner->getSocialMetaValue('SiteName'));
        }

        if (!$this->owner->SocialMetaSiteDescription) {
            $descriptionField->setAttribute('placeholder', $this->owner->getSocialMetaValue('SiteDescription'));
        }

        $fields->addFieldsToTab(
            $this->owner->getSocialMetaTabName('Social'),
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
                    _t("SocialMetaConfigExtension.TWITTER", 'Twitter'),
                    2
                ),
                TextField::create(
                    'SocialMetaTwitterAccount',
                    _t("SocialMetaConfigExtension.TWITTERHANDLE", 'Twitter Handle (@xyz)')
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
            $this->owner->getSocialMetaTabName('MicroData.Main'),
            [
                $typeField = DropdownField::create(
                    'MicroDataType',
                    'Type',
                    [
                        'Organization'  =>  'Organization',
                        'LocalBusiness' =>  'Local Business',
                        'Event'         =>  'Event'
                    ]
                ),
                DependentDropdownField::create(
                    'MicroDataTypeSpecific',
                    'More specific type',
                    $typeSpecificSource
                )
                    ->setDepends($typeField)
                    ->setEmptyString('- select -'),
                $logoField = UploadField::create(
                    'MicroDataSiteLogo',
                    _t("SocialMetaConfigExtension.Logo", 'Logo')
                )
                    ->setAllowedFileCategories('image'),
                $microDataEventField = Wrapper::create(
                    TextField::create('MicroDataEventLocationName', 'Event Location Name'),
                    ExternalURLField::create('MicroDataEventLocationWebsite', 'Event Location Website'),
                    DatetimeField::create('MicroDataEventStart', 'Event Start'),
                    DatetimeField::create('MicroDataEventEnd', 'Event End')
                ),
                TextField::create('MicroDataStreetAddress', 'Street Address'),
                TextField::create('MicroDataPOBoxNumber', 'PO Box Number'),
                TextField::create('MicroDataCity', 'City'),
                TextField::create('MicroDataPostCode', 'Post Code'),
                TextField::create('MicroDataRegion', 'State/Region'),
                TextField::create('MicroDataCountry', 'Country'),
                InternationalPhoneNumberField::create('MicroDataPhone', 'Phone'),
                InternationalPhoneNumberField::create('MicroDataFax', 'Fax'),
                $microDataEmailField = Wrapper::create(
                    EmailField::create('MicroDataEmail', 'Email')
                ),
                $microDataPaymentAcceptedField = Wrapper::create(
                    CheckboxSetField::create(
                        'MicroDataPaymentAccepted',
                        'Payment Accepted',
                        [
                            'cash'          =>  'Cash',
                            'cheque'        =>  'Cheque',
                            'credit card'   =>  'Credit Card',
                            'eftpos'        =>  'EFTPos',
                            'invoice'       =>  'Invoice',
                            'paypal'        =>  'PayPal'
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
            $this->owner->getSocialMetaTabName('MicroData.MapCoordinates'),
            [
                FieldGroup::create(
                    CheckboxField::create('IsMicroDataCoordinatesEnabled', 'Enable Location Coordinates')
                )
                    ->setTitle('Location Coordinates')
                    ->setName('EnableLocationCoordinatesGroup')
            ]
        );

        $mapsAPIKey = $this->owner->getMicroDataMapsAPIKey(false);

        if (!$mapsAPIKey) {
            $fields->addFieldToTab(
                $this->owner->getSocialMetaTabName('MicroData.MapCoordinates'),
                $mapsAPIKeyField = Wrapper::create(
                    TextField::create(
                        'MicroDataGoogleMapsAPIKey',
                        'Google Maps API key'
                    )
                )
            );

            $mapsAPIKeyField
                ->displayIf('IsMicroDataCoordinatesEnabled')->isChecked();
        }

        $mapsAPIKey = $this->owner->getMicroDataMapsAPIKey();

        if ($mapsAPIKey) {

            $fields->addFieldToTab(
                $this->owner->getSocialMetaTabName('MicroData.MapCoordinates'),
                $mapField = Wrapper::create(
                    GoogleMapField::create(
                        $this->owner,
                        'Coordinates',
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
                $this->owner->getSocialMetaTabName('MicroData.MapCoordinates'),
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
            $this->owner->getSocialMetaTabName('MicroData.OpeningHours'),
            [
                LiteralField::create(
                    'OpeningHoursInfoField',
                    '<p>Opening hours are only applicable to locations of type <em>Local Business</em></p><br><br>'
                ),
                GridField::create(
                    'MicroDataOpeningHours',
                    'Opening Hours',
                    $this->owner->MicroDataOpeningHours(),
                    GridFieldConfig_RecordEditor::create()
                )
            ]
        );

        $fields->addFieldsToTab(
            $this->owner->getSocialMetaTabName('MicroData.Locations'),
            [
                FieldGroup::create(
                    CheckboxField::create(
                        'HasMicroDataAdditionalLocations',
                        'This organisation/business has additional locations'
                    )
                )->setTitle('Extra Locations'),
                $additionalLocationsFields = Wrapper::create(
                    FieldGroup::create(
                        CheckboxField::create(
                            'IsMicroDataAdditionalLocationsSeparateEntities',
                            'The additional locations are separate businesses/departments (e.g. they have separate contact numbers, opening hours, etc.)'
                        )
                    )
                        ->setTitle('Business structure')
                        ->addExtraClass(''),
                    GridField::create(
                        'MicroDataAdditionalLocations',
                        'Additional Locations',
                        $this->owner->MicroDataAdditionalLocations(),
                        GridFieldConfig_RecordEditor::create()
                    )
                )
            ]
        );

        $additionalLocationsFields
            ->displayIf('HasMicroDataAdditionalLocations')->isChecked();

        $fields->addFieldsToTab(
            $this->owner->getSocialMetaTabName('MicroData.SameAs'),
            [
                LiteralField::create(
                    'SocialMetaSameAsLinksInfoField',
                    '<p>Add links to be used as <em>SameAs</em> links in the schema data. For example your business profiles on social media, review sites or business registration.</p>'
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
        $tabName = $this->owner->config()->get('socialmeta_root_tab_name');
        if ($tabPath) {
            $tabName .= '.' . $tabPath;
        }
        return $tabName;
    }

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();

        if ($this->owner->MicroDataType === 'Organization') {

            $this->owner->MicroDataPaymentAccepted = '';
            $this->owner->MicroDataEventLocationName = '';
            $this->owner->MicroDataEventLocationWebsite = '';
            $this->owner->MicroDataEventStart = '';
            $this->owner->MicroDataEventEnd = '';

            $items = $this->owner->MicroDataOpeningHours();
            if ($items && $items->Count() > 0) {
                foreach ($items as $item) {
                    $item->delete();
                }
            }

        } else if ($this->owner->MicroDataType === 'LocalBusiness') {

            $this->owner->MicroDataEventLocationName = '';
            $this->owner->MicroDataEventLocationWebsite = '';
            $this->owner->MicroDataEventStart = '';
            $this->owner->MicroDataEventEnd = '';

        } else if ($this->owner->MicroDataType === 'Event') {

            $this->owner->MicroDataEmail = '';
            $this->owner->MicroDataPaymentAccepted = '';

            $items = $this->owner->MicroDataOpeningHours();
            if ($items && $items->Count() > 0) {
                foreach ($items as $item) {
                    $item->delete();
                }
            }
        }

        if (!$this->owner->IsMicroDataCoordinatesEnabled) {
            $this->owner->MicroDataLocationLongitude = '';
            $this->owner->MicroDataLocationLatitude = '';
        }

        if (!$this->owner->HasMicroDataAdditionalLocations || $this->owner->MicroDataType === 'Event') {
            $items = $this->owner->MicroDataAdditionalLocations();
            if ($items && $items->Count() > 0) {
                foreach ($items as $item) {
                    $item->delete();
                }
            }
        }
    }
}
