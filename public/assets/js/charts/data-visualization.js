// Include the G// Definitio of constants to target elements that will contain the generated graph
const PIE_CHART_SELECTOR = 'myChartPie';
const BAR_CHART_SELECTOR = 'myChartBar';
const COLUMN_CHART_SELCTOR = 'myChartColumn';

export const dataVisualization = () => {

    google.charts.load('current',{ packages:['corechart'] });
    google.charts.setOnLoadCallback();

    // A submit listener on the form to perform a AJAX call to retrieve data from database
    $('form').submit((event) => {
        $.ajax({
          type: "POST",
          url: $('form').attr('data-url'),
          data: $('form').serialize(),
          dataType: "JSON",
          encode: true,
        }).done((data) => {
            //Call draw logic with data: columns names, query recors and a dynamic title that will displayed along the chart
            drawChart(data.columns, data.rows, `${data.title} : ${$("form select").val()}`);
        });
    
        // Cancel the default button submission
        event.preventDefault();
      });

}


export const drawChart = (columns, rows, title) => {
    let data = new google.visualization.DataTable();
    
    columns.forEach((column) => {

        // A trick with a prefix to define the column type
        let type = column.startsWith('str_') ? 'string' : 'number';
        data.addColumn(type, column);
    });
    
    let result = []
    rows.forEach((row) => {
        result.push(Object.values(row));
    });

    data.addRows(result);
    
    // Three types of charts implemented
    _drawPieChart(title, data);
    _drawBarChart(title, data);
    _drawColumnChart(title, data, columns);
}


const _drawPieChart = (title, data) => {

     const options = {
        title: `Pie Chart: ${title}`,
        is3D: true
      };
  
      const chart = new google.visualization.PieChart(document.getElementById(PIE_CHART_SELECTOR));
      chart.draw(data, options);
}


const _drawBarChart = (title, data) => {

    const options = {
       title: `Bar Chart: ${title}`,
     };
 
     const chart = new google.visualization.BarChart(document.getElementById(BAR_CHART_SELECTOR));
     chart.draw(data, options);
}



const _drawColumnChart = (title, data, columns) => {

    var options = {
        title: `Column Chart: ${title}`,
        hAxis: {
            title: columns[0]
        },
        vAxis: {
            title: columns[1]
        }
    };

    const chart = new google.visualization.ColumnChart(document.getElementById(COLUMN_CHART_SELCTOR));
    chart.draw(data, options);
}
