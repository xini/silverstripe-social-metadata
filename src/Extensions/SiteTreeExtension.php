<?php

namespace Innoweb\SocialMeta\Extensions;

use BurnBright\ExternalURLField\ExternalURLField;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\File;
use SilverStripe\Assets\Image;
use SilverStripe\CMS\Controllers\RootURLController;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\TextField;
use SilverStripe\i18n\i18n;
use SilverStripe\Security\Member;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\View\ArrayData;
use SilverStripe\View\HTML;

class SiteTreeExtension extends \SilverStripe\CMS\Model\SiteTreeExtension
{
    private static $title_divider = ' - ';
    private static $metadata_tab_enabled = true;

    private static $db = [
        'MetaTitle'         =>  'Varchar(255)',
        'MetaCanonicalURL'  =>  'ExternalURL'
    ];

    private static $has_one = [
        'MetaImage'         =>  Image::class
    ];

    private static $owns = [
        'MetaImage'
    ];

    public function getSocialMetaConfig()
    {
        $siteConfig = SiteConfig::current_site_config();
        return $siteConfig->getCurrentSocialMetaConfig();
    }

    public function MetaTags(&$tagString)
    {
        $socialMetaTags = [];

        // update title tag if set
        if (preg_match('/<title>.*<\/title>/', $tagString)) {
            if ($this->owner->getSocialMetaValue('Title')) {
                $formattedTitleTag = HTML::createTag(
                    'title',
                    [],
                    $this->owner->getSocialMetaValue('Title')
                );
                $tagString = preg_replace('/<title>.*<\/title>/', $formattedTitleTag, $tagString);
            }
        }

        // update meta description
        $tagString = preg_replace('/<meta name="description"[^>]+>\\n/', '', $tagString);
        if ($this->owner->getSocialMetaValue('Description')) {
            $socialMetaTags[] = HTML::createTag('meta', [
                'name'      =>  'description',
                'content'   =>  $this->owner->getSocialMetaValue('Description')
            ]);
        }

        $metaAuthor = $this->owner->getSocialMetaValue('Author');
        if ($metaAuthor) {
            $socialMetaTags[] = HTML::createTag('meta', [
                'name'      =>  'author',
                'content'   =>  $metaAuthor
            ]);
        }

        $canonicalURL = $this->owner->getSocialMetaValue('CanonicalURL');
        if ($canonicalURL) {
            $socialMetaTags[] = HTML::createTag('link', [
                'rel'   =>  'canonical',
                'href'  =>  $canonicalURL
            ]);
        }

        // Twitter
        $twitterCardType = $this->owner->getSocialMetaValue('TwitterCardType');
        $twitterImageURL = $this->owner->getSocialMetaValue('TwitterImageURL');
        $twitterSite = $this->owner->getSocialMetaValue('TwitterSite');
        $twitterTitle = $this->owner->getSocialMetaValue('TwitterTitle');
        $twitterDescription = $this->owner->getSocialMetaValue('TwitterDescription');
        $twitterCreators = $this->owner->getSocialMetaValue('TwitterCreators');

        if ($twitterCardType) {
            $socialMetaTags[] = HTML::createTag('meta', [
                'name'      =>  'twitter:card',
                'content'   =>  $twitterCardType
            ]);
        }

        if ($twitterSite) {
            $socialMetaTags[] = HTML::createTag('meta', [
                'name'      =>  'twitter:site',
                'content'   =>  $twitterSite
            ]);
        }

        if ($twitterTitle) {
            $socialMetaTags[] = HTML::createTag('meta', [
                'name'      =>  'twitter:title',
                'content'   =>  $twitterTitle
            ]);
        }

        if ($twitterDescription) {
            $socialMetaTags[] = HTML::createTag('meta', [
                'name'      =>  'twitter:description',
                'content'   =>  $twitterDescription
            ]);
        }

        if ($twitterImageURL) {
            $socialMetaTags[] = HTML::createTag('meta', [
                'name'      =>  'twitter:image',
                'content'   =>  $twitterImageURL
            ]);
        }

        if ($twitterCreators && is_array($twitterCreators)) {
            foreach ($twitterCreators as $twitterCreator) {
                $socialMetaTags[] = HTML::createTag('meta', [
                    'name'      =>  'twitter:creator',
                    'content'   =>  $twitterCreator
                ]);
            }
        }

        // Facebook / OpenGraph
        $facebookAppID = $this->owner->getSocialMetaValue('FacebookAppID');
        $facebookAdminIDs = $this->owner->getSocialMetaValue('FacebookAdminIDs');
        $openGraphType = $this->owner->getSocialMetaValue('OpenGraphType');
        $openGraphURL = $this->owner->getSocialMetaValue('OpenGraphURL');
        $openGraphTitle = $this->owner->getSocialMetaValue('OpenGraphTitle');
        $openGraphDescription = $this->owner->getSocialMetaValue('OpenGraphDescription');
        $openGraphLocale = $this->owner->getSocialMetaValue('OpenGraphLocale');
        $openGraphSite = $this->owner->getSocialMetaValue('OpenGraphSite');
        $openGraphImageURL = $this->owner->getSocialMetaValue('OpenGraphImageURL');

        if ($facebookAppID) {
            $socialMetaTags[] = HTML::createTag('meta', [
                'property'  =>  'fb:app_id',
                'content'   =>  $facebookAppID
            ]);
        }

        if ($facebookAdminIDs && is_array($facebookAdminIDs)) {
            foreach ($facebookAdminIDs as $facebookAdminID) {
                $socialMetaTags[] = HTML::createTag('meta', [
                    'property'  =>  'fb:admins',
                    'content'   =>  $facebookAdminID
                ]);
            }
        }

        if ($openGraphType) {
            $socialMetaTags[] = HTML::createTag('meta', [
                'property'  =>  'og:type',
                'content'   =>  $openGraphType
            ]);
        }

        if ($openGraphURL) {
            $socialMetaTags[] = HTML::createTag('meta', [
                'property'  =>  'og:url',
                'content'   =>  $openGraphURL
            ]);
        }

        if ($openGraphTitle) {
            $socialMetaTags[] = HTML::createTag('meta', [
                'property'  =>  'og:title',
                'content'   =>  $openGraphTitle
            ]);
        }

        if ($openGraphDescription) {
            $socialMetaTags[] = HTML::createTag('meta', [
                'property'  =>  'og:description',
                'content'   =>  $openGraphDescription
            ]);
        }

        if ($openGraphLocale) {
            $socialMetaTags[] = HTML::createTag('meta', [
                'property'  =>  'og:locale',
                'content'   =>  $openGraphLocale
            ]);
        }

        if ($openGraphSite) {
            $socialMetaTags[] = HTML::createTag('meta', [
                'property'  =>  'og:site_name',
                'content'   =>  $openGraphSite
            ]);
        }

        if ($openGraphImageURL) {
            $socialMetaTags[] = HTML::CreateTag('meta', [
                'property'  =>  'og:image',
                'content'   =>  $openGraphImageURL
            ]);
        }

        // Articles
        if ($openGraphType === 'article') {

            $facebookPublisher = $this->owner->getSocialMetaValue('FacebookPublisher');
            $openGraphAuthors = $this->owner->getSocialMetaValue('OpenGraphAuthors');
            $openGraphPublicationTime = $this->owner->getSocialMetaValue('OpenGraphPublicationTime');
            $openGraphModificationTime = $this->owner->getSocialMetaValue('OpenGraphModificationTime');
            $openGraphSection = $this->owner->getSocialMetaValue('OpenGraphSection');
            $openGraphTags = $this->owner->getSocialMetaValue('OpenGraphTags');

            if ($facebookPublisher) {
                $socialMetaTags[] = HTML::createTag('meta', [
                    'property'  =>  'article:publisher',
                    'content'   =>  $facebookPublisher
                ]);
            }

            if ($openGraphAuthors && is_array($openGraphAuthors)) {
                foreach ($openGraphAuthors as $openGraphAuthor) {
                    $socialMetaTags[] = HTML::createTag('meta', [
                        'property'  =>  'article:author',
                        'content'   =>  $openGraphAuthor
                    ]);
                }
            }

            if ($openGraphPublicationTime) {
                $socialMetaTags[] = HTML::createTag('meta', [
                    'property'  =>  'article:published_time',
                    'content'   =>  $openGraphPublicationTime
                ]);
            }

            if ($openGraphModificationTime) {
                $socialMetaTags[] = HTML::createTag('meta', [
                    'property'  =>  'article:modification_time',
                    'content'   =>  $openGraphModificationTime
                ]);
            }

            if ($openGraphSection) {
                $socialMetaTags[] = HTML::createTag('meta', [
                    'property'  =>  'article:section',
                    'content'   =>  $openGraphSection
                ]);
            }

            if ($openGraphTags && is_array($openGraphTags)) {
                foreach ($openGraphTags as $openGraphTag) {
                    $socialMetaTags[] = HTML::createTag('meta', [
                        'property'  =>  'article:tag',
                        'content'  =>  $openGraphTag
                    ]);
                }
            }
        }

        $schemaData = $this->owner->getSocialMetaValue('SchemaData');
        if ($schemaData) {
            $socialMetaTags[] = HTML::createTag(
                'script',
                ['type' =>  'application/ld+json'],
                json_encode($schemaData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
            );
        }

        if ($this->owner->hasMethod('updateSocialMetaTags')) {
            $socialMetaTags = $this->owner->updateSocialMetaTags($socialMetaTags);
        }

        $tagString .= "\n" . implode("\n", $socialMetaTags);
    }

    public function getSocialMetaValue($value, $skipController = false)
    {
        if (!$skipController && $controller = Controller::curr()) {

            if ($controller->hasMethod('getSocialMetaObject')) {

                if ($object = $controller->getSocialMetaObject()) {

                    if ($object->hasMethod('getSocialMeta' . $value)) {
                        return $object->{'getSocialMeta' . $value}();
                    }
                    if ($object->hasMethod('getDefaultSocialMeta' . $value)) {
                        return $object->{'getDefaultSocialMeta' . $value}();
                    }
                }
            }
            if ($controller->hasMethod('getSocialMeta' . $value)) {
                return $controller->{'getSocialMeta' . $value}();
            }
        }

        if ($this->owner->hasMethod('getSocialMeta' . $value)) {
            return $this->owner->{'getSocialMeta' . $value}();
        }

        if ($this->owner->hasMethod('getDefaultSocialMeta' . $value)) {
            return $this->owner->{'getDefaultSocialMeta' . $value}();
        }

        return null;
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
        return $siteName;
    }

    public function getDefaultSocialMetaDescription()
    {
        if ($this->owner->MetaDescription) {
            return $this->owner->MetaDescription;
        }

        $config = $this->owner->getSocialMetaConfig();
        return $config->getSocialMetaValue('SiteDescription');
    }

    public function getDefaultSocialMetaCanonicalURL()
    {
        return $this->owner->MetaCanonicalURL ?: preg_replace('/home\/$/i', '', $this->owner->AbsoluteLink());
    }

    public function getDefaultSocialMetaAuthors()
    {
        return null;
    }

    public function getDefaultSocialMetaAuthorString()
    {
        $names = $this->owner->getSocialMetaValue('AuthorsNames');
        if ($names && count($names) > 0) {
            return implode(',', $names);
        }
        return null;
    }

    public function getDefaultSocialMetaAuthorsNames()
    {
        $authors = $this->owner->getSocialMetaValue('Authors');

        if (is_string($authors)) {
            return explode(',', $authors);
        }

        if ($authors) {
            $names = [];
            foreach ($authors as $author) {
                if (is_a($author, Member::class) && $author->Name) {
                    $names[] = $author->Name;
                } else if (is_a($author, ArrayData::class) && $author->Name) {
                    $names[] = $author->Name;
                } else if (is_object($author) && $author->Name) {
                    $names[] = $author->Name;
                } else if (is_array($author) && isset($author['Name'])) {
                    $names[] = $author['Name'];
                } else if (is_string($author)) {
                    $names[] = $author;
                }
            }
            return $names;
        }
        return null;
    }

    public function getDefaultSocialMetaImage()
    {
        $image = $this->owner->MetaImage();
        if ($image && $image->exists()) {
            return $image;
        }

        $link = trim($this->owner->Link(), '/');
        if ($link !== '' && $link !== RootURLController::get_homepage_link()) {

            // extract first image in page content
            $htmlValue = Injector::inst()->create('HTMLValue', $this->owner->Content);
            if ($images = $htmlValue->getElementsByTagName('img')) {
                foreach ($images as $img) {
                    $path = urldecode(Director::makeRelative($img->getAttribute('src')));
                    $path = preg_replace('/_resampled\/[a-z0-9]*\//i', '', $path);
                    if ($tmp = File::find($path)) {
                        return $tmp;
                    }
                }
            }
        }

        $config = $this->owner->getSocialMetaConfig();
        return $config->getSocialMetaValue('SiteImage');
    }

    public function getDefaultSocialMetaLocale() {
        return i18n::get_locale();
    }

    public function getDefaultSocialMetaPublicationTime()
    {
        $version = $this->owner->allVersions()->filter('WasPublished', 1)->last();
        if ($version) {
            $created = $version->relField('Created');
            return date('c', strtotime($created));
        }
    }

    public function getDefaultSocialMetaModificationTime()
    {
        return ($this->owner->LastEdited)
            ? $this->owner->dbObject('LastEdited')->Rfc3339()
            : null;
    }

    public function getDefaultSocialMetaCreationTime()
    {
        return ($this->owner->Created)
        ? $this->owner->dbObject('Created')->Rfc3339()
        : null;
    }
    
    public function getDefaultSocialMetaCategory()
    {
        return null;
    }

    public function getDefaultSocialMetaTags()
    {
        return null;
    }

    public function getDefaultSocialMetaTwitterCardType()
    {
        $image = $this->owner->getSocialMetaValue('TwitterImage');

        if ($image) {
            if ($image->getWidth() >= 300 && $image->getHeight() >= 157) {
                return 'summary_large_image';
            }
        }

        return 'summary';
    }

    public function getDefaultSocialMetaTwitterImage()
    {
        $image = $this->owner->getSocialMetaValue('Image');
        if ($image && $image->exists()) {
            if ($image->hasMethod('FocusFill')) {
                return $image->FocusFill(1200,630);
            }
            return $image->Fill(1200,630);
        }
        return $image;
    }

    public function getDefaultSocialMetaTwitterImageURL()
    {
        $image = $this->owner->getSocialMetaValue('TwitterImage');
        return ($image) ? $image->AbsoluteLink() : null;
    }

    public function getDefaultSocialMetaTwitterSite()
    {
        $config = $this->owner->getSocialMetaConfig();
        return $config->getSocialMetaValue('TwitterAccount');
    }

    public function getDefaultSocialMetaTwitterTitle()
    {
        return $this->owner->getSocialMetaValue('Title');
    }

    public function getDefaultSocialMetaTwitterDescription()
    {
        return $this->owner->getSocialMetaValue('Description');
    }

    public function getDefaultSocialMetaTwitterCreators()
    {
        $authors = $this->owner->getSocialMetaValue('AuthorsNames');

        if ($authors) {
            $creators = [];
            foreach ($authors as $author) {
                if (is_a($author, Member::class) && $author->TwitterHandle) {
                    $creators[] = $author->TwitterHandle;
                } else if (is_a($author, ArrayData::class) && $author->TwitterHandle) {
                    $creators[] = $author->TwitterHandle;
                } else if (is_object($author) && $author->TwitterHandle) {
                    $creators[] = $author->TwitterHandle;
                } else if (is_array($author) && isset($author['TwitterHandle'])) {
                    $creators[] = $author['TwitterHandle'];
                }
            }
            return $creators;
        }

        return null;
    }

    public function getDefaultSocialMetaFacebookAppID()
    {
        $config = $this->owner->getSocialMetaConfig();
        return $config->getSocialMetaValue('FacebookAppID');
    }

    public function getDefaultSocialMetaFacebookAdminIDs()
    {
        $config = $this->owner->getSocialMetaConfig();
        return $config->getSocialMetaValue('FacebookAdminIDs');
    }

    public function getDefaultSocialMetaFacebookPublisher()
    {
        $config = $this->owner->getSocialMetaConfig();
        return $config->getSocialMetaValue('FacebookPage');
    }

    public function getDefaultSocialMetaOpenGraphType()
    {
        $configType = Config::inst()->get(get_class($this->owner), 'socialmeta_opengraph_type');
        return $configType ?: 'website';
    }

    public function getDefaultSocialMetaOpenGraphURL()
    {
        return $this->owner->getSocialMetaValue('CanonicalURL');
    }

    public function getDefaultSocialMetaOpenGraphTitle()
    {
        return $this->owner->getSocialMetaValue('Title');
    }

    public function getDefaultSocialMetaOpenGraphDescription()
    {
        return $this->owner->getSocialMetaValue('Description');
    }

    public function getDefaultSocialMetaOpenGraphLocale()
    {
        return $this->owner->getSocialMetaValue('Locale');
    }

    public function getDefaultSocialMetaOpenGraphSite()
    {
        $config = $this->owner->getSocialMetaConfig();
        return $config->getSocialMetaValue('SiteName');
    }

    public function getDefaultSocialMetaOpenGraphImage()
    {
        $image = $this->owner->getSocialMetaValue('Image');
        if ($image && $image->exists()) {
            if ($image->hasMethod('FocusFill')) {
                return $image->FocusFill(1200,630);
            }
            return $image->Fill(1200,630);
        }
        return $image;
    }

    public function getDefaultSocialMetaOpenGraphImageURL()
    {
        $image = $this->owner->getSocialMetaValue('OpenGraphImage');
        return ($image) ? $image->AbsoluteLink() : null;
    }

    public function getDefaultSocialMetaOpenGraphAuthors()
    {
        $authors = $this->owner->getSocialMetaValue('AuthorsNames');

        if ($authors) {
            $urls = [];
            foreach ($authors as $author) {
                if (is_a($author, Member::class) && $author->FacebookProfileURL) {
                    $urls[] = $author->FacebookProfileURL;
                } else if (is_object($author) && $author->FacebookProfileURL) {
                    $urls[] = $author->FacebookProfileURL;
                } else if (is_a($author, ArrayData::class) && $author->URL) {
                    $urls[] = $author->URL;
                } else if (is_array($author) && isset($author['FacebookProfileURL'])) {
                    $urls[] = $author['FacebookProfileURL'];
                } else if (is_array($author) && isset($author['URL'])) {
                    $urls[] = $author['URL'];
                }
            }
            return $urls;
        }

        return null;
    }

    public function getDefaultSocialMetaOpenGraphPublicationTime()
    {
        return $this->owner->getSocialMetaValue('PublicationTime');
    }

    public function getDefaultSocialMetaOpenGraphModificationTime()
    {
        return $this->owner->getSocialMetaValue('ModificationTime');
    }

    public function getDefaultSocialMetaOpenGraphSection()
    {
        return $this->owner->getSocialMetaValue('Category');
    }

    public function getDefaultSocialMetaOpenGraphTags()
    {
        return $this->owner->getSocialMetaValue('Tags');
    }

    public function getDefaultSocialMetaSchemaData()
    {
        $link = trim($this->owner->Link(), '/');
        if ($link === '' || $link === RootURLController::get_homepage_link()) {
            return $this->owner->getSocialMetaConfig()->getMicroDataSchemaData();
        }
        return null;
    }

    public function updateCMSFields(FieldList $fields)
    {
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
            ))
            ->setAttribute('placeholder', $this->owner->AbsoluteLink());

