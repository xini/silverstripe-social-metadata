(function($) {
	
	$('input#Form_EditForm_MicroDataEnableCoordinates').entwine({
		onmatch: function(e) {
			this._super();
			this.toggle();
		},
		onchange: function(e) {
			this._super();
			this.toggle();
		},
		toggle: function () {
			if ($(this).is(':checked')) {
				$('#Form_EditForm_MicroDataGoogleMapsAPIKey_Holder').show();
				$('#Form_EditForm_SiteConfig_MicroDataLocationLatitude_MicroDataLocationLongitude_Holder').show();
				$('#Form_EditForm_MicroDataCoordinatesInfo').show();
			} else {
				$('#Form_EditForm_MicroDataGoogleMapsAPIKey_Holder').hide();
				$('#Form_EditForm_SiteConfig_MicroDataLocationLatitude_MicroDataLocationLongitude_Holder').hide();
				$('#Form_EditForm_MicroDataCoordinatesInfo').hide();
			}
		}
	});
	
	$('select#Form_EditForm_MicroDataType').entwine({
		onmatch: function(e) {
			this._super();
			this.toggle();
		},
		onchange: function(e) {
			this._super();
			this.toggle();
		},
		toggle: function () {
			// hide all
			$('#MicroDataOpeningHoursDays').hide();
			$('#Form_EditForm_MicroDataOpeningHoursTimeOpen_Holder').hide();
			$('#Form_EditForm_MicroDataOpeningHoursTimeClose_Holder').hide();
			$('#MicroDataPaymentAccepted').hide();
			$('#Form_EditForm_MicroDataEventLocationName_Holder').hide();
			$('#Form_EditForm_MicroDataEventLocationWebsite_Holder').hide();
			$('#Form_EditForm_MicroDataEventStart_Holder').hide();
			$('#Form_EditForm_MicroDataEventEnd_Holder').hide();
			$('#Form_EditForm_MicroDataEmail_Holder').hide();
			// show by type
			if ($(this).val() == 'Organization') {
				$('#Form_EditForm_MicroDataEmail_Holder').show();
			} else if ($(this).val() == 'LocalBusiness') {
				$('#MicroDataOpeningHoursDays').show();
				$('#Form_EditForm_MicroDataOpeningHoursTimeOpen_Holder').show();
				$('#Form_EditForm_MicroDataOpeningHoursTimeClose_Holder').show();
				$('#MicroDataPaymentAccepted').show();
				$('#Form_EditForm_MicroDataEmail_Holder').show();
			} else if ($(this).val() == 'Event') {
				$('#Form_EditForm_MicroDataEventLocationName_Holder').show();
				$('#Form_EditForm_MicroDataEventLocationWebsite_Holder').show();
				$('#Form_EditForm_MicroDataEventStart_Holder').show();
				$('#Form_EditForm_MicroDataEventEnd_Holder').show();
			}
		}
	});
	
})(jQuery);