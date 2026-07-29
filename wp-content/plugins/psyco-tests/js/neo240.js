function DrawScalesChart(neo_data){
	Highcharts.chart('result_chart', {
		chart: {
			type: 'column'},
		title: {
			text: 'نئو 240 سئوالی',
			align: 'left'
		},
		credits: {
			enabled: false
		},
		xAxis: {
			categories: ['وجدان گرایی', 'دلپذیر بودن', 'باز بودن به تجربه ها', 'برونگرایی', 'بی ثباتی هیجانی'],
			crosshair: true,
			accessibility: {
				description: 'Countries'}},
		yAxis: {
			min: 0,
			max: 100,
			title: {
				text: ''}},
		tooltip: {
			valueSuffix: ''},
		plotOptions: {
			series: {
				dataLabels: {
					enabled: true}},
			column: {
				pointPadding: 0.2,
				borderWidth: 0}},
		series: [
			{
				name: 'نمرات',
				data: neo_data 
			}
		]});
}


// sample call: SubScaleChart('n1_n6_chart', 'بی ثباتی هیجانی', ['اضطراب','خشم و کینه ورزی','افسردگی','هشیاری به خویش-شرم','تکانشوری','آسیب پذیری از استرس'], [{n1_score_percentage},{n2_score_percentage},{n3_score_percentage},{n4_score_percentage},{n5_score_percentage},{n6_score_percentage}]);

function SubScaleChart(chart_id, chart_title, chart_categories, chart_data){
	Highcharts.chart(chart_id, {

		title: {
			text: chart_title,
			align: 'left'
		},
		credits: {
			enabled: false
		},
		xAxis: {
				categories: chart_categories
		},
		yAxis: {
			min: 0,
			max: 100
		},
		plotOptions: {
			series: {
				label: {
					connectorAllowed: false
				},
			}
		},

		series: [{
			name:'نمرات',
			data: chart_data
		}],

		responsive: {
			rules: [{
				condition: {
					maxWidth: 500
				},
				chartOptions: {
					legend: {
						layout: 'horizontal',
						align: 'center',
						verticalAlign: 'bottom'
					}
				}
			}]
		}

	});
}

function level_value (level){
  var ret_value = 0;
	if (level == 'خیلی زیاد'){
	ret_value = 90;
  }
  else if (level == 'زیاد'){
	ret_value = 70;
  }
  else if (level == 'متوسط'){
	ret_value = 50;
  }
  else if (level == 'کم'){
	ret_value = 30;
  }
  else if (level == 'خیلی کم'){
	ret_value = 10;
  }
  return ret_value;
}





function DrawPersonalityStyleCharts (data, suffix='')
{
	for(i = 0; i < data.length; i++)
	{
		record = data[i];
		try
		{
			PersonalityStyleChart (record[1], record[0], record[3], record[2], suffix)
		}
		catch (ex)
		{
		
		}
	}
}

function PersonalityStyleChart (XTitle, YTitle, XScore, YScore, suffix = '')
{
	const data = [[XScore,YScore]];

	let chart_title = YTitle+XTitle+"_chart"+suffix;
	// Create the chart
	const chart = Highcharts.chart(chart_title, {
		chart: {
			height: '100%',
			margin: 20
		},
		title: {
			text: YTitle + "-" + XTitle, 
			align: 'left'
		},
		credits: {
			enabled: false
		},
		accessibility: {
			landmarkVerbosity: 'one'
		},
		xAxis: {
			min: 20,
			max: 80,
			gridLineWidth: 1,
			tickInterval: 10,
			crossing: 50,
			title:{
				text: '<strong>' + XTitle + '</strong>',
				align: "left"
				
			}
		},
		yAxis: {
			min: 20,
			max: 80,
			tickInterval: 10,
			lineWidth: 1,
			crossing: 50,
			title:{
				text: '<strong>' + YTitle + '</strong>',
				align: "high",
				rotation: 0,
				
				y:  -10
			}
		},
		legend: {
			enabled: false
		},
		tooltip: {
			headerFormat: '',
			pointFormat: XTitle + '={point.x}, ' + YTitle + '={point.y}'
		},
		series: [{
			data
		}],
		exporting: {
            enabled: true
        }  
	});
}

