import ApexCharts from "apexcharts";

// i use this element to render the count chart
const dataOrderChart = document.getElementById('data-order-chart');

// i use this element to render the amount chart
const dataCollecteChart = document.getElementById('data-collecte-chart');

// create datas for amount and count datas for collecte and order the last 7 days
if (dataCollecteChart && dataOrderChart) {
    const datasCollecte = JSON.parse(dataCollecteChart.getAttribute('data-chart-datas'));
    const datasOrder = JSON.parse(dataOrderChart.getAttribute('data-chart-datas'));

    // the labels is true for all charts
    const labels = [];

    const collecteAmount = [];
    const collecteCount = [];

    for (let i = 0; i < datasCollecte.length; i++) {
        labels.push(datasCollecte[i]['dayName']);
        collecteAmount.push(datasCollecte[i]['totalAmount']);
        collecteCount.push(datasCollecte[i]['orderCount']);
    }

    const orderAmount = [];
    const orderCount = [];

    for (let i = 0; i < datasOrder.length; i++) {
        orderAmount.push(datasOrder[i]['totalAmount']);
        orderCount.push(datasOrder[i]['orderCount']);
    }

    // create chart for amount datas for collecte and order the last 7 days
    if (dataCollecteChart) {
        buildChart(orderAmount, collecteAmount, 'Montant des ventes', 'Montant des collectes', labels, dataCollecteChart);
    }

    // create chart for count datas for collecte and order the last 7 days
    if (dataOrderChart) {
        buildChart(orderCount, collecteCount, 'Nombre de ventes', 'Nombre de collectes', labels, dataOrderChart);
    }
}


function buildChart(orderValue, collecteValue, orderLabel, collecteLabel, labels, dataChart) {

    const options = {
        // add data series via arrays, learn more here: https://apexcharts.com/docs/series/
        series: [
            {
                name: orderLabel,
                data: orderValue,
                color: "#1A56DB",
            },
            {
                name: collecteLabel,
                data: collecteValue,
                color: "#7E3BF2",
            },
        ],
        chart: {
            height: "250px",
            maxWidth: "100%",
            type: "area",
            fontFamily: "Inter, sans-serif",
            dropShadow: {
                enabled: true,
            },
            toolbar: {
                show: true,
            },
        },
        tooltip: {
            enabled: true,
            x: {
                show: true,
            },
        },
        legend: {
            show: true
        },
        fill: {
            type: "gradient",
            gradient: {
                opacityFrom: 0.70,
                opacityTo: 0,
                shade: "#1C64F2",
                gradientToColors: ["#1C64F2"],
            },
        },
        dataLabels: {
            enabled: false,
        },
        stroke: {
            width: 2,
        },
        grid: {
            show: true,
            strokeDashArray: 4,
            padding: {
                left: 2,
                right: 2,
                top: 0
            },
        },
        xaxis: {
            categories: labels,
            labels: {
                show: true,
            },
            axisBorder: {
                show: false,
            },
            axisTicks: {
                show: true,
            },
        },
        yaxis: {
            show: false,
            labels: {
                formatter: function (value) {

                    if (value >= 1000) {
                        value = (value / 1000).toFixed(0) + 'k CFA';
                    }

                    if (value <= 100) {
                        value = value.toFixed(0);
                    }

                    return value;
                }
            }
        },
    }

    if (dataChart && typeof ApexCharts !== 'undefined') {
        const chart = new ApexCharts(dataChart, options);
        chart.render();
    }

}