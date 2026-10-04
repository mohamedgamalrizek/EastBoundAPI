(function($) {
    "use strict";

    $(".counter").counterUp({
        delay: 30,
        time: 3000
    });

    //     const wc = new PerfectScrollbar('.widget-chat');
    //     const wt = new PerfectScrollbar('.widget-todo');
    //   const wtm = new PerfectScrollbar(".widget-team");
    //     const wtl = new PerfectScrollbar('.widget-timeline');
    //     const wcm = new PerfectScrollbar('.widget-comments');
    var gdpData = {
        "us": 34.56,
        "in": 34.56,
        "gb": 34.56,
        "tr": 34.56,
        "ru": 34.56,
    }


    $("#world-map").vectorMap({
        map: "world_en",
        backgroundColor: "transparent",
        borderColor: "#fff",
        color: "#eee",
        colors: { in: "rgba(89, 59, 219, 1)",
            gb: "rgba(89, 59, 219, 0.8)",
            tr: "rgba(89, 59, 219, 0.7)",
            us: "rgba(89, 59, 219, 0.6)",
            ru: "rgba(89, 59, 219, 0.5)",
        },
        onLabelShow: function(event, label, code) {
            label.text(label.text() + " (" + gdpData[code] + ")");
        },
        enableZoom: true,
        showTooltip: true,
        selectedColor: "rgba(93, 120, 255,1)",
        hoverColor: "rgba(93, 120, 255,0.8)",
    });



    /*======== 16. ANALYTICS - ACTIVITY CHART ========*/
    var activity = document.getElementById("activity");
    if (activity !== null) {
        var activityData = [{
                first: [0, 65, 52, 115, 98, 165, 125],
                second: [40, 105, 92, 155, 138, 205, 165]
            },
            {
                first: [0, 65, 77, 33, 49, 100, 100],
                second: [20, 85, 97, 53, 69, 120, 120]
            },
            {
                first: [0, 40, 77, 55, 33, 116, 50],
                second: [30, 70, 107, 85, 73, 146, 80, ]
            },
            {
                first: [0, 44, 22, 77, 33, 151, 99],
                second: [60, 32, 120, 55, 19, 134, 88]
            }
        ];

        activity.height = 100;

        var config = {
            type: "line",
            data: {
                labels: [
                    "4 Jan",
                    "5 Jan",
                    "6 Jan",
                    "7 Jan",
                    "8 Jan",
                    "9 Jan",
                    "10 Jan"
                ],
                datasets: [{
                        label: "Active",
                        backgroundColor: "rgba(89, 59, 219, 1)",
                        borderColor: "rgba(89, 59, 219, 1)",
                        data: activityData[0].first,
                        lineTension: 0,
                        pointRadius: 0,
                        borderWidth: 2,
                    },
                    {
                        label: "Inactive",
                        backgroundColor: 'rgba(89, 59, 219, 0.61)',
                        borderColor: "rgba(89, 59, 219, 0.61)",
                        data: activityData[0].second,
                        lineTension: 0,
                        // borderDash: [10, 5],
                        borderWidth: 2,
                        pointRadius: 0,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    display: false
                },
                scales: {
                    xAxes: [{
                        gridLines: {
                            color: "rgba(89, 59, 219,0.1)",
                            drawBorder: true
                        },
                        ticks: {
                            fontColor: "#222",
                        },
                    }],
                    yAxes: [{
                        gridLines: {
                            display: false,
                            zeroLineColor: "transparent"
                        },
                        ticks: {
                            stepSize: 50,
                            fontColor: "#222",
                            fontFamily: "Nunito, sans-serif"
                        }
                    }]
                },
                tooltips: {
                    mode: "index",
                    intersect: false,
                    titleFontColor: "#888",
                    bodyFontColor: "#555",
                    titleFontSize: 12,
                    bodyFontSize: 15,
                    backgroundColor: "rgba(256,256,256,0.95)",
                    displayColors: true,
                    xPadding: 10,
                    yPadding: 7,
                    borderColor: "rgba(220, 220, 220, 0.9)",
                    borderWidth: 2,
                    caretSize: 6,
                    caretPadding: 5
                }
            }
        };



        var ctx = document.getElementById("activity").getContext("2d");
        var myLine = new Chart(ctx, config);

        var items = document.querySelectorAll("#user-activity .nav-tabs .nav-item");
        items.forEach(function(item, index) {
            item.addEventListener("click", function() {
                config.data.datasets[0].data = activityData[index].first;
                config.data.datasets[1].data = activityData[index].second;
                myLine.update();
            });
        });
    }

    var nk = document.getElementById("sold-product");
    // nk.height = 50
    new Chart(nk, {
        type: 'pie',
        data: {
            defaultFontFamily: 'Poppins',
            datasets: [{
                data: [45, 25, 20, 10],
                borderWidth: 0,
                backgroundColor: [
                    "rgba(89, 59, 219, .9)",
                    "rgba(89, 59, 219, .7)",
                    "rgba(89, 59, 219, .5)",
                    "rgba(89, 59, 219, .07)"
                ],
                hoverBackgroundColor: [
                    "rgba(89, 59, 219, .9)",
                    "rgba(89, 59, 219, .7)",
                    "rgba(89, 59, 219, .5)",
                    "rgba(89, 59, 219, .07)"
                ]

            }],
            labels: [
                "one",
                "two",
                "three",
                "four"
            ]
        },
        options: {
            responsive: true,
            legend: false,
            maintainAspectRatio: false
        }
    });

    var data = {
        labels: ["0", "1", "2", "3", "4", "5", "6", "0", "1", "2", "3", "4", "5", "6"],
        datasets: [{
            label: "My First dataset",
            backgroundColor: "rgba(89, 59, 219,1)",
            strokeColor: "rgba(95,186,88,1)",
            pointColor: "rgba(0,0,0,0)",
            pointStrokeColor: "rgba(0,0,0,0)",
            pointHighlightFill: "rgba(95,186,88,1)",
            pointHighlightStroke: "rgba(95,186,88,1)",
            data: [65, 59, 80, 81, 56, 55, 40, 65, 59, 80, 81, 56, 55, 40]
        }]
    };

    var ctx = document.getElementById("activeUser").getContext("2d");
    var chart = new Chart(ctx, {
        type: "bar",
        data: data,
        options: {
            responsive: !0,
            maintainAspectRatio: false,
            legend: {
                display: !1
            },
            tooltips: {
                enabled: false
            },
            scales: {
                xAxes: [{
                    display: !1,
                    gridLines: {
                        display: !1
                    },
                    barPercentage: 0.9,
                    categoryPercentage: 1
                }],
                yAxes: [{
                    display: !1,
                    ticks: {
                        padding: 10,
                        stepSize: 10,
                        max: 100,
                        min: 0
                    },
                    gridLines: {
                        display: !0,
                        drawBorder: !1,
                        lineWidth: 1,
                        zeroLineColor: "#e5e5e5"
                    }
                }]
            }
        }
    });

    setInterval(function() {
        chart.config.data.datasets[0].data.push(
            Math.floor(10 + Math.random() * 80)
        );
        chart.config.data.datasets[0].data.shift();
        chart.update();
    }, 2000);




    ////////////
    function r(t, r) {
        return Math.floor(Math.random() * (r - t + 1) + t);
    }
    var interval = 2e3,
        variation = 5,
        c = r(5, 2e3);
    $("#counter").text(c),
        setInterval(function() {
            var t = r(-variation, variation);
            (c += t), $("#counter").text(c);
        }, interval);
})(jQuery);


const wt = new PerfectScrollbar('.widget-todo');
const wtl = new PerfectScrollbar('.widget-timeline');