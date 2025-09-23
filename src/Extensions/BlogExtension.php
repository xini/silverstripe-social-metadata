<?php

namespace Innoweb\SocialMeta\Extensions;

use SilverStripe\Blog\Model\BlogController;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Control\Controller;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Extension;

class BlogExtension extends Extension
{

    public function getSocialMetaTitle()
    {
        $config = $this->getOwner()->getSocialMetaConfig();
        $siteName = $config->getSocialMetaValue('SiteName');
        $divider = Config::inst()->get(SiteTree::class, 'title_divider');

        $controller = Controller::curr();
        if (is_a($controller, BlogController::class)) {
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
        }

        return null;
    }

    public function getSocialMetaDescription()
    {
        $controller = Controller::curr();
        if (is_a($controller, BlogController::class)) {
            if (($category = $controller->getCurrentCategory()) && $category->MetaDescription) {
                return $category->MetaDescription;
            } elseif (($tag = $controller->getCurrentTag()) && $tag->MetaDescription) {
                return $tag->MetaDescription;
            }
        }

        return null;
    }

    public function getSocialMetaCanonicalURL()
    {
        $controller = Controller::curr();
        if (is_a($controller, BlogController::class)) {
            // get pagination page
            $page = '';
            $posts = $controller->PaginatedList();
            if (($pageNum = (int)$posts->getPageStart()) > 0) {
                $page = '?' . $posts->getPaginationGetVar() . '=' . $pageNum;
            }

            // get specific URL for archive, categories and tags
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
                if ($category->MetaCanonicalURL) {
                    return $category->MetaCanonicalURL;
                } else {
                    return $controller->join_links($controller->AbsoluteLink(), "category", $category->URLSegment, $page);
                }
            } elseif ($tag = $controller->getCurrentTag()) {
                if ($tag->MetaCanonicalURL) {
                    return $tag->MetaCanonicalURL;
                } else {
                    return $controller->join_links($controller->AbsoluteLink(), "tag", $tag->URLSegment, $page);
                }
            } elseif (strlen($page) > 0) {
                return $controller->join_links($controller->AbsoluteLink(), $page);
            }
        }

        return null;
    }

    public function getSocialMetaImage()
    {
        $controller = Controller::curr();
        if (is_a($controller, BlogController::class)) {
            if ($category = $controller->getCurrentCategory()) {
                if (($image = $category->MetaImage()) && $image->exists()) {
                    return $image;
                }
            } elseif ($tag = $controller->getCurrentTag()) {
                if (($image = $tag->MetaImage()) && $image->exists()) {
                    return $image;
                }
            }
        }

        return null;
    }

    public function getSocialMetaExtraMeta()
    {
        $controller = Controller::curr();
        if (is_a($controller, BlogController::class)) {
            if ($category = $controller->getCurrentCategory()) {
                if ($category->ExtraMeta) {
                    return $category->ExtraMeta;
                }
            } elseif ($tag = $controller->getCurrentTag()) {
                if ($tag->ExtraMeta) {
                    return $tag->ExtraMeta;
                }
            }
        }

        return null;
    }
}
