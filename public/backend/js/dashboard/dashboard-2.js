
(function($) {
  "use strict"

  let ctx = document.getElementById("ecom_6");
  ctx.height = 75;
  new Chart(ctx, {
      type: 'bar',
      data: {
          labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun","Jul","Aug","Sep","Oct","Nov","Dec"],
          type: 'line',
          defaultFontFamily: 'Montserrat',
          datasets: [{
              data: [25, 15, 57, 12, 85, 10,75,30,45,60,95,40],
              label: "iPhone X",
              backgroundColor: 'rgba(115, 103, 240,1)',
              borderColor: 'rgba(115, 103, 240,1)',
              borderWidth: 0,
              pointStyle: 'circle',
              pointRadius: 5,
              pointBorderColor: 'rgba(115, 103, 240,1)',
              pointBackgroundColor: 'rgba(115, 103, 240,1)',
          }]
      },
      options: {
          responsive: !0,
          maintainAspectRatio: true,
          tooltips: {
              // display: false,
              mode: 'index',
              titleFontSize: 12,
              titleFontColor: '#fff',
              bodyFontColor: '#fff',
              backgroundColor: '#000',
              titleFontFamily: 'Montserrat',
              bodyFontFamily: 'Montserrat',
              cornerRadius: 3,
              intersect: false,
          },
          legend: {
              display: false,
              position: 'top',
              labels: {
                  usePointStyle: true,
                  fontFamily: 'Montserrat',
              },


          },
          scales: {
              xAxes: [{
                  display: false,
                  gridLines: {
                      display: false,
                      drawBorder: false
                  },
                  scaleLabel: {
                      display: false,
                      labelString: 'Month'
                  },
                  barPercentage: 1.5,
                  categoryPercentage: 0.2
              }],
              yAxes: [{
                  display: false,
                  gridLines: {
                      display: false,
                      drawBorder: false
                  },
                  scaleLabel: {
                      display: true,
                      labelString: 'Value'
                  }
              }]
          },
          title: {
              display: false,
          }
      }
  });


  


})(jQuery);


(function($) {
  "use strict"

  let ctx = document.getElementById("ecom_7");
  ctx.height = 75;
  new Chart(ctx, {
      type: 'line',
      data: {
          labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun"],
          type: 'line',
          defaultFontFamily: 'Montserrat',
          datasets: [{
              data: [50, 35, 57, 42, 85, 60],
              label: "iPhone X",
              backgroundColor: 'rgba(115, 103, 240,0.5)',
              borderColor: 'rgba(115, 103, 240,1)',
              borderWidth: 4,
              pointStyle: 'circle',
              pointRadius: 0,
              pointBorderColor: 'rgba(115, 103, 240,1)',
              pointBackgroundColor: 'rgba(115, 103, 240,1)',
          }]
      },
      options: {
          responsive: !0,
          maintainAspectRatio: true,
          tooltips: {
              mode: 'index',
              titleFontSize: 12,
              titleFontColor: '#fff',
              bodyFontColor: '#fff',
              backgroundColor: '#000',
              titleFontFamily: 'Montserrat',
              bodyFontFamily: 'Montserrat',
              cornerRadius: 3,
              intersect: false,
          },
          legend: {
              display: false,
              position: 'top',
              labels: {
                  usePointStyle: true,
                  fontFamily: 'Montserrat',
              },


          },
          scales: {
              xAxes: [{
                  display: false,
                  gridLines: {
                      display: false,
                      drawBorder: false
                  },
                  scaleLabel: {
                      display: false,
                      labelString: 'Month'
                  }
              }],
              yAxes: [{
                  display: false,
                  gridLines: {
                      display: false,
                      drawBorder: false
                  },
                  scaleLabel: {
                      display: true,
                      labelString: 'Value'
                  }
              }]
          },
          title: {
              display: false,
          }
      }
  });


  


})(jQuery);


(function($) {
  "use strict"

  let ctx = document.getElementById("ecom_8");
  ctx.height = 75;
  new Chart(ctx, {
      type: 'line',
      data: {
          labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun"],
          type: 'line',
          defaultFontFamily: 'Montserrat',
          datasets: [{
              data: [0, 15, 57, 12, 85, 10],
              label: "iPhone X",
              backgroundColor: 'rgba(115, 103, 240,1)',
              borderColor: 'rgba(115, 103, 240,1)',
              borderWidth: 2,
              pointStyle: 'circle',
              pointRadius: 0,
              pointBorderColor: 'rgba(115, 103, 240,1)',
              pointBackgroundColor: 'transparent',
          }]
      },
      options: {
          responsive: !0,
          maintainAspectRatio: true,
          tooltips: {
              mode: 'index',
              titleFontSize: 12,
              titleFontColor: '#fff',
              bodyFontColor: '#fff',
              backgroundColor: '#000',
              titleFontFamily: 'Montserrat',
              bodyFontFamily: 'Montserrat',
              cornerRadius: 3,
              intersect: false,
          },
          legend: {
              display: false,
              position: 'top',
              labels: {
                  usePointStyle: true,
                  fontFamily: 'Montserrat',
              },


          },
          scales: {
              xAxes: [{
                  display: false,
                  gridLines: {
                      display: false,
                      drawBorder: false
                  },
                  scaleLabel: {
                      display: false,
                      labelString: 'Month'
                  }
              }],
              yAxes: [{
                  display: false,
                  gridLines: {
                      display: false,
                      drawBorder: false
                  },
                  scaleLabel: {
                      display: true,
                      labelString: 'Value'
                  }
              }]
          },
          title: {
              display: false,
          }
      }
  });


  


})(jQuery);


(function($) {
  "use strict"

  let ctx = document.getElementById("ecom_9");
  ctx.height = 75;
  new Chart(ctx, {
      type: 'line',
      data: {
          labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun"],
          type: 'line',
          defaultFontFamily: 'Montserrat',
          datasets: [{
              data: [0, 15, 57, 12, 85, 10],
              label: "iPhone X",
              backgroundColor: 'rgba(115, 103, 240,0.5)',
              borderColor: 'rgba(115, 103, 240,1)',
              borderWidth: 2,
              pointStyle: 'circle',
              pointRadius: 0,
              pointBorderColor: 'rgba(115, 103, 240,1)',
              pointBackgroundColor: 'transparent',
              lineTension: 0,
          }]
      },
      options: {
          responsive: !0,
          maintainAspectRatio: true,
          tooltips: {
              mode: 'index',
              titleFontSize: 12,
              titleFontColor: '#fff',
              bodyFontColor: '#fff',
              backgroundColor: '#000',
              titleFontFamily: 'Montserrat',
              bodyFontFamily: 'Montserrat',
              cornerRadius: 3,
              intersect: false,
          },
          legend: {
              display: false,
              position: 'top',
              labels: {
                  usePointStyle: true,
                  fontFamily: 'Montserrat',
              },


          },
          scales: {
              xAxes: [{
                  display: false,
                  gridLines: {
                      display: false,
                      drawBorder: false
                  },
                  scaleLabel: {
                      display: false,
                      labelString: 'Month'
                  }
              }],
              yAxes: [{
                  display: false,
                  gridLines: {
                      display: false,
                      drawBorder: false
                  },
                  scaleLabel: {
                      display: true,
                      labelString: 'Value'
                  }
              }]
          },
          title: {
              display: false,
          }
      }
  });


  


})(jQuery);
