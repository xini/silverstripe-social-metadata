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
		if ($this->owner->MetaTitle) {
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
		if ($config && $config->exists() && $config->SharingTitle) {
			return $config->SharingTitle;
		} else if ($config && $config->exists() && $config->Title) {
			return $config->Title;
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
		} else if ($this->owner->many_many('Authors') && $authors = $this->owner->Authors()) {
			// blog module
			return $authors;
		} else {
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

	/**
	 * using data added by innoweb/silverstripe-social-profiles if available
	 */
	public function getSocialMetaProfilePages() {
	    $profiles = array();
	    $config = $this->getSocialMetaConfig();
	    if ($config && $config->exists()) {
    	    if ($config->FacebookPage) {
    	        $profiles[] = new ArrayData(array("URL" => $config->FacebookPage)); 
    	    }
    	    if ($config->TwitterPage) {
    	        $profiles[] = new ArrayData(array("URL" => $config->TwitterPage));
    	    }
    	    if ($config->GooglePage) {
    	        $profiles[] = new ArrayData(array("URL" => $config->GooglePage));
    	    }
    	    if ($config->LinkedinPage) {
    	        $profiles[] = new ArrayData(array("URL" => $config->LinkedinPage));
    	    }
    	    if ($config->PinterestPage) {
    	        $profiles[] = new ArrayData(array("URL" => $config->PinterestPage));
    	    }
    	    if ($config->InstagramPage) {
    	        $profiles[] = new ArrayData(array("URL" => $config->InstagramPage));
    	    }
    	    if ($config->YoutubePage) {
    	        $profiles[] = new ArrayData(array("URL" => $config->YoutubePage));
    	    }
	    }
	    return new ArrayList($profiles);
	}

}

