<?php

namespace Innoweb\SocialMeta\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\SiteConfig\SiteConfig;

class CurrentConfigSiteConfigExtension extends Extension
{
    public function getCurrentSocialMetaConfig()
    {
        return SiteConfig::current_site_config();
    }
}
