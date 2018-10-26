<?php

namespace Innoweb\SocialMeta\Extensions;

use BurnBright\ExternalURLField\DBExternalURL;
use BurnBright\ExternalURLField\ExternalURLField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataExtension;

class AuthorExtension extends DataExtension
{
    private static $db = [
        'FacebookProfileURL'    =>  DBExternalURL::class,
        'TwitterHandle'         =>  'Varchar(50)'
    ];

    public function updateCMSFields(FieldList $fields)
    {
        $fields->addFieldsToTab(
            'Root.Main',
            [
                ExternalURLField::create(
                    'FacebookProfileURL',
                    _t("ArticleAuthorRole.FACEBOOKPROFILE", 'Facebook Profile (full URL)')
                ),
                TextField::create(
                    'TwitterHandle',
                    _t("ArticleAuthorRole.TWITTERHANDLE", 'Twitter Handle (@xyz)')
                )
            ],
            'DirectGroups'
        );
    }
}