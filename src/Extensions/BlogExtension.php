<?php

namespace Innoweb\SocialMeta\Extensions;

use SilverStripe\Control\Controller;
use SilverStripe\CMS\Controllers\ContentController;
use SilverStripe\Core\Config\Config;
use SilverStripe\CMS\Model\SiteTree;

class BlogExtension extends \SilverStripe\CMS\Model\SiteTreeExtension
{

    public function getSocialMetaCanonicalURL()
    {
        $page = '';
        $controller = Controller::curr();
        $posts = $controller->PaginatedList();
        if (($pageNum = (int)$posts->getPageStart()) > 0) {
            $page = '?' . $posts->getPaginationGetVar() . '=' . $pageNum;
        }
        if ($controller->getArchiveYear() || $controller->getArchiveMonth() || $controller->getArchiveDay()) {
            $year = $controller->getArchiveYear();
            $month = $controller->getArchiveMonth();
            $day = $controller->getArchiveDay();
            if ($day && $month && $year) {
                return $controller->join_links($controller->AbsoluteLink(), "archive", $year, $month, $day, $page);
            } elseif ($month && $year) {
                return $controller->join_links($controller->AbsoluteLink(), "archive", $year, $month, $page);
            } elseif ($year) {
                return $controller->join_links($controller->AbsoluteLink(), "archive", $year, $page);
            }
        } elseif ($category = $controller->getCurrentCategory()) {
            return $controller->join_links($controller->AbsoluteLink(), "category", $category->URLSegment, $page);
        } elseif ($tag = $controller->getCurrentTag()) {
            return $controller->join_links($controller->AbsoluteLink(), "tag", $tag->URLSegment, $page);
        } elseif (strlen($page) > 0) {
            return $controller->join_links($controller->AbsoluteLink(), $page);
        }
        return null;
    }
    
    public function getSocialMetaTitle()
    {
        $config = $this->owner->getSocialMetaConfig();
        $siteName = $config->getSocialMetaValue('SiteName');
        $divider = Config::inst()->get(SiteTree::class, 'title_divider');
        
        $controller = Controller::curr();
        if ($category = $controller->getCurrentCategory()) {
            if ($category->MetaTitle) {
                return $category->MetaTitle;
            }
            if ($category->Title) {
                return $category->Title . $divider . $siteName;
            }
        } elseif ($tag = $controller->getCurrentTag()) {
            if ($tag->MetaTitle) {
                return $tag->MetaTitle;
            }
            if ($tag->Title) {
                return $tag->Title . $divider . $siteName;
            }
        }
        
        return $this->owner->getDefaultSocialMetaTitle();
    }
    
    public function getSocialMetaDescription()
    {
        $controller = Controller::curr();
        if (is_a($controller, ContentController::class)) {
            if (($category = $controller->getCurrentCategory()) && $category->MetaDescription) {
                return $category->MetaDescription;
            } elseif (($tag = $controller->getCurrentTag()) && $tag->MetaDescription) {
                return $tag->MetaDescription;
            }
        }
        return $this->owner->getDefaultSocialMetaDescription();
    }
    
}
