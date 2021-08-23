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
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\TextareaField;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\ORM\FieldType\DBHTMLVarchar;
use SilverStripe\ORM\FieldType\DBText;
use SilverStripe\ORM\FieldType\DBVarchar;
use SilverStripe\Security\Member;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\View\ArrayData;
use SilverStripe\View\HTML;
use SilverStripe\i18n\i18n;

class SiteTreeExtension extends \SilverStripe\CMS\Model\SiteTreeExtension
{
    public const INCLUDE_SITE_JSONLD_HOME = 'home';
    public const INCLUDE_SITE_JSONLD_ALL = 'all';

    private static $title_divider = ' - ';
    private static $metadata_tab_enabled = true;
    private static $meta_description_fallback_fields = [];
    private static $meta_description_fallback_to_site = true;
    private static $minify_jsonld = true;
    private static $include_site_jsonld = self::INCLUDE_SITE_JSONLD_HOME;

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
        $tagString = preg_replace('/<meta name="description"[^>]+>\\n?/', '', $tagString);
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
        $openGraphImage = $this->owner->getSocialMetaValue('OpenGraphImage');
        $openGraphSeeAlsoEntries = $this->owner->getSocialMetaValue('OpenGraphSeeAlsoEntries');

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

        if ($openGraphImage) {
            $socialMetaTags[] = HTML::CreateTag('meta', [
                'property'  =>  'og:image',
                'content'   =>  $openGraphImage->getAbsoluteURL()
            ]);
            if (Director::is_https()) {
                $socialMetaTags[] = HTML::CreateTag('meta', [
                    'property'  =>  'og:image:secure_url',
                    'content'   =>  $openGraphImage->getAbsoluteURL()
                ]);
            }
            $socialMetaTags[] = HTML::CreateTag('meta', [
                'property'  =>  'og:image:type',
                'content'   =>  $openGraphImage->getMimeType()
            ]);
            $socialMetaTags[] = HTML::CreateTag('meta', [
                'property'  =>  'og:image:width',
                'content'   =>  $openGraphImage->getWidth()
            ]);
            $socialMetaTags[] = HTML::CreateTag('meta', [
                'property'  =>  'og:image:height',
                'content'   =>  $openGraphImage->getHeight()
            ]);
            $socialMetaTags[] = HTML::CreateTag('meta', [
                'property'  =>  'og:image:alt',
                'content'   =>  $openGraphImage->Title
            ]);
        }

        if ($openGraphSeeAlsoEntries && $openGraphSeeAlsoEntries->exists()) {
            foreach ($openGraphSeeAlsoEntries as $openGraphSeeAlsoEntry) {
                $socialMetaTags[] = HTML::createTag('meta', [
                    'property'  =>  'og:see_also',
                    'content'   =>  $openGraphSeeAlsoEntry->URL
                ]);
            }
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

        $schemaData = null;
        $pageSchemaData = $this->owner->getSocialMetaValue('SchemaData');
        $includeSiteSchemaData = $this->owner->getIncludeSiteSchemaData();
        if ($pageSchemaData && $includeSiteSchemaData) {
            $config = $this->owner->getSocialMetaConfig();
            if (isset($pageSchemaData['@type']) || isset($pageSchemaData['@context'])) {
                // page array directly defines a type. create an array and place both blocks in it
                $schemaData = [];
                $schemaData[] = $config->getMicroDataSchemaData();
                $schemaData[] = $pageSchemaData;
            } else if (count($pageSchemaData) > 1) {
                // page data define more than one type. add site data to top of page array
                array_unshift($pageSchemaData, $config->getMicroDataSchemaData());
                $schemaData = $pageSchemaData;
            } else if (count($pageSchemaData) == 1) {
                // page data defines on type, wrapped in an array.
                $schemaData = array_merge($config->getMicroDataSchemaData(), $pageSchemaData[0]);
            }
        } else if ($pageSchemaData) {
            $schemaData = $pageSchemaData;
        } else if ($includeSiteSchemaData) {
            $config = $this->owner->getSocialMetaConfig();
            $schemaData = $config->getMicroDataSchemaData();
        }
        if ($schemaData) {
            $options = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
            if (Config::inst()->get(self::class, 'minify_jsonld') === false) {
                $options = $options | JSON_PRETTY_PRINT;
            }
            $socialMetaTags[] = HTML::createTag(
                'script',
                ['type' =>  'application/ld+json'],
                json_encode($schemaData, $options)
            );
        }

        if ($this->owner->hasMethod('updateSocialMetaTags')) {
            $socialMetaTags = $this->owner->updateSocialMetaTags($socialMetaTags);
        }

        $tagString .= "\n" . implode("\n", $socialMetaTags);

        $extraMeta = $this->owner->getSocialMetaValue('ExtraMeta');
        if ($extraMeta) {
            $tagString .= "\n" . $extraMeta;
        }

    }

