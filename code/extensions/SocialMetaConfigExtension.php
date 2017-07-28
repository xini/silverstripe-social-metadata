<?php
class SocialMetaConfigExtension extends DataExtension {
 
	private static $db = array(
		'MetaFacebookPage' => 'Varchar(255)',
		'MetaFacebookAppId' => 'Varchar(255)',
		'MetaFacebookAdmins' => 'MultiValueField',
		'MetaTwitterHandle' => 'Varchar(50)',
		
	    'MicroDataType' => 'Varchar(255)',
	    'MicroDataStreetAddress' => 'Varchar(255)',
	    'MicroDataPOBoxNumber' => 'Varchar(255)',
	    'MicroDataCity' => 'Varchar(255)',
	    'MicroDataPostCode' => 'Varchar(255)',
	    'MicroDataRegion' => 'Varchar(255)',
	    'MicroDataCountry' => 'Varchar(255)',
	    'MicroDataPhone' => 'Varchar(255)',
	    'MicroDataFax' => 'Varchar(255)',
	    'MicroDataEmail' => 'Varchar(255)',
	    'MicroDataOpeningHoursDays' => 'Varchar(255)',
	    'MicroDataOpeningHoursTimeOpen' => "Enum(',00:00,01:00,02:00,03:00,04:00,05:00,06:00,07:00,08:00,09:00,10:00,11:00,12:00,13:00,14:00,15:00,16:00,17:00,18:00,19:00,20:00,21:00,22:00,23:00,24:00', '')",
	    'MicroDataOpeningHoursTimeClose' => "Enum(',00:00,01:00,02:00,03:00,04:00,05:00,06:00,07:00,08:00,09:00,10:00,11:00,12:00,13:00,14:00,15:00,16:00,17:00,18:00,19:00,20:00,21:00,22:00,23:00,24:00', '')",
	    'MicroDataPaymentAccepted' => 'Varchar(255)',
	    "MicroDataEnableCoordinates" => 'Boolean',
	    "MicroDataGoogleMapsAPIKey" => 'Varchar(255)',
	    "MicroDataLocationLongitude" => "Varchar",
	    "MicroDataLocationLatitude" => "Varchar",
	    "MicroDataEventLocationName" => "Varchar(255)",
	    "MicroDataEventLocationWebsite" => "Varchar(255)",
	    "MicroDataEventStart" => "SS_Datetime",
	    "MicroDataEventEnd" => "SS_Datetime",
	);
	
	private static $has_one = array(
	    'MicroDataLogo' => 'Image'
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
			        TextField::create('MicroDataEventLocationName', 'Event Location Name'),
			        TextField::create('MicroDataEventLocationWebsite', 'Event Location Website'),
			        TextField::create('MicroDataStreetAddress', 'Street Address'),
			        TextField::create('MicroDataPOBoxNumber', 'PO Box Number'),
			        TextField::create('MicroDataCity', 'City'),
			        TextField::create('MicroDataPostCode', 'Post Code'),
			        TextField::create('MicroDataRegion', 'State/Region'),
			        TextField::create('MicroDataCountry', 'Country'),
			        TextField::create('MicroDataPhone', 'Phone'),
			        TextField::create('MicroDataFax', 'Fax'),
			        TextField::create('MicroDataEmail', 'Email'),
			        DatetimeField::create('MicroDataEventStart', 'Event Start'),
			        DatetimeField::create('MicroDataEventEnd', 'Event End'),
			        UploadField::create("MicroDataLogo", _t("SocialMetaConfigExtension.Logo", 'Logo'))
    			        ->setFolderName('social')
    			        ->setAllowedExtensions(array('jpg', 'gif', 'png')),
			        CheckboxSetField::create(
			            "MicroDataOpeningHoursDays",
			            "Days Open",
			            array(
			                'Mo' => 'Monday',
			                'Tu' => 'Tuesday',
			                'We' => 'Wednesday',
			                'Th' => 'Thursday',
			                'Fr' => 'Friday',
			                'Sa' => 'Saturday',
			                'So' => 'Sunday',
			            )
		            ),
		            DropdownField::create(
		                "MicroDataOpeningHoursTimeOpen",
		                "Opening Time",
		                singleton($this->owner->ClassName)->dbObject('MicroDataOpeningHoursTimeOpen')->enumValues()
	                ),
	                DropdownField::create(
	                    "MicroDataOpeningHoursTimeClose",
	                    "Closing Time",
	                    singleton($this->owner->ClassName)->dbObject('MicroDataOpeningHoursTimeClose')->enumValues()
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
			        CheckboxField::create("MicroDataEnableCoordinates", "Enable Coordinates"),
                )
			);
			
