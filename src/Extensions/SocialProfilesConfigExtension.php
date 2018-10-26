<?php

namespace Innoweb\SocialMeta\Extensions;

use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataExtension;
use SilverStripe\View\ArrayData;

class SocialProfilesConfigExtension extends DataExtension
{
    public function updateSchemaData($data)
    {
        $profilePages = $this->owner->getSocialMetaValue('ProfilePages');
        if ($profilePages && $profilePages->exists()) {
            $sameAs = [];
            foreach ($profilePages as $profilePage) {
                $sameAs = $profilePage->URL;
            }
            if (count($sameAs)) {
                $data['sameAs'] = $sameAs;
            }
        }
        return $data;
    }

    public function getSocialMetaProfilePages()
    {
        $profiles = [];
        if ($this->owner->ProfilesFacebookPage) {
            $profiles[] = ArrayData::create(['URL' => $this->owner->ProfilesFacebookPage]);
        }
        if ($this->owner->ProfilesTwitterPage) {
            $profiles[] = ArrayData::create(['URL' => $this->owner->ProfilesTwitterPage]);
        }
        if ($this->owner->ProfilesGooglePage) {
            $profiles[] = ArrayData::create(['URL' => $this->owner->ProfilesGooglePage]);
        }
        if ($this->owner->ProfilesLinkedinPage) {
            $profiles[] = ArrayData::create(['URL' => $this->owner->ProfilesLinkedinPage]);
        }
        if ($this->owner->ProfilesPinterestPage) {
            $profiles[] = ArrayData::create(['URL' => $this->owner->ProfilesPinterestPage]);
        }
        if ($this->owner->ProfilesInstagramPage) {
            $profiles[] = ArrayData::create(['URL' => $this->owner->ProfilesInstagramPage]);
        }
        if ($this->owner->ProfilesYoutubePage) {
            $profiles[] = ArrayData::create(['URL' => $this->owner->ProfilesYoutubePage]);
        }
        return ArrayList::create($profiles);
    }
}