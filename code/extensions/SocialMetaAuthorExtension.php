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
    public function onBeforeWrite() {
        parent::onBeforeWrite();
        
        $this->owner->GooglePlusProfile = $this->updateLinkURL($this->owner->GooglePlusProfile);
        $this->owner->FacebookProfile = $this->updateLinkURL($this->owner->FacebookProfile);
    }
    
    private function updateLinkURL($url) {
        if($url) {
            if(
                substr($url, 0, 8) != 'https://'
                && substr($url, 0, 7) != 'http://'
                && substr($url, 0, 6) != 'ftp://'
                ) {
                    $url = 'http://' . $url;
                }
        }
        return $url;
    }
    
}