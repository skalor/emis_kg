$(document).ready(function () {
	ControllerAction.init();

	/**
	 * InstitutionClassesCheckFormValue
	 * When new classes input - form disabled and validation
	 * Added Bakay
	 */
	InstitutionClassesCheckFormValue.init();
});

var ControllerAction = {
	init: function () {
		this.fieldMapping();
	},

	fieldMapping: function (obj) {
		if (obj == undefined) {
			$('[field-target]').each(function () {
				$($(this).attr('field-target')).val($(this).attr('field-value'));
				if ($(this).prop('tagName') == 'A') {
					$(this).attr('href', '#');
				}
			});
		} else {
			$($(obj).attr('field-target')).val($(obj).attr('field-value'));
			if ($(obj).prop('tagName') == 'A') {
				$(obj).attr('href', '#');
			}
		}
	}
};

// Added Bakay
// Check valid Institution class Form
var InstitutionClassesCheckFormValue = {
	// Run class ClassesCheckFormValue checker
	init: function () {
		$('.input-form-class').each(function (index) {
			let letSelectFormName = "#multiclasses-" + index + "-name";
			if ($(letSelectFormName).length) {
				let input = $(letSelectFormName);
				if (/^[0-9]{1,2} [A-zА-я-]{1,2}$/.test(input.val())) {
					let lastEntry = '';
					let inputLength = input.val().length;
					input.on("keyup", function (e) {
						this.value = capitalize(this.value);
						let targetValue = $(e.currentTarget).attr('value');
						if (inputLength > 3) {
							input.prop('maxlength', '5');
						} else {
							input.prop('maxlength', '4');
						}
						targetValue = targetValue.substring(0, targetValue.length - 1);
						let targetValueLength = targetValue.length;
						if (checkChanges(targetValueLength, targetValue, this.value)) {
							this.value = targetValue + lastEntry;
						} else {
							lastEntry = this.value.slice(targetValueLength)
						}
					});
				}
			}
		});

		// Added Bakay
		// Capitalize class text
		function capitalize(str)  {
			return str.replace(/(?:^|\s|["'([{])+\S/g, match => match.toUpperCase());
		}
		// Added Bakay
		// Capitalize class text
		function checkChanges(targetValueLength, targetValue, inputValue) {
			for (let i = 0; i < targetValueLength; i++) {
				if (targetValue[i] !== inputValue[i])
					return true;
			}
			return false;
		}
	}
};
