<?php

namespace Innoweb\SocialMeta\Extensions;

use BurnBright\ExternalURLField\ExternalURLField;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\Image;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\ToggleCompositeField;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\ORM\FieldType\DBHTMLVarchar;
use SilverStripe\ORM\FieldType\DBText;
use SilverStripe\ORM\FieldType\DBVarchar;
use SilverStripe\SiteConfig\SiteConfig;

class MetaFieldsDataObjectExtension extends DataExtension
{
    private static $metafields_tab_name = 'Root.Metadata';

    private static $db = [
        'MetaTitle'         =>  'Varchar(255)',
        'MetaCanonicalURL'  =>  'ExternalURL',
        'MetaDescription'   =>  'Text',
        'ExtraMeta'         =>  "HTMLFragment(['whitelist' => ['meta', 'link']])"
    ];

    private static $has_one = [
        'MetaImage'         =>  Image::class
    ];

    private static $owns = [
        'MetaImage'
    ];

    private static $title_divider = ' - ';
    private static $metadata_tab_enabled = true;
    private static $meta_description_fallback_fields = [];
    private static $meta_description_fallback_to_site = true;

    public function getSocialMetaParent()
    {
        return false;
    }

    public function getSocialMetaConfig()
    {
        $siteConfig = SiteConfig::current_site_config();
        return $siteConfig->getCurrentSocialMetaConfig();
    }

    public function getDefaultSocialMetaTitle()
    {
        if ($this->owner->MetaTitle) {
            return $this->owner->MetaTitle;
        }

        $config = $this->owner->getSocialMetaConfig();
        $siteName = $config->getSocialMetaValue('SiteName');
        $divider = $this->owner->config()->get('title_divider');

        if ($this->owner->Title) {
            return $this->owner->Title . $divider . $siteName;
        }

        if ($parent = $this->owner->getSocialMetaParent()) {
            return $parent->getSocialMetaValue('Title', true);
        }
        return $siteName;
    }

    public function getDefaultSocialMetaDescription()
    {
        if ($this->owner->MetaDescription) {
            return $this->owner->MetaDescription;
        }

        if ($fallbackFields = $this->owner->config()->get('meta_description_fallback_fields')) {
            foreach ($fallbackFields as $fieldName) {
                if ($this->owner->hasDatabaseField($fieldName) && $this->owner->getField($fieldName)) {
                    $field = $this->owner->dbObject($fieldName);
                    if (is_a($field, DBHTMLVarchar::class)) {
                        return $field->Plain();
                    } else if (is_a($field, DBHTMLText::class)) {
                        return $field->Summary();
                    } else if (is_a($field, DBText::class)) {
                        return $field->Summary();
                    } else if (is_a($field, DBVarchar::class)) {
                        return $field->Plain();
                    }
                }
            }
        }

        if ($parent = $this->owner->getSocialMetaParent()) {
            return $parent->getSocialMetaValue('Description', true);
        }

        if ($this->owner->config()->get('meta_description_fallback_to_site')) {
            $config = $this->owner->getSocialMetaConfig();
            return $config->getSocialMetaValue('SiteDescription');
        }

        return null;
    }

    public function getDefaultSocialMetaCanonicalURL()
    {
        return $this->owner->MetaCanonicalURL
            ?: preg_replace(
                '/home\/$/i',
                '',
                $this->owner->AbsoluteLink()
            );
    }

    public function getDefaultSocialMetaImage()
    {
        $image = $this->owner->MetaImage();
        if ($image && $image->exists()) {
            return $image;
        }

        if ($parent = $this->owner->getSocialMetaParent()) {
            return $parent->getSocialMetaValue('Image', true);
        }

        $config = $this->owner->getSocialMetaConfig();
        return $config->getSocialMetaValue('SiteImage');
    }

    public function getDefaultSocialMetaPublicationTime()
    {
        if ($this->owner->hasMethod('allVersions')) {
            $version = $this->owner->allVersions()->filter('WasPublished', 1)->last();
            if ($version) {
                $created = $version->relField('Created');
                return date('c', strtotime($created));
            }
        }
        return ($this->owner->Created)
            ? $this->owner->dbObject('Created')->Rfc3339()
            : null;
    }

    public function getDefaultSocialMetaModificationTime()
    {
        return ($this->owner->LastEdited)
            ? $this->owner->dbObject('LastEdited')->Rfc3339()
            : null;
    }

    public function updateCMSFields(FieldList $fields)
    {
        $fields->removeByName('MetaTitle');
        $fields->removeByName('MetaDescription');
        $fields->removeByName('MetaCanonicalURL');
        $fields->removeByName('MetaImage');
        $fields->removeByName('ExtraMeta');

        $metaTitleField = TextField::create(
            'MetaTitle',
            $this->owner->fieldLabel('MetaTitle')
        )
            ->setRightTitle(_t(
                SiteTree::class.'.METATITLEHELP',
                'Shown at the top of the browser window and used as the "linked text" by search engines.'
            ))
            ->addExtraClass('help');

        $metaURLField = ExternalURLField::create('MetaCanonicalURL', 'Canonical URL')
            ->setRightTitle(_t(
                SiteTree::class.'.METACANONICALURLHELP',
                'This defaults to the absolute URL of the page. Only set this if search engines should count another URL as the original (e.g. if re-posting a blog post from another source).'
            ));

        $metaImageField = UploadField::create(
            'MetaImage',
            _t(SiteTree::class.'.METAIMAGELABEL', 'Image')
        )
            ->setFolderName('Meta')
            ->setAllowedFileCategories('image');

        $metaDescriptionField = TextareaField::create('MetaDescription', $this->owner->fieldLabel('MetaDescription'))
            ->setRightTitle(_t(
                SiteTree::class.'.METADESCHELP',
                "Search engines use this content for displaying search results (although it will not influence their ranking)."
            ))
            ->addExtraClass('help');

        $metaExtraField = TextareaField::create('ExtraMeta', $this->owner->fieldLabel('ExtraMeta'))
            ->setRightTitle(_t(
                SiteTree::class.'.METAEXTRAHELP',
                "HTML tags for additional meta information. For example <meta name=\"customName\" content=\"your custom content here\" />"
            ))
            ->addExtraClass('help');

        $tabEnabled = $this->owner->config()->get('metadata_tab_enabled');
        if ($tabEnabled) {

            $tabName = $this->owner->config()->get('metafields_tab_name');

            $fields->addFieldsToTab(
                $tabName,
                [
                    $metaTitleField,
                    $metaURLField,
                    $metaImageField,
                    $metaDescriptionField,
                    $metaExtraField
                ]
            );

        } else {
            $fields->push(
                ToggleCompositeField::create(
                    'Metadata',
                    _t('SiteTree.MetadataToggle', 'Metadata'),
                    [
                        $metaTitleField,
                        $metaURLField,
                        $metaImageField,
                        $metaDescriptionField,
                        $metaExtraField
                    ]
                )->setHeadingLevel(4)
            );
        }
    }
}
