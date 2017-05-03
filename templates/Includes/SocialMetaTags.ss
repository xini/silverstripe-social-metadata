<% cached 'social-meta', $ID, $List(SiteTree).max(LastEdited), $List(SiteTree).count(), $List(Member).max(LastEdited), $List(Member).count(), $SiteConfig.LastEdited %>
<%-- Schema.org markup for Google+ --%>
<% if $SocialMetaPageTitle %><meta itemprop="name" content="$SocialMetaPageTitle"><% end_if %>
<% if $SocialMetaDescription %><meta itemprop="description" content="$SocialMetaDescription.NoHTML"><% end_if %>
<%-- Twitter Card data --%>
<meta name="twitter:card" content="<% if $SocialMetaImage && $SocialMetaImage.Width >= 280 %>summary_large_image<% else %>summary<% end_if %>">
<% if $SocialMetaTwitterHandle %><meta name="twitter:site" content="$SocialMetaTwitterHandle"><% end_if %>
<% if $SocialMetaPageTitle %><meta name="twitter:title" content="$SocialMetaPageTitle"><% end_if %>
<% if $SocialMetaDescription %><meta name="twitter:description" content="$SocialMetaDescription.NoHTML"><% end_if %>
<%-- Open Graph data --%>
<% if $SocialMetaFacebookAppID %><meta property="fb:app_id" content="$SocialMetaFacebookAppID" /><% end_if %>
<% if $SocialMetaFacebookLocale %><meta property="og:locale" content="$SocialMetaFacebookLocale" /><% end_if %>
<% if $SocialMetaPageTitle %><meta property="og:title" content="$SocialMetaPageTitle" /><% end_if %>
<% if $SocialMetaFacebookType %><meta property="og:type" content="$SocialMetaFacebookType" /><% end_if %>
<% if $SocialMetaPageURL %><meta property="og:url" content="$SocialMetaPageURL" /><% end_if %>
<% if $SocialMetaDescription %><meta property="og:description" content="$SocialMetaDescription.NoHTML" /><% end_if %>
<% if $SocialMetaSiteName %><meta property="og:site_name" content="$SocialMetaSiteName" /><% end_if %>
<% if $FacebookType == "article" %>
	<% if $SocialMetaFacebookPage %><meta property="article:publisher" content="$SocialMetaFacebookPage" /><% end_if %>
	<% if $SocialMetaPublicationDate %><meta property="article:published_time" content="$SocialMetaPublicationDate" /><% end_if %>
	<% if $SocialMetaModificationDate %><meta property="article:modified_time" content="$SocialMetaModificationDate" /><% end_if %>
	<% if $SocialMetaSection %><meta property="article:section" content="$SocialMetaSection" /><% end_if %>
	<% if $SocialMetaTags %><% loop $SocialMetaTags %><meta property="article:tag" content="$Title" /><% end_loop %><% end_if %>
