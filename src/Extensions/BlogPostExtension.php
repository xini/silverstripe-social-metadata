<?php

namespace Innoweb\SocialMeta\Extensions;

class BlogPostExtension extends \SilverStripe\CMS\Model\SiteTreeExtension
{
    public function getSocialMetaDescription()
    {
        if ($this->owner->Summary) {
            return $this->owner->obj('Summary')->Plain();
        } else if ($this->owner->Excerpt()) {
            return $this->owner->Excerpt();
        }
        return $this->owner->getDefaultSocialMetaDescription();
    }

    public function getSocialMetaAuthors()
    {
        return $this->owner->getCredits() ?: null;
    }

    public function getSocialMetaImage()
    {
        if ($this->owner->FeaturedImageID && $this->owner->FeaturedImage()) {
            return $this->owner->FeaturedImage();
        }
        return $this->owner->getDefaultSocialMetaImage();
    }

    public function getSocialMetaPublicationTime()
    {
        if ($this->owner->PublishDate) {
            return $this->owner->obj('PublishDate')->Rfc3339();
        }
        return $this->owner->getDefaultSocialMetaPublicationTime();
    }

    public function getSocialMetaCategory()
    {
        if ($this->owner->Categories()) {
            foreach ($this->owner->Categories() as $category) {
                return $category->Title;
            }
        }
        return null;
    }

    public function getSocialMetaTags()
    {
        if ($this->owner->Tags()) {
            $tags = [];
            foreach ($this->owner->Tags() as $tag) {
                $tags[] = $tag->Title;
            }
            return $tags;
        }
        return null;
    }

    public function getSocialMetaSchemaData()
    {
        $data = array(
            '@context' 	=>	'http://schema.org',
            '@type' 	=>	'Article',
            'headline'	=>	$this->owner->getSocialMetaValue('Title')
        );

        $config = $this->owner->getSocialMetaConfig();

        $data['datePublished'] = $this->owner->getSocialMetaValue('PublicationTime');
        $data['dateModified'] = $this->owner->getSocialMetaValue('ModificationTime');
        $data['url'] = $this->owner->getSocialMetaValue('CanonicalURL');
        $data['publisher'] = $config->getSocialMetaValue('SiteName');

        if ($this->owner->getSocialMetaValue('AuthorsNames')) {
            $data['author'] = [];
            foreach ($this->owner->getSocialMetaValue('AuthorsNames') as $name) {
                $data['author'][] = [
                    '@type' =>  'Person',
                    'name'  =>  $name
                ];
            }
        } else {
            $data['author'] = [
                '@type' =>  'Organization',
                'name'  =>  $config->getSocialMetaValue('SiteName'),
                'url'   =>  $config->getSocialMetaValue('SiteURL')
            ];
        }
        
        if ($this->owner->getSocialMetaValue('Description')) {
            $data['description'] = $this->owner->getSocialMetaValue('Description');
        }

        $data['articleBody'] = $this->owner->obj('Content')->Plain();

        $image = $this->owner->getSocialMetaValue('Image');
        if ($image && $image->exists()) {
            $data['image'] = [
                '@type'     =>  'ImageObject',
                'url'       =>  $image->AbsoluteLink(),
                'width'     =>  $image->getWidth() . 'px',
                'height'    =>  $image->getHeight() . 'px'
            ];
        }

        $attributes = null;
        $categories = $this->owner->Categories();
        if ($categories && $categories->Count()) {
            $attributes = $categories;
        } else {
            $tags = $this->owner->Tags();
            if ($tags && $tags->Count()) {
                $attributes = $tags;
            }
        }
        if ($attributes) {
            $data["articleSection"] = [];
            foreach ($attributes as $attribute) {
                $data["articleSection"][] = $attribute->Title;
            }
        }

        return $data;
    }
}
