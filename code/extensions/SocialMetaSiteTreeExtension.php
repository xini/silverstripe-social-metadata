<?php
class SocialMetaSiteTreeExtension extends SiteTreeExtension {
    
    public function MetaTags(&$tags) {
        // $tags .= "<meta name=\"name\" content=\"" . $content . "\" />\n";
        
        $config = $this->owner->getSocialMetaConfig();
        if ($config && $config->exists()) {
        
            // twitter
            if (($image = $this->owner->getSocialMetaImage()) && $image->getWidth() >= 280) {
                $tags .= "<meta name=\"twitter:card\" content=\"summary_large_image\" />\n";
            } else {
                $tags .= "<meta name=\"twitter:card\" content=\"summary\" />\n";
            }
            if ($content = $config->MetaTwitterHandle) {
                $tags .= "<meta name=\"twitter:site\" content=\"" . $content . "\" />\n";
            }
            if ($content = $this->owner->getSocialMetaPageTitle()) {
                $tags .= "<meta name=\"twitter:title\" content=\"" . $content . "\" />\n";
            }
            if ($content = $this->owner->getSocialMetaDescription()) {
                $tags .= "<meta name=\"twitter:description\" content=\"" . $content . "\" />\n";
            }
            
            // facebook / open graph
            if ($content = $config->MetaFacebookAppId) {
                $tags .= "<meta property=\"fb:app_id\" content=\"" . $content . "\" />\n";
            }
            $facebookType = $this->getSocialMetaFacebookType();
            if ($facebookType) {
                $tags .= "<meta property=\"og:type\" content=\"" . $facebookType . "\" />\n";
            }
            if ($content = $this->owner->getSocialMetaPageTitle()) {
                $tags .= "<meta property=\"og:title\" content=\"" . $content . "\" />\n";
            }
            if ($content = $this->owner->getSocialMetaPageURL()) {
                $tags .= "<meta property=\"og:url\" content=\"" . $content . "\" />\n";
            }
            if ($content = $this->owner->getSocialMetaFacebookLocale()) {
                $tags .= "<meta property=\"og:locale\" content=\"" . $content . "\" />\n";
            }
            if ($content = $this->owner->getSocialMetaDescription()) {
                $tags .= "<meta property=\"og:description\" content=\"" . $content . "\" />\n";
            }
            if ($content = $config->getSocialMetaSiteName()) {
                $tags .= "<meta property=\"og:site_name\" content=\"" . $content . "\" />\n";
            }
            if ($facebookType == 'article') {
                if ($content = $config->MetaFacebookPage) {
                    $tags .= "<meta property=\"article:publisher\" content=\"" . $content . "\" />\n";
                }
                if ($content = $this->owner->getSocialMetaPublicationDate()) {
                    $tags .= "<meta property=\"article:published_time\" content=\"" . $content . "\" />\n";
                }
                if ($content = $this->owner->getSocialMetaModificationDate()) {
                    $tags .= "<meta property=\"article:modified_time\" content=\"" . $content . "\" />\n";
                }
                if ($content = $this->owner->getSocialMetaModificationDate()) {
                    $tags .= "<meta property=\"article:modified_time\" content=\"" . $content . "\" />\n";
                }
                if (($sections = $this->owner->getSocialMetaSections()) && count($sections)) {
                    foreach ($sections as $section) {
                        $tags .= "<meta property=\"article:section\" content=\"" . $section . "\" />\n";
                    }
                }
                if (($articleTags = $this->owner->getSocialMetaTags()) && count($articleTags)) {
                    foreach ($articleTags as $tag) {
                        $tags .= "<meta property=\"article:tag\" content=\"" . $tag . "\" />\n";
                    }
                }
            }
            if ($admins = $config->MetaFacebookAdmins) {
                foreach ($admins as $admin) {
                    $tags .= "<meta property=\"fb:admins\" content=\"" . $admin . "\" />\n";
                }
            }
            
            // authors
            if ($authors = $this->owner->getSocialMetaAuthors()) {
                foreach ($authors as $author) {
                    if ($content = $author->GooglePlusProfile) {
                        $tags .= "<link rel=\"author\" href=\"" . $content . "\" />\n";
                    }
                    if ($content = $author->FacebookProfile) {
                        $tags .= "<meta property=\"article:author\" content=\"" . $content . "\" />\n";
                    }
                    if ($content = $author->TwitterHandle) {
                        $tags .= "<meta name=\"twitter:creator\" content=\"" . $content . "\" />\n";
                    }
                }
                $tags .= "<meta name=\"author\" content=\"" . implode(', ', $authors->column('Name')) . "\" />\n";
            }
            
            // images
            if ($image = $this->owner->getSocialMetaImage()) {
                // two versions of focuspoint module
                if ($image->hasMethod('FocusFill') && $cropped = $image->FocusFill(1200,630)) {
                    $tags .= "<meta name=\"twitter:image\" content=\"" . $cropped->AbsoluteLink() . "\" />\n";
                    $tags .= "<meta property=\"og:image\" content=\"" . $cropped->AbsoluteLink() . "\" />\n";
                } else if ($image->hasMethod('CroppedFocusedImage') && $cropped = $image->CroppedFocusedImage(1200,630)) {
                    $tags .= "<meta name=\"twitter:image\" content=\"" . $cropped->AbsoluteLink() . "\" />\n";
                    $tags .= "<meta property=\"og:image\" content=\"" . $cropped->AbsoluteLink() . "\" />\n";
                    
                // default SS fill
                } else if ($cropped = $image->Fill(1200,630)) {
                    $tags .= "<meta name=\"twitter:image\" content=\"" . $cropped->AbsoluteLink() . "\" />\n";
                    $tags .= "<meta property=\"og:image\" content=\"" . $cropped->AbsoluteLink() . "\" />\n";
                }
            }
            
            // schema data 
            if (($data = $config->getSchemaData()) && class_exists('Multisites') && trim($this->owner->Link(), "/") == MultisitesRootController::get_homepage_link()) {
                $tags .= "<script type=\"application/ld+json\">" . $data . "</script>\n";
            } else if (($data = $config->getSchemaData()) && (trim($this->owner->Link(), "/") == RootURLController::get_homepage_link() || trim($this->owner->Link(), "/") == '')) {
                $tags .= "<script type=\"application/ld+json\">" . $data . "</script>\n";
            } else if ($this->owner->hasMethod('getSchemaData') && ($data = $this->owner->getSchemaData())) {
                $tags .= "<script type=\"application/ld+json\">" . $data . "</script>\n";
            }
        }        
    }
    
