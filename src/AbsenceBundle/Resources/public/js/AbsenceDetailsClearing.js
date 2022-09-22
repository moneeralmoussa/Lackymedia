$(document).ready(function() {});

var publicHolidays = [];

function isPublicHoliday(start) {
	if (publicHolidays.indexOf(start.format("YYYY-MM-DD")) > -1) {
		return true;
	}
	if (start.day() == 6 || start.day() == 0) {
		return true;
	}
	return false;
}

function transformAbsenceDetailClearingToCheckbox(absenceDetailClearing) {
	var output = 0;
	switch (absenceDetailClearing.toString()) {
		case "1.0":
			output = 1;
			break;
		case "0.5":
			output = 2;
			break;
		case "0.0":
			output = 3;
			break;
	}
	return output;
}

function calcDaysFromRadio() {
	var days = 0.0;
	_.each($('#day-option-component input[type=radio]:checked'), function(radiobutton) {
		val = $(radiobutton).val();
		switch (Number(val)) {
			case 1:
				days += 1.0;
				break;
			case 2:
				days += 0.5;
				break;
			case 3:
				days += 0.0;
				break;
		}
	});
	$('#absencebundle_absence_day').val(days);
}

function generateDays(employee, start, end) {

	$.ajax({
		url: "/calendar/publicholidays/json",
		async: false,
		success: function(data) {
			_.forEach(data, function(value) {
				if (value.publicHoliday == true) {
					vstart = moment(value.start);
					if ((vstart.isBefore(end) && vstart.isAfter(start))) {
						publicHolidays.push(value.start);
					}
				}
			});
		}
	});

	var absencedetailsclearing = [];
	$.ajax({
		url: "/absence/absencedetailsclearing",
		method: "POST",
		async: false,
		data: {
			employee: employee,
			start: start,
			end: end,
		},
		success: function(data) {
			absencedetailsclearing = data;
		}
	});

	const range = moment().range(moment(start).startOf('day'), moment(end).startOf('day').subtract(1, 'day'));
	const days = Array.from(range.by('days')).map(m => [m.format('YYYY-MM-DD'), m]);

	if (range >= 0) {
		var html_days = '';
		var html_days_tab_panel = '';
		var button_color = '';
		var checked_holiday = '';
		var checked_public_holiday = '';
		_.forEach(days, function(value) {
			if (isPublicHoliday(value[1])) {
				button_color = 'btn-danger';
				checked_public_holiday = 'checked="checked"';
			} else {
				button_color = 'btn-success';
				checked_holiday = 'checked="checked"';
			}

			if (!isPublicHoliday(value[1])) {
				var result = _.filter(absencedetailsclearing, function(item) {
					return moment(item.date.date).format('YYYY-MM-DD') == value[0];
				});

				if (!!result[0]) {
					var option_value = transformAbsenceDetailClearingToCheckbox(result[0].dayDetail);
				}

				if (option_value == 2) {
					button_color = 'btn-half';
				} else if (option_value == 3) {
					button_color = 'btn-default';
				}

				html_days_tab_panel += '<div role="tabpanel" class="tab-pane" id="' + value[0] + '">\
										<input type="radio" name="absenceoptions[' + value[0] + ']" ' + checked_holiday + ' value="1" ' + (option_value == 1 ? 'checked' : '') + '> Urlaub<br>\
										<input type="radio" name="absenceoptions[' + value[0] + ']" value="2" ' + (option_value == 2 ? 'checked' : '') + '> Halber Tag<br>\
										<input type="radio" name="absenceoptions[' + value[0] + ']" ' + checked_public_holiday + ' value="3" ' + (option_value == 3 ? 'checked' : '') + '> kein Urlaub\
									</div>';
			} else {
				html_days_tab_panel += '<div role="tabpanel" class="tab-pane" id="' + value[0] + '">\
											<p>Entweder ein Feiertag oder Wochenende!</p>\
										</div>';
			}
			html_days += '<li class="btn btn-flat ' + button_color + '" style="display:inline-block;"><a href="#' + value[0] + '" style="color:#fff;" aria-controls="profile" role="tab" data-toggle="tab">' + moment(value[0]).format('DD.MM') + '</a></li>';
			checked_public_holiday = '';
			checked_holiday = '';
		});
		$('#day-option-component').html('	  <div class="panel panel-default" style="overflow:hidden;">\
												  <div class="panel-body">\
													  <label>Urlaubstage anpassen</label>\
													<div class="form-group">\
														<ul class="btn-group" role="group" aria-label="..." id="days" style="display:flex;flex:1;overflow: auto;padding-left:0">\
														</ul>\
													</div>\
													<div class="tab-content" id="days-tab-panel"></div>\
												  </div>\
												</div>');
		$(document).find('#days').html(html_days);
		$(document).find('#days-tab-panel').html(html_days_tab_panel);
	}

	calcDaysFromRadio();

}
$(document).on('change', 'input[type=radio]', function() {

	var active_button = $(document).find('#day-option-component .btn.active');

	if (this.value == '1') {
		if (active_button.hasClass('btn-default') || active_button.hasClass('btn-half')) {
			active_button.removeClass('btn-default');
			active_button.removeClass('btn-half');
			active_button.addClass('btn-success');
		}
	} else if (this.value == '2') {
		if (active_button.hasClass('btn-default') || active_button.hasClass('btn-success')) {
			active_button.removeClass('btn-default');
			active_button.removeClass('btn-success');
			active_button.addClass('btn-half');
		}
	} else if (this.value == '3') {
		if (active_button.hasClass('btn-success') || active_button.hasClass('btn-half')) {
			active_button.removeClass('btn-success');
			active_button.removeClass('btn-half');
			active_button.addClass('btn-default');
		}
	}
});