        $metaImageField = UploadField::create(
            'MetaImage',
            _t(SiteTree::class.'.METAIMAGELABEL', 'Image')
        )
            ->setFolderName('Meta')
            ->setAllowedFileCategories('image');

        $tabEnabled = $this->owner->config()->get('metadata_tab_enabled');
        if ($tabEnabled) {

            $fields->removeByName('MetaDescription');
            $fields->removeByName('ExtraMeta');
            $fields->removeByName('Metadata');

            $fields->addFieldsToTab(
                'Root.Metadata',
                [
                    $metaTitleField,
                    $metaURLField,
                    $metaImageField,
                    $metaDescriptionField = TextareaField::create('MetaDescription', $this->owner->fieldLabel('MetaDescription')),
                    $metaExtraField = TextareaField::create('ExtraMeta', $this->owner->fieldLabel('ExtraMeta'))
                ]
            );

            $metaDescriptionField
                ->setRightTitle(
                    _t(
                        SiteTree::class.'.METADESCHELP',
                        "Search engines use this content for displaying search results (although it will not influence their ranking)."
                    )
                )
                ->addExtraClass('help');

            $metaExtraField
                ->setRightTitle(
                    _t(
                        SiteTree::class.'.METAEXTRAHELP',
                        "HTML tags for additional meta information. For example <meta name=\"customName\" content=\"your custom content here\" />"
                    )
                )
                ->addExtraClass('help');

        } else {
            $fields->insertBefore($metaTitleField, 'MetaDescription');
            $fields->insertBefore($metaURLField, 'MetaDescription');
        }
    }

    public function updateFieldLabels(&$labels)
    {
        $labels['MetaTitle'] = _t(SiteTree::class.'.METATITLE', 'Title');
    }
}
