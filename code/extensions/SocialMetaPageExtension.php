<?php
class SocialMetaPageExtension extends SiteTreeExtension {
    
    public function getSocialMetaConfig() {
        if (class_exists('Multisites') && !Config::inst()->get('SocialMetaConfigExtension', 'multisites_enable_global_settings')) {
            return Multisites::inst()->getCurrentSite();
        } else {
            return SiteConfig::current_site_config();
        }
        return null;
    }
    
    public function getSocialMetaPageURL() {
        return preg_replace('/home\/$/i', '', $this->owner->AbsoluteLink());
    }
    
    public function getSocialMetaPageTitle() {
        $config = $this->getSocialMetaConfig();
        if ($this->owner->hasMethod('getTitleTag') && $this->owner->getTitleTag()) {
            return $this->owner->getTitleTag();
        } else if ($this->owner->MetaTitle) {
            return $this->owner->MetaTitle;
        } else if ($this->owner->Title) {
            return $this->owner->Title;
        } else if ($config && $config->exists() && $config->DefaultSharingTitle) {
            return $config->DefaultSharingTitle;
        } else if ($config && $config->exists() && $config->Title) {
            return $config->Title;
        }
        return null;
    }
    
    public function getSocialMetaSiteName() {
        $config = $this->getSocialMetaConfig();
        if ($config && $config->exists() && $config->DefaultSharingTitle) {
            return $config->DefaultSharingTitle;
        } else if ($config && $config->exists() && $config->Title) {
            return $config->Title;
        }
        return null;
    }
    
    public function getSocialMetaSiteDescription() {
        $config = $this->getSocialMetaConfig();
        if ($config && $config->exists() && $config->DefaultSharingDescription) {
            return $config->DefaultSharingDescription;
        } else if (($homelink = RootURLController::get_homepage_link()) && $home = SiteTree::get_by_link($homelink)) {
            return $home->getSocialMetaDescription();
        }
        return null;
    }
    
    public function getSocialMetaSiteURL() {
        return Director::absoluteBaseURL();
    }
    
    public function getSocialMetaDescription() {
        $config = $this->getSocialMetaConfig();
        if ($this->owner->MetaDescription) {
            return $this->owner->MetaDescription;
        } else if ($this->owner->Summary) {
            // blog module
            return $this->owner->Summary;
        } else if ($this->owner->hasMethod('Excerpt') && $this->owner->Excerpt()) {
            // blog module
            return $this->owner->Excerpt();
        } else if ($config && $config->exists() && $config->DefaultSharingDescription) {
            return $config->DefaultSharingDescription;
        }
        return null;
    }
    
    public function getSocialMetaFacebookType() {
        $configs = Config::inst()->get('SocialMetaPageExtension', 'facebook_page_type');
        $currentpage = Injector::inst()->get($this->owner->class);
        foreach ($configs as $classname => $type) {
            if ($currentpage instanceof $classname) {
                return $type;
            }
        }
        return "website";
    }
    
    public function getSocialMetaFacebookLocale() {
        return i18n::get_locale();
    }
    
    public function getSocialMetaFacebookAppID() {
        $config = $this->getSocialMetaConfig();
        if ($config && $config->exists()) {
           return $config->MetaFacebookAppId;
        }
        return null;
    }

    public function getSocialMetaFacebookAdmins() {
        $config = $this->getSocialMetaConfig();
        if ($config && $config->exists()) {
            return $config->MetaFacebookAdmins;
        }
        return null;
    }
    
    public function getSocialMetaTwitterHandle() {
        $config = $this->getSocialMetaConfig();
        if ($config && $config->exists()) {
           return $config->MetaTwitterHandle;
        }
        return null;
    }
    
    public function getSocialMetaImage() {
        $config = $this->getSocialMetaConfig();
        $image = null;
        if (!is_a($this->owner, "HomePage")) {
            if ($this->owner->has_one('FeaturedImage') && $this->owner->FeaturedImage()) {
                // blog module
                $image = $this->owner->FeaturedImage();
            } else if ($this->owner->many_many('ContentImages') && $this->owner->ContentImages()) {
                // e.g. news module
                $image = $this->owner->ContentImages()->First();
            } else if ($this->owner->has_one('Icon') && $this->owner->Icon()) {
                // e.g. events module
                $image = $this->owner->Icon();
            } else if ($this->owner->has_one('Keyvisual') && $this->owner->Keyvisual()) {
                // keyvisual module
                $image = $this->owner->Keyvisual();
            } else if ($this->owner->many_many('Keyvisuals') && $this->owner->Keyvisuals()) {
                // keyvisual module (slider)
                $image = $this->owner->Keyvisuals()->First();
            } else if ($this->owner->has_many('PageSlides') && ($slides = $this->owner->PageSlides()) && $slides->exists()) {
                // keyvisual module (slider)
                foreach ($slides as $slide) {
                    if ($slide && $slide->exists() && ($temp = $slide->Image()) && $temp->exists()) {
                        $image = $temp;
                        break;
                    }
                }
            } else {
                // extract first image in page content
                $htmlValue = Injector::inst()->create('HTMLValue', $this->owner->Content);
                if($images = $htmlValue->getElementsByTagName('img')) {
                    foreach($images as $img) {
                        $path = urldecode(Director::makeRelative($img->getAttribute('src')));
                        $path = preg_replace('/_resampled\/[a-z0-9]*\//i', '', $path);
                        if($tmp = File::find($path)) {
                            $image = $tmp;
                            break;
                        }
                    }
                }
            }
        }
        // fallback from innoweb/silverstripe-social-share
        if ((!$image || !$image->exists()) && $config && $config->exists() && $config->has_one('DefaultSharingImage')) {
            $image = $config->DefaultSharingImage();
        }
        return $image;
    }
    
