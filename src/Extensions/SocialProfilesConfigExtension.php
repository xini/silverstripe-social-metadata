<?php

namespace Innoweb\SocialMeta\Extensions;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\LiteralField;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataExtension;
use SilverStripe\View\ArrayData;

class SocialProfilesConfigExtension extends DataExtension
{
    public function updateCMSFields(FieldList $fields)
    {
        $fields->insertBefore(
            'SocialMetaSameAsLinks',
            LiteralField::create(
                'SocialMetaSameAsSocialInfoField',
                '<p>Social media profiles added on the Social Media Profiles tab are added automatically.</p>'
            )
        );
    }

    public function updateSchemaData(&$data)
    {
        $profilePages = $this->owner->getSocialMetaValue('ProfilePages');
        if ($profilePages && $profilePages->exists()) {
            if (isset($data['sameAs'])) {
                $sameAs = $data['sameAs'];
            } else {
                $sameAs = [];
            }
            foreach ($profilePages as $profilePage) {
                $sameAs[] = $profilePage->URL;
            }
            if (count($sameAs)) {
                $sameAs = array_unique($sameAs);
                $data['sameAs'] = $sameAs;
            }
        }
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