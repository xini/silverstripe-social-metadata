<?php

namespace Innoweb\SocialMeta\Extensions;

use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataExtension;
use Symbiote\Multisites\Control\MultisitesRootController;

class MultisiteSiteExtension extends DataExtension
{
    public function updateSiteCMSFields(FieldList $fields)
    {
        $this->updateCMSFields($fields);
    }

    public function getSocialMetaSiteDescription()
    {
        if (!empty($this->getOwner()->SocialMetaSiteDescription)) {
            return $this->getOwner()->SocialMetaSiteDescription;
        }

        $homeLink = MultisitesRootController::get_homepage_link();
        $homePage = SiteTree::get_by_link($homeLink);

        if ($homePage && $homePage->exists()) {
            if ($homePage->hasMethod('getSocialMetaDescription') && $homePage->getSocialMetaDescription()) {
                return $homePage->getSocialMetaDescription();
            }
            return $homePage->MetaDescription;
        }
        return null;
    }
    
    public function getSocialMetaSchemaData()
    {
        $link = trim($this->owner->Link(), '/');
        
        if ($link === MultisitesRootController::get_homepage_link()) {
            return $this->owner->getSocialMetaConfig()->getMicroDataSchemaData();
        }
    }
    
}