    public function getSocialMetaLogo() {
        $image = null;
        $config = $this->getSocialMetaConfig();
        if ($config && $config->exists() && $config->has_one('MicroDataLogo')) {
            $image = $config->MicroDataLogo();
        }
        return $image;
    }
    
    public function getSocialMetaPublicationDate() {
        if ($this->owner->PublishDate) {
            // blog module
            return $this->owner->obj('PublishDate')->Rfc3339();
        } else if ($this->owner->Date) {
            // news module
            return $this->owner->obj('Date')->Rfc3339();
        } else {
            $version = $this->owner->allVersions()->filter("WasPublished", "1")->last();
            if ($version) {
                return $version->obj('Created')->Rfc3339();
            }
        }
        return null;
    }
    
    public function getSocialMetaModificationDate() {
        if ($this->owner->LastEdited) {
            return $this->owner->obj('LastEdited')->Rfc3339();
        }
        return null;
    }
    
    public function getSocialMetaAuthors() {
        if ($this->owner->hasMethod('getPageAuthors') && ($authors = $this->owner->getPageAuthors())) {
            // news module
            return $authors;
        } else if ($this->owner->hasMethod('getCredits') && ($authors = $this->owner->getCredits())) {
            // blog module
            $authors = $authors->column('Name');
            return implode(', ', $authors);
        } else {
            // standard page
            $version = $this->owner->allVersions()->last();
            if ($version) {
                return new ArrayList(array($version->Author()));
            }
        }
        return null;
    }

    public function getSocialMetaSection() {
        if ($this->owner->many_many('Categories') && ($categories = $this->owner->getManyManyComponents('Categories')) && $categories->exists()) {
            // blog module
            return $categories->First()->Title;
        } else if ($this->owner->has_one('EventCategory') && $this->owner->EventCategory()) {
            // events module
            return $this->owner->EventCategory()->Title;
        }
        return null;
    }

    public function getSocialMetaTags() {
        if ($this->owner->many_many('Tags') && $this->owner->Tags()) {
            // blog module
            return $this->owner->Tags();
        }
        return null;
    }
    
    public function getSocialMetaSchemaType() {
        $config = $this->getSocialMetaConfig();
        if ($config && $config->exists()) {
           return $config->MicroDataType;
        }
        return null;
    }
    
    public function getSocialMetaStreetAddress() {
        $config = $this->getSocialMetaConfig();
        if ($config && $config->exists()) {
           return $config->MicroDataStreetAddress;
        }
        return null;
    }
    
    public function getSocialMetaPOBoxNumber() {
        $config = $this->getSocialMetaConfig();
        if ($config && $config->exists()) {
           return $config->MicroDataPOBoxNumber;
        }
        return null;
    }
    
    public function getSocialMetaCity() {
        $config = $this->getSocialMetaConfig();
        if ($config && $config->exists()) {
           return $config->MicroDataCity;
        }
        return null;
    }
    
    public function getSocialMetaPostCode() {
        $config = $this->getSocialMetaConfig();
        if ($config && $config->exists()) {
           return $config->MicroDataPostCode;
        }
        return null;
    }
    
    public function getSocialMetaRegion() {
        $config = $this->getSocialMetaConfig();
        if ($config && $config->exists()) {
           return $config->MicroDataRegion;
        }
        return null;
    }
    
    public function getSocialMetaCountry() {
        $config = $this->getSocialMetaConfig();
        if ($config && $config->exists()) {
           return $config->MicroDataCountry;
        }
        return null;
    }
    
    public function getSocialMetaPhone() {
        $config = $this->getSocialMetaConfig();
        if ($config && $config->exists()) {
           return $config->MicroDataPhone;
        }
        return null;
    }
    