    public function getSocialMetaValue($key, $skipController = false)
    {
        if (!$skipController && $controller = Controller::curr()) {

            if ($controller->hasMethod('getSocialMetaObject')) {

                if ($object = $controller->getSocialMetaObject()) {

                    if ($object->hasMethod('getSocialMeta' . $key) && ($value = $object->{'getSocialMeta' . $key}()) && $value !== false) {
                        return $value;
                    }
                    if ($object->hasMethod('getDefaultSocialMeta' . $key) && ($value = $object->{'getDefaultSocialMeta' . $key}()) && $value !== false) {
                        return $value;
                    }
                }
            }
            if ($controller->hasMethod('getSocialMeta' . $key) && ($value = $controller->{'getSocialMeta' . $key}()) && $value !== false) {
                return $value;
            }
        }

        if ($this->owner->hasMethod('getSocialMeta' . $key) && ($value = $this->owner->{'getSocialMeta' . $key}()) && $value !== false) {
            return $value;
        }

        if ($this->owner->hasMethod('getDefaultSocialMeta' . $key) && ($value = $this->owner->{'getDefaultSocialMeta' . $key}()) && $value !== false) {
            return $value;
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

        if ($this->owner->config()->get('meta_description_fallback_to_site')) {
            $config = $this->owner->getSocialMetaConfig();
            return $config->getSocialMetaValue('SiteDescription');
        }

        return null;
    }

    public function getDefaultSocialMetaCanonicalURL()
    {
        return $this->owner->MetaCanonicalURL ?: preg_replace('/home\/$/i', '', $this->owner->AbsoluteLink());
    }

    public function getDefaultSocialMetaExtraMeta()
    {
        return null;
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
            $config = Config::inst()->get(self::class, 'image_size_twitter');
            if ($config && isset($config['width']) && isset($config['height'])) {
                if ($image->hasMethod('FocusFill')) {
                    return $image->FocusFill($config['width'], $config['height']);
                }
                return $image->Fill($config['width'], $config['height']);
            }
        }
        return null;
    }

    public function getDefaultSocialMetaTwitterImageURL()
    {
        $image = $this->owner->getSocialMetaValue('TwitterImage');
        return ($image) ? $image->getAbsoluteURL() : null;
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
        return preg_replace('/home\/$/i', '', $this->owner->AbsoluteLink());
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
            $config = Config::inst()->get(self::class, 'image_size_opengraph');
            if ($config && isset($config['width']) && isset($config['height'])) {
                if ($image->hasMethod('FocusFill')) {
                    return $image->FocusFill($config['width'], $config['height']);
                }
                return $image->Fill($config['width'], $config['height']);
            }
        }
        return null;
    }

    public function getDefaultSocialMetaOpenGraphImageURL()
    {
        $image = $this->owner->getSocialMetaValue('OpenGraphImage');
        return ($image) ? $image->getAbsoluteURL() : null;
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

    public function getDefaultSocialMetaOpenGraphSeeAlsoEntries()
    {
        $config = $this->owner->getSocialMetaConfig();
        return $config->getSocialMetaValue('ProfilePages');
    }

    public function getDefaultSocialMetaSchemaData()
    {
        return null;
    }

    /**
     * Mark the page to include site jsonld data on this page
     *
     * @param bool $include
     */
    public function setIncludeSiteSchemaData(bool $include)
    {
        $this->owner->include_site_jsonld_override = $include;
    }

    public function getIncludeSiteSchemaData()
    {
        $currentLink = trim($this->owner->Link(), '/');
        if (
            isset($this->owner->include_site_jsonld_override)
        ) {
            return $this->owner->include_site_jsonld_override;
        } else if (
            $this->owner->config()->include_site_jsonld == self::INCLUDE_SITE_JSONLD_HOME
            && ($currentLink == '' || $currentLink === RootURLController::get_homepage_link())
        ) {
            return true;
        } else if (
            $this->owner->config()->include_site_jsonld == self::INCLUDE_SITE_JSONLD_ALL
        ) {
            return true;
        }
        return false;
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