<% end_if %>
<% if $SocialMetaFacebookAdmins && $SocialMetaFacebookAdmins.Items %><% loop $SocialMetaFacebookAdmins.Items %><meta property="fb:admins" content="$Value" /><% end_loop %><% end_if %>
<%-- Authors --%><% if $SocialMetaAuthors %><% loop $SocialMetaAuthors %>
<% if $GooglePlusProfile %><link rel="author" href="$GooglePlusProfile" /><% end_if %>
<% if $FacebookProfile %><meta property="article:author" content="$FacebookProfile" /><% end_if %>
<% if $TwitterHandle %><meta name="twitter:creator" content="$TwitterHandle"><% end_if %><% end_loop %>
<meta name="author" content="<% loop $SocialAuthors %><% if $Pos > 1 %>, <% end_if %>$Name<% end_loop %>">
<% end_if %>
<%-- Images --%><% if $SocialMetaImage %><meta itemprop="image" content="$SocialMetaImage.CroppedFocusedImage(1200,630).AbsoluteURL">
<meta name="twitter:image" content="$SocialMateImage.CroppedFocusedImage(1200,630).AbsoluteURL">
<meta property="og:image" content="$SocialMetaImage.CroppedFocusedImage(1200,630).AbsoluteURL" /><% end_if %>
<script type="application/ld+json">
{
	"@context": "http://schema.org",
	"@type": "$SocialMetaSchemaType"
	<% if $SocialMetaSiteName %>, "name": "$SocialMetaSiteName"<% end_if %>
	<% if $SocialMetaDescription %>, "description": "$SocialMetaDescription"<% end_if %>
	<% if $SocialMetaImage %>, "image": "$SocialMetaImage.CroppedFocusedImage(1200,630).AbsoluteURL"<% end_if %>
	, "url": "$SocialMetaSiteURL"
	<% if $SocialMetaEventStart %>, "startDate": "$SocialMetaEventStart.Rfc3339"<% end_if %>
	<% if $SocialMetaEventEnd %>, "endDate": "$SocialMetaEventEnd.Rfc3339"<% end_if %>
	<% if $SocialMetaSchemaType == "Event" %>
		, "location": {
			"@type": "Place"
			<% if $SocialMetaEventLocationName %>, "name": "$SocialMetaEventLocationName"<% end_if %>
			<% if $ SocialMetaEventLocationWebsite %>, "sameAs": "$SocialMetaEventLocationWebsite"<% end_if %>
	<% end_if %>
	<% if $SocialMetaStreetAddress || $SocialMetaPOBoxNumber || $SocialMetaCity || $SocialMetaPostCode %>
		, "address": {
			"@type": "PostalAddress"
			<% if $SocialMetaCountry %>, "addressCountry": "$SocialMetaCountry"<% end_if %>
			<% if $SocialMetaCity %>, "addressLocality": "$SocialMetaCity"<% end_if %>
			<% if $SocialMetaRegion %>, "addressRegion": "$SocialMetaRegion"<% end_if %>
			<% if $SocialMetaPostCode %>, "postalCode":"$SocialMetaPostCode"<% end_if %>
			<% if $SocialMetaPOBoxNumber %>, "postOfficeBoxNumber": "$SocialMetaPOBoxNumber"<% end_if %>
			<% if $SocialMetaStreetAddress %>, "streetAddress": "$SocialMetaStreetAddress"<% end_if %>
		}
	<% end_if %>
	<% if $SocialMetaPhone %>, "telephone": "$SocialMetaPhone"<% end_if %>
	<% if $SocialMetaFax %>, "faxNumber": "$SocialMetaFax"<% end_if %>
	<% if $SocialMetaEmail %>, "email": "$SocialMetaEmail"<% end_if %>
	<% if $SocialMetaEnableCoordinates && $SocialMetaLocationLatitude && $SocialMetaLocationLongitude %>
		, "geo": {
			"@type": "GeoCoordinates",
			"latitude": "$SocialMetaLocationLatitude",
			"longitude": "$SocialMetaLocationLongitude"
		}
	<% end_if %>
	<% if $SocialMetaSchemaType == "Event" %>
		}
	<% end_if %>
	<% if $SocialMetaOpeningHoursDays %>, "openingHours": "$SocialMetaOpeningHoursDays<% if $SocialMetaOpeningHoursTimeOpen && $SocialMetaOpeningHoursTimeClose %> $SocialMetaOpeningHoursTimeOpen-$SocialMetaOpeningHoursTimeClose<% end_if %>"<% end_if %>
	<% if $SocialMetaPaymentAccepted %>, "paymentAccepted": "$SocialMetaPaymentAccepted"<% end_if %>
	<% if $SocialMetaProfilePages %>
		, "sameAs" : [
			<% loop $SocialMetaProfilePages %>"$URL"<% if not $Last %>,<% end_if %><% end_loop %>
		]
	<% end_if %>
}
</script>
<% end_cached %>