    public function getSocialMetaFax() {
        $config = $this->getSocialMetaConfig();
        if ($config && $config->exists()) {
           return $config->MicroDataFax;
        }
        return null;
    }
    
    public function getSocialMetaEmail() {
        $config = $this->getSocialMetaConfig();
        if ($config && $config->exists()) {
           return $config->MicroDataEmail;
        }
        return null;
    }
    
    public function getSocialMetaOpeningHoursDays() {
        $config = $this->getSocialMetaConfig();
        if ($config && $config->exists()) {
           return $config->MicroDataOpeningHoursDays;
        }
        return null;
    }
    
    public function getSocialMetaOpeningHoursTimeOpen() {
        $config = $this->getSocialMetaConfig();
        if ($config && $config->exists()) {
           return $config->MicroDataOpeningHoursTimeOpen;
        }
        return null;
    }

    public function getSocialMetaOpeningHoursTimeClose() {
        $config = $this->getSocialMetaConfig();
        if ($config && $config->exists()) {
           return $config->MicroDataOpeningHoursTimeClose;
        }
        return null;
    }
    
    public function getSocialMetaPaymentAccepted() {
        $config = $this->getSocialMetaConfig();
        if ($config && $config->exists()) {
           return $config->MicroDataPaymentAccepted;
        }
        return null;
    }
    
    public function getSocialMetaEnableCoordinates() {
        $config = $this->getSocialMetaConfig();
        if ($config && $config->exists()) {
           return $config->MicroDataEnableCoordinates;
        }
        return null;
    }
    
    public function getSocialMetaLocationLongitude() {
        $config = $this->getSocialMetaConfig();
        if ($config && $config->exists()) {
           return $config->MicroDataLocationLongitude;
        } 
        return null;
    }
    
    public function getSocialMetaLocationLatitude() {
        $config = $this->getSocialMetaConfig();
        if ($config && $config->exists()) {
           return $config->MicroDataLocationLatitude;
        }
        return null;
    }

    public function getSocialMetaEventLocationName() {
        $config = $this->getSocialMetaConfig();
        if ($config && $config->exists()) {
            return $config->MicroDataEventLocationName;
        }
        return null;
    }
    
    public function getSocialMetaEventLocationWebsite() {
        $config = $this->getSocialMetaConfig();
        if ($config && $config->exists()) {
            return $config->MicroDataEventLocationWebsite;
        }
        return null;
    }
    
    public function getSocialMetaEventStart() {
        $config = $this->getSocialMetaConfig();
        if ($config && $config->exists()) {
            return $config->dbObject('MicroDataEventStart');
        }
        return null;
    }
    
    public function getSocialMetaEventEnd() {
        $config = $this->getSocialMetaConfig();
        if ($config && $config->exists()) {
            return $config->dbObject('MicroDataEventEnd');
        }
        return null;
    }
    
