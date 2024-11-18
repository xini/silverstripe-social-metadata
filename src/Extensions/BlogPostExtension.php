<?php

namespace Innoweb\SocialMeta\Extensions;

class BlogPostExtension extends \SilverStripe\CMS\Model\SiteTreeExtension
{
    public function getSocialMetaDescription()
    {
        if ($this->getOwner()->Summary) {
            return $this->getOwner()->obj('Summary')->Plain();
        } else if ($this->getOwner()->Excerpt()) {
            return $this->getOwner()->Excerpt();
        }
        return $this->getOwner()->getDefaultSocialMetaDescription();
    }

    public function getSocialMetaAuthors()
    {
        return $this->getOwner()->getCredits() ?: null;
    }

    public function getSocialMetaImage()
    {
        if (($image = $this->getOwner()->MetaImage()) && $image->exists()) {
            return $image;
        }

        if ($this->getOwner()->FeaturedImageID && $this->getOwner()->FeaturedImage()) {
            return $this->getOwner()->FeaturedImage();
        }
        return $this->getOwner()->getDefaultSocialMetaImage();
    }

    public function getSocialMetaPublicationTime()
    {
        if ($this->getOwner()->PublishDate) {
            return $this->getOwner()->obj('PublishDate')->Rfc3339();
        }
        return $this->getOwner()->getDefaultSocialMetaPublicationTime();
    }

    public function getSocialMetaCategory()
    {
        if ($this->getOwner()->Categories()) {
            foreach ($this->getOwner()->Categories() as $category) {
                return $category->Title;
            }
        }
        return null;
    }

    public function getSocialMetaTags()
    {
        if ($this->getOwner()->Tags()) {
            $tags = [];
            foreach ($this->getOwner()->Tags() as $tag) {
                $tags[] = $tag->Title;
            }
            return $tags;
        }
        return null;
    }

    public function getSocialMetaSchemaData()
    {
        $data = array(
            '@context' 	=>	'https://schema.org',
            '@type' 	=>	'Article',
            'headline'	=>	$this->getOwner()->getSocialMetaValue('Title')
        );

        $config = $this->getOwner()->getSocialMetaConfig();

        $data['datePublished'] = $this->getOwner()->getSocialMetaValue('PublicationTime');
        $data['dateModified'] = $this->getOwner()->getSocialMetaValue('ModificationTime');
        $data['url'] = $this->getOwner()->getSocialMetaValue('CanonicalURL');
        $data['publisher'] = $config->getSocialMetaValue('SiteName');

        if ($this->getOwner()->getSocialMetaValue('AuthorsNames')) {
            $data['author'] = [];
            foreach ($this->getOwner()->getSocialMetaValue('AuthorsNames') as $name) {
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

        if ($this->getOwner()->getSocialMetaValue('Description')) {
            $data['description'] = $this->getOwner()->getSocialMetaValue('Description');
        }

        $image = $this->getOwner()->getSocialMetaValue('Image');
        if ($image && $image->exists()) {
            $data['image'] = [
                '@type'     =>  'ImageObject',
                'url'       =>  $image->getAbsoluteURL(),
                'width'     =>  $image->getWidth() . 'px',
                'height'    =>  $image->getHeight() . 'px'
            ];
        }

        $attributes = null;
        $categories = $this->getOwner()->Categories();
        if ($categories && $categories->Count()) {
            $attributes = $categories;
        } else {
            $tags = $this->getOwner()->Tags();
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
