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
                '<p>' . _t('SocialProfilesConfigExtension.ProfilesAddedAutomatically', 'Social media profiles added on the Social Media Profiles tab are added automatically.') . '</p>'
            )
        );
    }

    public function updateSchemaData(&$data)
    {
        $profilePages = $this->getOwner()->getSocialMetaValue('ProfilePages');
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
                $sameAs = array_values(array_unique($sameAs));
                $data['sameAs'] = $sameAs;
            }
        }
    }

    public function getSocialMetaProfilePages()
    {
        $profiles = [];
        if ($this->getOwner()->ProfilesFacebookPage) {
            $profiles[] = ArrayData::create(['URL' => $this->getOwner()->ProfilesFacebookPage]);
        }
        if ($this->getOwner()->ProfilesTwitterPage) {
            $profiles[] = ArrayData::create(['URL' => $this->getOwner()->ProfilesTwitterPage]);
        }
        if ($this->getOwner()->ProfilesGooglePage) {
            $profiles[] = ArrayData::create(['URL' => $this->getOwner()->ProfilesGooglePage]);
        }
        if ($this->getOwner()->ProfilesLinkedinPage) {
            $profiles[] = ArrayData::create(['URL' => $this->getOwner()->ProfilesLinkedinPage]);
        }
        if ($this->getOwner()->ProfilesPinterestPage) {
            $profiles[] = ArrayData::create(['URL' => $this->getOwner()->ProfilesPinterestPage]);
        }
        if ($this->getOwner()->ProfilesInstagramPage) {
            $profiles[] = ArrayData::create(['URL' => $this->getOwner()->ProfilesInstagramPage]);
        }
        if ($this->getOwner()->ProfilesYoutubePage) {
            $profiles[] = ArrayData::create(['URL' => $this->getOwner()->ProfilesYoutubePage]);
        }
        return ArrayList::create($profiles);
    }
}
