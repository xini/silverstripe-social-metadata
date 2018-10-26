<?php

namespace Innoweb\SocialMeta\Extensions;

use SilverStripe\ORM\DataExtension;
use SilverStripe\SiteConfig\SiteConfig;

class CurrentConfigSiteConfigExtension extends DataExtension
{
    public function getCurrentSocialMetaConfig()
    {
        return SiteConfig::current_site_config();
    }
}