			// check if API key already exists somewhere else
			if (($siteConfig = SiteConfig::current_site_config()) && $siteConfig->APIKey) {
			    // goggle maps module
			    $mapsApiKey = $siteConfig->APIKey;
			} else if (Config::inst()->get('GoogleMapField', 'default_options.api_key')) {
			    // default config google maps field
			    $mapsApiKey = Config::inst()->get('GoogleMapField', 'default_options.api_key');
			}
			// api key field if not already configured
			if (!isset($mapsApiKey)) {
			    $fields->addFieldToTab(
			        "Root.SocialMetadata",
			        TextField::create('MicroDataGoogleMapsAPIKey', 'Google Maps API key')
		        );
			}
			// check if configured here
			if (!isset($mapsApiKey)) {
			    $mapsApiKey = $this->owner->MicroDataGoogleMapsAPIKey;
			}
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
    			    )
    			);
			} else {
			    $fields->addFieldToTab(
			        "Root.SocialMetadata",
			        LiteralField::create(
			            'CoordinatesInfo',
			            '<p id="Form_EditForm_MicroDataCoordinatesInfo">'._t('SocialMetaConfigExtension.AddGoogleMapsAPIKey', 'Please add a Google Maps API key and save the config in order to set the coordinates.').'</p>'
		            )
		        );
			}
			
			// set tab titles
			$fields->fieldByName("Root.SocialMetadata")->setTitle(_t('SocialMetaConfigExtension.MetadataTab', 'Social Metadata'));
		
		}
	}
	
	public function updateSiteCMSFields(FieldList $fields) {
		$this->updateCMSFields($fields);
	}
	
	public function onBeforeWrite() {
		parent::onBeforeWrite();
		
		$this->owner->MicroDataEventLocationWebsite = $this->updateLinkURL($this->owner->MicroDataEventLocationWebsite);
		
		// clean up data
		if ($this->owner->MicroDataType == "Organization") {
		    $this->owner->MicroDataOpeningHoursDays = "";
		    $this->owner->MicroDataOpeningHoursTimeOpen = "";
		    $this->owner->MicroDataOpeningHoursTimeClose = "";
		    $this->owner->MicroDataPaymentAccepted = "";
		    $this->owner->MicroDataEventLocationName = "";
		    $this->owner->MicroDataEventLocationWebsite = "";
		    $this->owner->MicroDataEventStart = "";
		    $this->owner->MicroDataEventEnd = "";
		} else if ($this->owner->MicroDataType == "LocalBusiness") {
		    $this->owner->MicroDataEventLocationName = "";
		    $this->owner->MicroDataEventLocationWebsite = "";
		    $this->owner->MicroDataEventStart = "";
		    $this->owner->MicroDataEventEnd = "";
		} else if ($this->owner->MicroDataType == "Event") {
		    $this->owner->MicroDataEmail = "";
		    $this->owner->MicroDataOpeningHoursDays = "";
		    $this->owner->MicroDataOpeningHoursTimeOpen = "";
		    $this->owner->MicroDataOpeningHoursTimeClose = "";
		    $this->owner->MicroDataPaymentAccepted = "";
		}
		
		// remove coordinates
		if (!$this->owner->MicroDataEnableCoordinates) {
		    $this->owner->MicroDataLocationLongitude = "";
		    $this->owner->MicroDataLocationLatitude = "";
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