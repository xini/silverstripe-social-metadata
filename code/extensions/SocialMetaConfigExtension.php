<?php
class SocialMetaConfigExtension extends DataExtension {
 
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
        "MicroDataEventStart" => "SS_Datetime",
        "MicroDataEventEnd" => "SS_Datetime",
    );
    
    private static $has_one = array(
        'MicroDataLogo' => 'Image',
    );

    private static $has_many = array(
        'OpeningHours' => 'SocialMetaOpeningHours',
        'AdditionalLocations' => 'SocialMetaBusinessLocation',
    );
    
    public function updateCMSFields(FieldList $fields) {
        
        if (
            !class_exists('Multisites')
            || (Config::inst()->get('SocialMetaConfigExtension', 'multisites_enable_global_settings') && $this->owner instanceof SiteConfig)
            || (!Config::inst()->get('SocialMetaConfigExtension', 'multisites_enable_global_settings') && $this->owner instanceof Site)
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
                
            // micro data
            $fields->addFieldsToTab(
                "Root.SocialMetadata",
                array(
                    new HeaderField("microdataheader", _t("SocialMetaConfigExtension.MicroData", 'Micro Data'), 2),
                    
                    DropdownField::create(
                        "MicroDataType",
                        "Type",
                        array(
                            'Organization' => 'Organisation',
                            'LocalBusiness' => 'Local Business',
                            'Event' => 'Event',
                        )
                    ),
                    
                    UploadField::create("MicroDataLogo", _t("SocialMetaConfigExtension.Logo", 'Logo'))
                        ->setFolderName('social')
                        ->setAllowedExtensions(array('jpg', 'gif', 'png')),
                    
                    TextField::create('MicroDataEventLocationName', 'Event Location Name'),
                    TextField::create('MicroDataEventLocationWebsite', 'Event Location Website'),
                    DatetimeField::create('MicroDataEventStart', 'Event Start'),
                    DatetimeField::create('MicroDataEventEnd', 'Event End'),
                    
                    TextField::create('MicroDataStreetAddress', 'Street Address'),
                    TextField::create('MicroDataPOBoxNumber', 'PO Box Number'),
                    TextField::create('MicroDataCity', 'City'),
                    TextField::create('MicroDataPostCode', 'Post Code'),
                    TextField::create('MicroDataRegion', 'State/Region'),
                    TextField::create('MicroDataCountry', 'Country'),
                    TextField::create('MicroDataPhone', 'Phone'),
                    TextField::create('MicroDataFax', 'Fax'),
                    TextField::create('MicroDataEmail', 'Email'),
                    
                    FieldGroup::create(
                        CheckboxField::create("MicroDataEnableCoordinates", "")
                    )->setTitle("Enable Location Coordinates"),
                         
                    GridField::create(
                        'OpeningHours',
                        'Opening Hours',
                        $this->owner->OpeningHours(),
                        GridFieldConfig_RecordEditor::create()
                    ),
                    
                    CheckboxSetField::create(
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
                    
                    FieldGroup::create(
                        CheckboxField::create("MicroDataAdditionalLocations", "This organisation/business has additional locations"),
                        CheckboxField::create("MicroDataAdditionalLocationsSeparateEntities", "The additional locations are seperate businesses/departments<br />(e.g. they have separate contact numbers, opening hours, etc.)")
                    )->setTitle("Business structure")
                    ->addExtraClass(''),
                    
                    GridField::create(
                        'AdditionalLocations',
                        'Additional Locations',
                        $this->owner->AdditionalLocations(),
                        GridFieldConfig_RecordEditor::create()
                    ),

                )
            );
            
            // specific type
            $typesSpecific = Config::inst()->get('SocialMetaConfigExtension', 'organization_types');
            $typesSpecific += Config::inst()->get('SocialMetaConfigExtension', 'localbusiness_types');
            $typesSpecific += Config::inst()->get('SocialMetaConfigExtension', 'event_types');
            $typesSpecific = array('' => '- select -') + $typesSpecific;
            $typesSpecificParent = array_combine(
                array_keys(Config::inst()->get('SocialMetaConfigExtension', 'organization_types')), 
                array_fill(0, count(Config::inst()->get('SocialMetaConfigExtension', 'organization_types')), 'Organization')
            );
            $typesSpecificParent += array_combine(
                array_keys(Config::inst()->get('SocialMetaConfigExtension', 'localbusiness_types')), 
                array_fill(0, count(Config::inst()->get('SocialMetaConfigExtension', 'localbusiness_types')), 'LocalBusiness')
            );
            $typesSpecificParent += array_combine(
                array_keys(Config::inst()->get('SocialMetaConfigExtension', 'event_types')),
                array_fill(0, count(Config::inst()->get('SocialMetaConfigExtension', 'event_types')), 'Event')
            );
            $typesSpecificParent = array('' => 'All') + $typesSpecificParent;
             $fields->insertAfter(
                 'MicroDataType', 
                 DropdownAttributesField::create(
                     "MicroDataTypeSpecific",
                     "More specific type",
                     $typesSpecific
                )
                ->addExtraClass('dropdown')
                ->setOptionsAttributes('data-type', $typesSpecificParent)
            );
            
            // get maps api key from external sources
            $mapsApiKey = $this->owner->getGoogleMapsAPIKey(false);
            // add api key field if not already configured elsewhere
            if (!$mapsApiKey) {
                $fields->addFieldToTab(
                    "Root.SocialMetadata",
                    TextField::create('MicroDataGoogleMapsAPIKey', 'Google Maps API key'),
                    'OpeningHours'
                );
            }
            // get final api ke, including local one
            $mapsApiKey = $this->owner->getGoogleMapsAPIKey();
            
            // add map field
            if ($mapsApiKey) {
                $fields->addFieldToTab(
                    "Root.SocialMetadata",
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
                    ),
                    'OpeningHours'
                );
            } else {
                $fields->addFieldToTab(
                    "Root.SocialMetadata",
                    LiteralField::create(
                        'CoordinatesInfo',
                        '<p id="Form_EditForm_MicroDataCoordinatesInfo">'._t('SocialMetaConfigExtension.AddGoogleMapsAPIKey', 'Please add a Google Maps API key and save the config in order to set the coordinates.').'</p>'
                    ),
                    'OpeningHours'
                );
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
        } else if (Config::inst()->get('GoogleMapField', 'default_options.api_key')) {
            // default config google maps field
            $mapsApiKey = Config::inst()->get('GoogleMapField', 'default_options.api_key');
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
    
}