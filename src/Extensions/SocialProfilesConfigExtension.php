<?php

namespace Innoweb\SocialMeta\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Model\ArrayData;
use SilverStripe\Model\List\ArrayList;

class SocialProfilesConfigExtension extends Extension
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
            $sameAs = $data['sameAs'] ?? [];
            
            foreach ($profilePages as $profilePage) {
                $sameAs[] = $profilePage->URL;
            }
            
            if (count($sameAs) > 0) {
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

        if ($this->getOwner()->ProfilesSnapchatPage) {
            $profiles[] = ArrayData::create(['URL' => $this->getOwner()->ProfilesSnapchatPage]);
        }

        if ($this->getOwner()->ProfilesTiktokPage) {
            $profiles[] = ArrayData::create(['URL' => $this->getOwner()->ProfilesTiktokPage]);
        }

        if ($this->getOwner()->ProfilesTwitchPage) {
            $profiles[] = ArrayData::create(['URL' => $this->getOwner()->ProfilesTwitchPage]);
        }

        if ($this->getOwner()->ProfilesVimeoPage) {
            $profiles[] = ArrayData::create(['URL' => $this->getOwner()->ProfilesVimeoPage]);
        }

        if ($this->getOwner()->ProfilesRedditPage) {
            $profiles[] = ArrayData::create(['URL' => $this->getOwner()->ProfilesRedditPage]);
        }

        if ($this->getOwner()->ProfilesGithubPage) {
            $profiles[] = ArrayData::create(['URL' => $this->getOwner()->ProfilesGithubPage]);
        }

        if ($this->getOwner()->ProfilesThreadsPage) {
            $profiles[] = ArrayData::create(['URL' => $this->getOwner()->ProfilesThreadsPage]);
        }

        if ($this->getOwner()->ProfilesMastodonPage) {
            $profiles[] = ArrayData::create(['URL' => $this->getOwner()->ProfilesMastodonPage]);
        }

        if ($this->getOwner()->ProfilesBlueskyPage) {
            $profiles[] = ArrayData::create(['URL' => $this->getOwner()->ProfilesBlueskyPage]);
        }

        return ArrayList::create($profiles);
    }
}