function DrawDisorderChart (dis_desc, dis_key)
{
  let container = dis_desc.toLowerCase().replace(' ', '_').concat('_chart');
  var temp = dis_key.replace ('N', '~').replace ('E', '~').replace ('O', '~').replace ('A', '~').replace ('C', '~').substr(1);

  var v = temp.split ('~');
  var disorder_data = [];

  for (let i = 0; i < v.length; i++){
	if (v[i] == '++'){
	  disorder_data = disorder_data.concat ([[80,80,level_value(levels[i]),100,99]]); 
	}
	else if (v[i] == '+'){
		disorder_data = disorder_data.concat ([[60,60,level_value(levels[i]),80,79]]);    
	}
	else if (v[i] == '*'){
		disorder_data = disorder_data.concat ([[40,40,level_value(levels[i]),60,59]]);    
	}
	else if (v[i] == '-'){
		disorder_data = disorder_data.concat ([[20,20,level_value(levels[i]),40,39]]);    
	}
	else if (v[i] == '--'){
		disorder_data = disorder_data.concat ([[0,0,level_value(levels[i]),20,19]]);    
	}
  }


  const chart = Highcharts.chart(container, {
	  title: {
		  text: dis_desc,
		  align: 'left',
		  margin: 30
	  },
	  subtitle: {
		text: dis_key,
		align: 'left'
		},
	  credits: {
		enabled: false
	  },
	  accessibility: {
		  landmarkVerbosity: 'one'
	  },
	  legend: {
		  enabled: false
	  },
	  xAxis: {
		  categories: ['N', 'E', 'O', 'A', 'C'],
		  crosshair: {
			  enabled: true
		  },

	  },
	  yAxis: {
		  max: 100,
		  tickInterval: 20,
		  title: {
			  enabled: false
		  },
		  labels: {
			  format: '{text}'
		  },
		   plotBands: [{ // Very Low
			  from: 0,
			  to: 20,
			  color: 'rgba(68, 170, 213, 0.1)',
			  label: {
				  text: 'خیلی کم',
				  style: {
					  color: '#606060'
				  }
			  }
		  }, { //Low
			  from: 20,
			  to: 40,
			  color: 'rgba(0, 0, 0, 0)',
			  label: {
				  text: 'کم',
				  style: {
					  color: '#606060'
				  }
			  }
		  }, { //Medium
			  from: 40,
			  to: 60,
			  color: 'rgba(68, 170, 213, 0.1)',
			  label: {
				  text: 'متوسط',
				  style: {
					  color: '#606060'
				  }
			  }
		  }, { //High
			  from: 60,
			  to: 80,
			  color: 'rgba(0, 0, 0, 0)',
			  label: {
				  text: 'زیاد',
				  style: {
					  color: '#606060'
				  }
			  }
		  }, { //Very High
			  from: 80,
			  to: 100,
			  color: 'rgba(68, 170, 213, 0.1)',
			  label: {
				  text: 'خیلی زیاد',
				  style: {
					  color: '#606060'
				  }
			  }
		  }],
	  },
	  tooltip: {
		enabled: false
		 // followPointer: true
	  },
	  colors: ['#8E4161', '#4F685F', '#A4604D', '#4C5270', '#6A040F',
		  '#51574A', '#6F7D8C', '#824D5C', '#466365', '#7B6F72'],
	  series: [{
		  name: 'Scale Levels',
		  type: 'boxplot',
		  medianWidth: 3,
		  colorByPoint: true,
		  stickyTracking: false,
		  cursor: 'pointer',
		  data: disorder_data

	  }]
  });
}

function DrawDisorderCharts (data)
{
	for(i = 0; i < data.length; i++)
	{
		record = data[i];
		try
		{
			DrawDisorderChart (record[0], record[1])
		}
		catch (ex)
		{
		
		}
	}
}

