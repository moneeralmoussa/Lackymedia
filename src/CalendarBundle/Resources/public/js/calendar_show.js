function calcBusinessDays(startDate, endDate) {
	var day = moment(startDate);
	var businessDays = 0;

	while (day.isSameOrBefore(endDate, 'day')) {
		if (day.day() != 0 && day.day() != 6) businessDays++;
		day.add(1, 'd');
	}
	return businessDays;
}

function isWeekend(date) {
	return (date.day() === 6) || (date.day() === 0);
}

function generateStatisticChart(statistic, year) {

	if (_.isEmpty(statistic)) {
		statistic = {};
	}

	var start = moment([year]).startOf('year');
	var end = moment([year]).endOf('year');
	var year_days = end.diff(start, 'days') + 1;
	var year_workdays = calcBusinessDays(start, end);
	var year_weekends = year_days - year_workdays;
	var year_public_holidays = _.filter(public_holidays_json, function(o) {
		if (
			o.publicHoliday == true &&
			!isWeekend(new moment(o.start)) &&
			moment(o.start).isSame(new moment(current_view_year, 'YYYY'), 'year')
		) return o
	}).length;
	// console.log(year_public_holidays);
	var arbeitstage = {
		Arbeitstage: {
			days: year_workdays - year_public_holidays - (_.sum(_.map(statistic, 'days'))),
			color: '#34495e',
		},
		Wochenende: {
			days: year_weekends,
			color: '#E4EDEC',
		},
		Feiertage: {
			days: year_public_holidays,
			color: '#F1D4CD',
		}
	};

	_.assign(statistic, arbeitstage);
	var colors = _.map(statistic, function(a) {
		return a.color === '#f1c40f' ? a.color = '#2ECC71' : a.color;
	});


	pie = {
		datasets: [{
			data: _.map(statistic, 'days'),
			backgroundColor: colors
		}],
		labels: _.keys(statistic)
	};
	var ctx = $("#pieReasons");

	var chart = new Chart(ctx, {
		type: 'doughnut',
		data: pie,
		options: {
			legend: {
				display: true,
				position: 'bottom'
			}
		},
		centerText: {
			display: true,
			text: year_days,
		}
	});
	ctx.data('chart', chart);
}

function getRemainingMtlString(data) {
	var tagstring = '';
	if (data.remainingmtl > 1) {
		tagstring = ' Tage ';
	} else {
		tagstring = ' Tag ';
	}
	return data.remainingmtl + tagstring;
}

function reloadAbsenceRemaining(data, year) {
	console.log(year);
	$('#remainingData').html("\
  <table class='table'>\
  	<thead>\
		<tr>\
		  	<th>#</th>\
		  	<th>Titel</th>\
		  	<th>Tage</th>\
		</tr>\
  	</thead>\
	<tbody>\
		<tr>\
		  <th scope='row'>+</th>\
		  <td>Resturlaub " + (year - 1) + "</td>\
		  <td>" + data.remaining + "</td>\
		</tr>\
		<tr>\
		  <th scope='row'>+</th>\
		  <td>Urlaubstage " + year + "</td>\
		  <td>" + data.contract + "</td>\
		</tr>\
		<tr>\
		  <th scope='row'>+</th>\
		  <td>Zusätzliche Urlaubstage</td>\
		  <td>" + data.additional + "</td>\
		</tr>\
		<tr>\
		  <th scope='row'>-</th>\
		  <td>Abzügliche Urlaubstage</td>\
		  <td>" + data.substract + "</td>\
		</tr>\
		<tr>\
		  <th scope='row'>=</th>\
		  <td>Gesamte Urlaubstage " + year + "</td>\
		  <td>" + (data.remaining + data.contract + data.additional - data.substract) + "</td>\
		</tr>\
		<tr>\
		  <th scope='row'>-</th>\
		  <td>Genommene Urlaubstage " + year + "</td>\
		  <td>" + data.taken + "</td>\
		</tr>\
		<tr>\
		  <th scope='row'>=</th>\
		  <td>Übrige Urlaubstage</td>\
		  <td>" + data.sum + "</td>\
		</tr>\
	</tbody>\
</table>");
}

function preparedVacationlocks(data) {
	var datasource = [];
	_.each(data, function(value, key) {
		_.each(value, function(v) {
			datasource.push(new Date(v));
		});
	});
	return datasource;
}

function preparedDataSource(data) {
	_.each(data, function(value) {
		value.startDate = moment(value.startDate.date).toDate()
		value.endDate = moment(value.endDate.date).toDate()
	});
	return data;
}

function getRangeOfDates(start, end, key, arr = [start.startOf(key)]) {
	if (start.isAfter(end))
		throw new Error('start must precede end')
	const next = moment(start).add(1, key).startOf(key);
	if (next.isAfter(end, key))
		return arr;
	return getRangeOfDates(next, end, key, arr.concat(next));
}

$(document).ready(function() {

	Chart.Chart.pluginService.register({
		beforeDraw: function(chart) {
			if (chart.config.centerText.display !== null && typeof chart.config.centerText.display !== 'undefined' && chart.config.centerText.display) {
				drawTotals(chart);
			}
		}
	});

	function drawTotals(chart) {

		var width = chart.chart.width,
			height = chart.chart.height,
			ctx = chart.chart.ctx;

		ctx.restore();
		var fontSize = (height / 114).toFixed(2);
		ctx.font = fontSize + "em sans-serif";
		ctx.textBaseline = "middle";

		var text = chart.config.centerText.text,
			textX = Math.round((width - ctx.measureText(text).width) / 2),
			textY = ((height - chart.chart.legend.height) / 2);

		ctx.fillText(text, textX, textY);
		ctx.save();
	}

	$("[name='team']").bootstrapSwitch();

	$('#team').on('switchChange.bootstrapSwitch', function(event, state) {
		if (state === true) {
			$.ajax({
				url: route_team_json,
				success: function(data) {
					data.forEach(function(element) {
						element.startDate = moment(element.startDate.date, 'YYYY-MM-DD HH:mm').toDate();
						element.endDate = moment(element.endDate.date, 'YYYY-MM-DD HH:mm').toDate();
					});
					$('#calendar').data('calendar').setDataSource(data);
				}
			});
		} else {
			$.ajax({
				url: route_absence_json,
				success: function(data) {
					data.forEach(function(element) {
						element.startDate = moment(element.startDate.date, 'YYYY-MM-DD HH:mm').toDate();
						element.endDate = moment(element.endDate.date, 'YYYY-MM-DD HH:mm').toDate();
					});
					$('#calendar').data('calendar').setDataSource(data);
				}
			});
		}
	});

	generateStatisticChart(chart_statistic, current_view_year);

	$(function() {
		$('#datetimepicker1').datetimepicker({
			date: new Date(),
			viewMode: 'months',
			format: 'MMMM YYYY',
			locale: 'de'
		});
		$('#datetimepicker1').on('dp.change', function() {
			$.ajax({
				type: "POST",
				url: route_remainingmtl,
				data: {
					date: $('#datetimepicker1').data("DateTimePicker").date().format('YYYY-MM-DD'),
					ids: employee_id
				},
				success: function(resource) {

					$('#monthly-days').text(getRemainingMtlString(resource) + 'übrig');
				}
			});
		});

		$('#monthly-days').text(getRemainingMtlString(remainin_mtl_json) + 'übrig');

	});

	$('#yearly').text('Urlaubstage - ' + current_view_year);
	$('#statistic').text('Statistik - ' + current_view_year);
});