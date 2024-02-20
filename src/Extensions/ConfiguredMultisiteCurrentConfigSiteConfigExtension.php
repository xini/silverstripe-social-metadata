<?php

namespace Innoweb\SocialMeta\Extensions;

use SilverStripe\ORM\DataExtension;
use Fromholdio\ConfiguredMultisites\Multisites;

class ConfiguredMultisiteCurrentConfigSiteConfigExtension extends DataExtension
{
    public function getCurrentSocialMetaConfig()
    {
        return Multisites::inst()->getCurrentSite();
    }
}
