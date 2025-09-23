<?php

namespace Innoweb\SocialMeta\Extensions;

use SilverStripe\Core\Extension;
use Symbiote\Multisites\Multisites;

class MultisiteCurrentConfigSiteConfigExtension extends Extension
{
    public function getCurrentSocialMetaConfig()
    {
        return Multisites::inst()->getCurrentSite();
    }
}
