<?php
class SocialMetaOpeningHours extends DataObject {
    
    private static $singular_name = "Opening Hours";
    private static $plural_name = "Opening Hours";
    
    private static $db = array(
        'Days' => 'Varchar(255)',
        'TimeOpen' => "Enum(',00:00,01:00,02:00,03:00,04:00,05:00,06:00,07:00,08:00,09:00,10:00,11:00,12:00,13:00,14:00,15:00,16:00,17:00,18:00,19:00,20:00,21:00,22:00,23:00,24:00', '')",
        'TimeClose' => "Enum(',00:00,01:00,02:00,03:00,04:00,05:00,06:00,07:00,08:00,09:00,10:00,11:00,12:00,13:00,14:00,15:00,16:00,17:00,18:00,19:00,20:00,21:00,22:00,23:00,24:00', '')",
    );
    
    private static $has_one = array(
        'SiteConfig' => 'SiteConfig',
        'Site' => 'SiteTree',
        'Location' => 'SocialMetaBusinessLocation',
    );
    
    private static $searchable_fields = array(
    );
    
    private static $summary_fields = array(
        'Days' => 'Days',
        'Hours' => 'Hours',
    );
    
    private static $casting = array(
        'Hours' => 'Varchar(255)',
    );
    
    public function getCMSFields() {
    
        $fields = parent::getCMSFields();
        
        $fields->removeByName('SiteConfigID');
        $fields->removeByName('SiteID');
        $fields->removeByName('LocationID');
    
        $fields->addFieldsToTab(
            "Root.Main",
            array(
                CheckboxSetField::create(
                    "Days",
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
                    "TimeOpen",
                    "Opening Time",
                    singleton($this->ClassName)->dbObject('TimeOpen')->enumValues()
                ),
                DropdownField::create(
                    "TimeClose",
                    "Closing Time",
                    singleton($this->ClassName)->dbObject('TimeClose')->enumValues()
                ),
            )
        );
        
        return $fields;
         
    }
    
    public function getHours() {
        if ($this->TimeOpen && $this->TimeClose) {
            return $this->TimeOpen . '-' . $this->TimeClose;
        }
        return null;
    }
    
    public function getParent() {
        if ($this->SiteConfigID > 0) {
            return $this->SiteConfig();
        } else if ($this->SiteID > 0) {
            return $this->Site();
        } else if ($this->LocationID > 0) {
            return $this->Location();
        }
        return null;
    }

}