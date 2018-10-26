<?php

namespace Innoweb\SocialMeta\Extensions;

use SilverStripe\ORM\DataExtension;
use Symbiote\Multisites\Multisites;

class MultisiteCurrentConfigSiteConfigExtension extends DataExtension
{
    public function getCurrentSocialMetaConfig()
    {
        return Multisites::inst()->getCurrentSite();
    }
}