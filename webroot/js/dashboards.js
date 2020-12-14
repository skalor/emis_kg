/*
@OPENEMIS LICENSE LAST UPDATED ON 2013-05-16

OpenEMIS
Open Education Management Information System

Copyright © 2013 UNECSO.  This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by the Free Software Foundation
, either version 3 of the License, or any later version.  This program is distributed in the hope
that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
or FITNESS FOR A PARTICULAR PURPOSE.See the GNU General Public License for more details. You should
have received a copy of the GNU General Public License along with this program.  If not, see
<http://www.gnu.org/licenses/>.  For more information please wire to contact@openemis.org.
*/

$(document).ready(function() {
	dashboards.init();
});

var dashboards = {
	init: function() {
		var self = this;

		$.each($('.highchart'), function(key, group) {
			if (!$(group).hasClass('dashboard-custom-chart')) {
                var json = $(group).html();
                var obj = JSON.parse(json);

                $(group).highcharts(obj);
                $(group).css({"visibility":"visible"});

            }
			else {
                var json = $(group).html();
				self.initCustomChart(key,json);

			}
		});
		$('#dashboard-spinner').css({"display":"none"});
	},
	// fix PIB: custom chart
	initCustomChart: function (index,json) {

		var obj = null;
		try {
			obj = $.parseJSON(json);
		}
		catch (err) {
			return;
		}
        if (('dashboard-custom-chart-'+index) === 'dashboard-custom-chart-0') {
            obj.colors = ['#688C90', '#90688C', '#6F52ED'];
            obj.exporting = {enabled: false};
            obj.xAxis.labels = { autoRotation: [-10, -20, -30, -40, -50, -60, -70, -80, -90] };
            obj.plotOptions = { column: { maxPointWidth: 12, groupPadding: 0.3 } };
            obj.title.align = 'left';
            obj.title.x = 8;
            obj.subtitle.align = 'right';
            obj.subtitle.y = 12;
            if ($(window).width() < 720) {
                delete obj.subtitle.y;
                obj.plotOptions.column.groupPadding = 0.1;
            }
            $("#dashboard-custom-chart-0").highcharts(obj);
        }
        else if (('dashboard-custom-chart-'+index) === 'dashboard-custom-chart-1') {
            let student = new StatisticsCountGender('#dashboard-custom-chart-1', obj, 1, 3000);
            student.render();
            student.work();
        }
        else if (('dashboard-custom-chart-'+index) === 'dashboard-custom-chart-2') {
            let staff = new StatisticCountStaffType('#dashboard-custom-chart-2', obj, 3000);
            staff.render();
            staff.work();
        }
        else if (('dashboard-custom-chart-'+index) === 'dashboard-custom-chart-3') {
            let year = new StatisticCountStaffYear('#dashboard-custom-chart-3', obj, 3000);
            year.render();
            year.work();
        }
        $('#dashboard-custom-chart-'+index).css({"visibility":"visible"});

	}
}
// Meerbek's code starts
function initCustomChart (yearId) {
    var  institutionId = $('input[name=location]').val().substr(26);
    var objects = null;
    $.ajax({
        url: '/Institution/Institutions/highChartApi',
        data: {
            "yearId": yearId,
            "institutionId": institutionId

        },
        success: function(result){
            objects = result;
            objects.forEach(function (json,index) {

                dashboards.initCustomChart(index,json);
            });
        },
        error:function(err){
            return;
        }
    });
    $('#dashboard-spinner').css({"display":"none"});



}
// Meerbek's code ends
