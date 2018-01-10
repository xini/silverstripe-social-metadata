<?php
class SocialMetaAuthorExtension extends DataExtension {
    
    private static $db = array(
        'GooglePlusProfile' => 'Varchar(255)',
        'FacebookProfile' => 'Varchar(255)',
        'TwitterHandle' => 'Varchar(50)',
    );
    
    public function updateCMSFields(FieldList $fields) {
        $fields->addFieldToTab(
            'Root.Main',
            new TextField("GooglePlusProfile", _t("ArticleAuthorRole.GOOGLEPROFILE", 'Google+ Profile (full URL)')),
            'DirectGroups'
        );
        $fields->addFieldToTab(
            'Root.Main',
            new TextField("FacebookProfile", _t("ArticleAuthorRole.FACEBOOKPROFILE", 'Facebook Profile (full URL)')),
            'DirectGroups'
        );
        $fields->addFieldToTab(
            'Root.Main',
            new TextField("TwitterHandle", _t("ArticleAuthorRole.TWITTERHANDLE", 'Twitter Handle (@xyz)')),
            'DirectGroups'
        );
    }
    
}