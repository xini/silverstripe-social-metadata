<?php

namespace Innoweb\SocialMeta\Extensions;

use Fromholdio\ConfiguredMultisites\Multisites;
use SilverStripe\Core\Extension;

class ConfiguredMultisiteCurrentConfigSiteConfigExtension extends Extension
{
    public function getCurrentSocialMetaConfig()
    {
        return Multisites::inst()->getCurrentSite();
    }
}
