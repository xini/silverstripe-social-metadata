<?php
class SocialMetaBusinessLocation extends DataObject {
    
    private static $singular_name = "Business Location";
    private static $plural_name = "Business Locations";
    
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
        'SiteConfig' => 'SiteConfig',
        'Site' => 'SiteTree',
    );
        
    private static $has_many = array(
        'OpeningHours' => 'SocialMetaOpeningHours',
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
            
            // main type
            $fields->addFieldToTab(
                "Root.Main",
                DropdownField::create(
                    "MicroDataType",
                    "Type",
                    array(
                        'Organization' => 'Organisation',
                        'LocalBusiness' => 'Local Business',
                    )
                )
            );
            
            // specific type
            $typesSpecific = Config::inst()->get('SocialMetaConfigExtension', 'organization_types');
            $typesSpecific += Config::inst()->get('SocialMetaConfigExtension', 'localbusiness_types');
            $typesSpecific = array('' => '- select -') + $typesSpecific;
            $typesSpecificParent = array_combine(
                array_keys(Config::inst()->get('SocialMetaConfigExtension', 'organization_types')),
                array_fill(0, count(Config::inst()->get('SocialMetaConfigExtension', 'organization_types')), 'Organization')
            );
            $typesSpecificParent += array_combine(
                array_keys(Config::inst()->get('SocialMetaConfigExtension', 'localbusiness_types')),
                array_fill(0, count(Config::inst()->get('SocialMetaConfigExtension', 'localbusiness_types')), 'LocalBusiness')
            );
            $typesSpecificParent = array('' => 'All') + $typesSpecificParent;
            $fields->addFieldToTab(
                "Root.Main",
                DropdownAttributesField::create(
                    "MicroDataTypeSpecific",
                    "More specific type",
                    $typesSpecific
                )
                ->addExtraClass('dropdown')
                ->setOptionsAttributes('data-type', $typesSpecificParent)
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
            
                    GridField::create(
                        'OpeningHours',
                        'Opening Hours',
                        $this->OpeningHours(),
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
                )
            );
            
            // get final api key, including local one
            $mapsApiKey = $this->getParentConfig()->getGoogleMapsAPIKey();
            // add map field
            if ($mapsApiKey) {
                $fields->addFieldsToTab(
                    "Root.Main",
                    array(
                        FieldGroup::create(
                            CheckboxField::create("MicroDataEnableCoordinates", "")
                        )->setTitle("Enable Location Coordinates"),
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
                        ),
                    ),
                    'OpeningHours'
                );
            } else {
                $fields->addFieldToTab(
                    "Root.Main",
                    LiteralField::create(
                        'CoordinatesInfo',
                        '<p id="Form_ItemEditForm_MicroDataCoordinatesInfo">'._t('SocialMetaBusinessLocation.AddGoogleMapsAPIKey', 'Please add a Google Maps API key to the main config in order to enable coordinates.').'</p>'
                    ),
                    'OpeningHours'
                );
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