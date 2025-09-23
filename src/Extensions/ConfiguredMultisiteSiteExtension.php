<?php

namespace Innoweb\SocialMeta\Extensions;

use Fromholdio\ConfiguredMultisites\Control\MultisitesRootController;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\FieldList;

class ConfiguredMultisiteSiteExtension extends Extension
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
        $link = trim((string) $this->getOwner()->Link(), '/');

        if ($link === MultisitesRootController::get_homepage_link()) {
            return $this->getOwner()->getSocialMetaConfig()->getMicroDataSchemaData();
        }
        return null;
    }
}