    public function getSchemaData() {
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
        if (($logo = $this->getSocialMetaLogo()) && $logo->exists()) {
            $data["logo"] = array(
                "@type" => 'ImageObject',
                "url" => $logo->AbsoluteLink(),
	            "width" => $logo->getWidth().'px',
	            "height" => $logo->getHeight().'px',
            );
        }
        if ($this->owner->getSocialMetaSiteURL()) {
            $data["url"] = $this->owner->getSocialMetaSiteURL();
        }
        if ($this->owner->getSocialMetaProfilePages()) {
            $sameAs = array();
            foreach ($this->owner->getSocialMetaProfilePages() as $profile) {
                $sameAs[] = $profile->URL;
            }
            $data["sameAs"] = $sameAs;
        }
        
        // build address
        if ($this->owner->getSocialMetaStreetAddress() || $this->owner->getSocialMetaPOBoxNumber() || $this->owner->getSocialMetaCity() || $this->owner->getSocialMetaPostCode()) {
            $address = array(
                "@type" => "PostalAddress"
            );
            if ($this->owner->getSocialMetaCountry()) {
                $address["addressCountry"] = $this->owner->getSocialMetaCountry();
            }
            if ($this->owner->getSocialMetaCity()) {
                $address["addressLocality"] = $this->owner->getSocialMetaCity();
            }
            if ($this->owner->getSocialMetaRegion()) {
                $address["addressRegion"] = $this->owner->getSocialMetaRegion();
            }
            if ($this->owner->getSocialMetaPostCode()) {
                $address["postalCode"] = $this->owner->getSocialMetaPostCode();
            }
            if ($this->owner->getSocialMetaPOBoxNumber()) {
                $address["postOfficeBoxNumber"] = $this->owner->getSocialMetaPOBoxNumber();
            }
            if ($this->owner->getSocialMetaStreetAddress()) {
                $address["streetAddress"] = $this->owner->getSocialMetaStreetAddress();
            }
        }
        
        // build coordinates
        if ($this->owner->getSocialMetaEnableCoordinates() && $this->owner->getSocialMetaLocationLatitude() && $this->owner->getSocialMetaLocationLongitude()) {
            $coordinates = array(
                "@type" => "GeoCoordinates",
                "latitude" => $this->owner->getSocialMetaLocationLatitude(),
                "longitude" => $this->owner->getSocialMetaLocationLongitude(),
            );
        }
        
        // event data
        if ($this->owner->getSocialMetaSchemaType == "Event") {
            
            // add data to event and event location
            
            if ($this->owner->getSocialMetaEventStart()) {
                $data["startDate"] = $this->owner->getSocialMetaEventStart()->Rfc3339();
            }
            if ($this->owner->getSocialMetaEventEnd()) {
                $data["endDate"] = $this->owner->getSocialMetaEventEnd()->Rfc3339();
            }
            // event location
            $location = array(
                "@type" => "Place",
            );
            if ($this->owner->getSocialMetaEventLocationName()) {
                $location["name"] = $this->owner->getSocialMetaEventLocationName();
            }
            if ($this->owner->getSocialMetaEventLocationWebsite()) {
                $location["sameAs"] = $this->owner->getSocialMetaEventLocationWebsite();
            }
            // address
            if (isset($address)) {
                $location["address"] = $address;
            }
            // contact details
            if ($this->owner->getSocialMetaPhone()) {
                $location["telephone"] = $this->owner->getSocialMetaPhone();
            }
            if ($this->owner->getSocialMetaFax()) {
                $location["faxNumber"] = $this->owner->getSocialMetaFax();
            }
            if ($this->owner->getSocialMetaEmail()) {
                $location["email"] = $this->owner->getSocialMetaEmail();
            }
            // coordinates
            if (isset($coordinates)) {
                $location["geo"] = $coordinates;
            }
            
            $data["location"] = $location;
            
        } else {
            
            // add address and contact data to main data if not an event
            
            // address
            if (isset($address)) {
                $data["address"] = $address;
            }
            // contact details
            if ($this->owner->getSocialMetaPhone()) {
                $data["telephone"] = $this->owner->getSocialMetaPhone();
            }
            if ($this->owner->getSocialMetaFax()) {
                $data["faxNumber"] = $this->owner->getSocialMetaFax();
            }
            if ($this->owner->getSocialMetaEmail()) {
                $data["email"] = $this->owner->getSocialMetaEmail();
            }
            // coordinates
            if (isset($coordinates)) {
                $data["geo"] = $coordinates;
            }
            
        }
        
        // business properties
        if ($this->owner->getSocialMetaOpeningHoursDays()) {
            $data["openingHours"] = $this->owner->getSocialMetaOpeningHoursDays();
            if ($this->owner->getSocialMetaOpeningHoursTimeOpen() && $this->owner->getSocialMetaOpeningHoursTimeClose()) {
                $data["openingHours"] .= $this->owner->getSocialMetaOpeningHoursTimeOpen() . '-' . $this->owner->getSocialMetaOpeningHoursTimeClose();
            }
        }
        if ($this->owner->getSocialMetaPaymentAccepted()) {
            $data["paymentAccepted"] = $this->owner->getSocialMetaPaymentAccepted();
        }
        
        // return array of ld+json data in order to give sub pages the ability to add more ld+json blocks
        $dataSets = new ArrayList();
        $dataSets->push(new ArrayData(array(
            "DataSet" => json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        )));
        return $dataSets;
    }

    /**
     * using data added by innoweb/silverstripe-social-profiles if available
     */
    public function getSocialMetaProfilePages() {
        $profiles = array();
        $config = $this->getSocialMetaConfig();
        if ($config && $config->exists()) {
            if ($config->ProfilesFacebookPage) {
                $profiles[] = new ArrayData(array("URL" => $config->ProfilesFacebookPage)); 
            }
            if ($config->ProfilesTwitterPage) {
                $profiles[] = new ArrayData(array("URL" => $config->ProfilesTwitterPage));
            }
            if ($config->ProfilesGooglePage) {
                $profiles[] = new ArrayData(array("URL" => $config->ProfilesGooglePage));
            }
            if ($config->ProfilesLinkedinPage) {
                $profiles[] = new ArrayData(array("URL" => $config->ProfilesLinkedinPage));
            }
            if ($config->ProfilesPinterestPage) {
                $profiles[] = new ArrayData(array("URL" => $config->ProfilesPinterestPage));
            }
            if ($config->ProfilesInstagramPage) {
                $profiles[] = new ArrayData(array("URL" => $config->ProfilesInstagramPage));
            }
            if ($config->ProfilesYoutubePage) {
                $profiles[] = new ArrayData(array("URL" => $config->ProfilesYoutubePage));
            }
        }
        return new ArrayList($profiles);
    }

}