    public function getSocialMetaConfig() {
        if (class_exists('Multisites') && !Config::inst()->get('SocialMetaConfigExtension', 'multisites_enable_global_settings')) {
            return Multisites::inst()->getCurrentSite();
        } else {
            return SiteConfig::current_site_config();
        }
        return null;
    }
    
    public function getSocialMetaPageURL() {
        if ($this->owner->hasMethod('getCanonicalURL')) {
            return $this->owner->getCanonicalURL();
        } else {
            return preg_replace('/home\/$/i', '', $this->owner->AbsoluteLink());
        }
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
    
    public function getSocialMetaDescription() {
        $config = $this->getSocialMetaConfig();
        if ($this->owner->MetaDescription) {
            return $this->owner->MetaDescription;
        } else if ($this->owner->Summary) {
            // blog module
            return strip_tags($this->owner->obj('Summary'));
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
    
    public function getSocialMetaImage() {
        $config = $this->getSocialMetaConfig();
        $image = null;
        if (!is_a($this->owner, "HomePage")) {
            if ($this->owner->hasMethod('getCustomSocialMetaImage') && ($image = $this->owner->getCustomSocialMetaImage()) && $image->exists()) {
                // method that can be overridden on any page type
                $image = $image;
            } else if ($this->owner->hasMethod('getKeyvisualImage') && ($image = $this->owner->getKeyvisualImage()) && $image->exists()) {
                // method that can be overridden on any page type
                $image = $image;
            } else if ($this->owner->has_one('FeaturedImage') && $this->owner->FeaturedImage()) {
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
        } else if ($this->owner->hasMethod('getCredits') && ($authors = $this->owner->getCredits())) {
            // blog module
            return $authors;
//        disabled for now as version publisher is not really the author
//        } else {
//            // standard page
//            $version = $this->owner->allVersions()->last();
//            if ($version && ($author = $version->Author())) {
//                return new ArrayList(array($author));
//            }
        }
        return null;
    }
    
    public function getSocialMetaSections() {
        if ($this->owner->many_many('Categories') && ($categories = $this->owner->Categories()) && $categories->exists()) {
            // blog module
            return $categories->column('Title');
        } else if ($this->owner->has_one('EventCategory') && ($category = $this->owner->EventCategory())) {
            // events module
            return array($category->Title);
        }
        return null;
    }
    
    public function getSocialMetaTags() {
        if ($this->owner->many_many('Tags') && ($tags = $this->owner->Tags()) && $tags->exists()) {
            // blog module
            return $tags->column('Title');
        }
        return null;
    }
    
}

