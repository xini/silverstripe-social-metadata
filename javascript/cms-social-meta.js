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
			$('#Form_EditForm_MicroDataTypeSpecific').val("").trigger("liszt:updated");
		},
		toggle: function () {
			// hide all
			$('#Form_EditForm_OpeningHours').hide();
			$('#MicroDataPaymentAccepted').hide();
			$('#Form_EditForm_MicroDataEventLocationName_Holder').hide();
			$('#Form_EditForm_MicroDataEventLocationWebsite_Holder').hide();
			$('#Form_EditForm_MicroDataEventStart_Holder').hide();
			$('#Form_EditForm_MicroDataEventEnd_Holder').hide();
			$('#Form_EditForm_MicroDataEmail_Holder').hide();
			$('#Form_EditForm_MicroDataTypeSpecific option[data-type!="All"]').prop("disabled", true).hide();
			// show by type
			if ($(this).val() == 'Organization') {
				$('#Form_EditForm_MicroDataEmail_Holder').show();
				$('#Form_EditForm_MicroDataTypeSpecific option[data-type="Organization"]').prop("disabled", false).show();
				$('#Form_EditForm_MicroDataTypeSpecific').trigger("liszt:updated");
			} else if ($(this).val() == 'LocalBusiness') {
				$('#Form_EditForm_OpeningHours').show();
				$('#MicroDataPaymentAccepted').show();
				$('#Form_EditForm_MicroDataEmail_Holder').show();
				$('#Form_EditForm_MicroDataTypeSpecific option[data-type="LocalBusiness"]').prop("disabled", false).show();
				$('#Form_EditForm_MicroDataTypeSpecific').trigger("liszt:updated");
			} else if ($(this).val() == 'Event') {
				$('#Form_EditForm_MicroDataEventLocationName_Holder').show();
				$('#Form_EditForm_MicroDataEventLocationWebsite_Holder').show();
				$('#Form_EditForm_MicroDataEventStart_Holder').show();
				$('#Form_EditForm_MicroDataEventEnd_Holder').show();
				$('#Form_EditForm_MicroDataTypeSpecific option[data-type="Event"]').prop("disabled", false).show();
				$('#Form_EditForm_MicroDataTypeSpecific').trigger("liszt:updated");
			}
		}
	});
	
	$('input#Form_EditForm_MicroDataAdditionalLocations').entwine({
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
				$('#Form_EditForm_AdditionalLocations').show();
			} else {
				$('#Form_EditForm_AdditionalLocations').hide();
			}
		}
	});

	$('input#Form_ItemEditForm_MicroDataEnableCoordinates').entwine({
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
				$('#Form_ItemEditForm_SocialMetaBusinessLocation_MicroDataLocationLatitude_MicroDataLocationLongitude_Holder').show();
				$('#Form_ItemEditForm_MicroDataCoordinatesInfo').show();
			} else {
				$('#Form_ItemEditForm_SocialMetaBusinessLocation_MicroDataLocationLatitude_MicroDataLocationLongitude_Holder').hide();
				$('#Form_ItemEditForm_MicroDataCoordinatesInfo').hide();
			}
		}
	});
	
	$('select#Form_ItemEditForm_MicroDataType').entwine({
		onmatch: function(e) {
			this._super();
			this.toggle();
		},
		onchange: function(e) {
			this._super();
			this.toggle();
			$('#Form_ItemEditForm_MicroDataTypeSpecific').val("").trigger("liszt:updated");
		},
		toggle: function () {
			// hide all
			$('#Form_ItemEditForm_OpeningHours').hide();
			$('#MicroDataPaymentAccepted').hide();
			$('#Form_EditForm_MicroDataTypeSpecific option[data-type!="All"]').prop("disabled", true).hide();
			// show by type
			if ($(this).val() == 'Organization') {
				$('#Form_EditForm_MicroDataTypeSpecific option[data-type="Organization"]').prop("disabled", false).show();
				$('#Form_EditForm_MicroDataTypeSpecific').trigger("liszt:updated");
			} else if ($(this).val() == 'LocalBusiness') {
				$('#Form_ItemEditForm_OpeningHours').show();
				$('#MicroDataPaymentAccepted').show();
				$('#Form_EditForm_MicroDataTypeSpecific option[data-type="LocalBusiness"]').prop("disabled", false).show();
				$('#Form_EditForm_MicroDataTypeSpecific').trigger("liszt:updated");
			}
		}
	});
	
})(jQuery);