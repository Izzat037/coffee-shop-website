(function($) {
    "use strict"; // Start of use strict

    // Toggle the side navigation
    $("#sidebarToggle, #sidebarToggleTop").on('click', function(e) {
        $("body").toggleClass("sidebar-toggled");
        $(".sidebar").toggleClass("toggled");
        if ($(".sidebar").hasClass("toggled")) {
            $('.sidebar .collapse').collapse('hide');
        }
    });

    // Close any open menu accordions when window is resized below 768px
    $(window).resize(function() {
        if ($(window).width() < 768) {
            $('.sidebar .collapse').collapse('hide');
        }
    });

    // Prevent the content wrapper from scrolling when the fixed side navigation hovered over
    $('body.fixed-nav .sidebar').on('mousewheel DOMMouseScroll wheel', function(e) {
        if ($(window).width() > 768) {
            var e0 = e.originalEvent,
                delta = e0.wheelDelta || -e0.detail;
            this.scrollTop += (delta < 0 ? 1 : -1) * 30;
            e.preventDefault();
        }
    });

    // Scroll to top button appear
    $(document).on('scroll', function() {
        var scrollDistance = $(this).scrollTop();
        if (scrollDistance > 100) {
            $('.scroll-to-top').fadeIn();
        } else {
            $('.scroll-to-top').fadeOut();
        }
    });

    // Smooth scrolling using jQuery easing
    $(document).on('click', 'a.scroll-to-top', function(e) {
        var $anchor = $(this);
        $('html, body').stop().animate({
            scrollTop: ($($anchor.attr('href')).offset().top)
        }, 1000, 'easeInOutExpo');
        e.preventDefault();
    });

    // Initialize DataTables for tables with the datatable class
    $(document).ready(function() {
        if ($.fn.DataTable) {
            $('.datatable').DataTable({
                responsive: true,
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search...",
                },
                pageLength: 10,
                lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]]
            });
        }
    });

    // File input customization
    $(document).on('change', '.custom-file-input', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).siblings('.custom-file-label').addClass('selected').html(fileName);
    });

    // Product image preview
    $('#productImage').on('change', function() {
        const file = this.files[0];
        if (file) {
            let reader = new FileReader();
            reader.onload = function(event) {
                $('#imagePreview').attr('src', event.target.result).removeClass('d-none');
            }
            reader.readAsDataURL(file);
        }
    });

    // Confirm deletion
    $('.btn-delete').on('click', function(e) {
        if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
            e.preventDefault();
        }
    });

    // Form validation
    (function() {
        'use strict';
        window.addEventListener('load', function() {
            // Fetch all the forms we want to apply custom Bootstrap validation styles to
            var forms = document.getElementsByClassName('needs-validation');
            // Loop over them and prevent submission
            var validation = Array.prototype.filter.call(forms, function(form) {
                form.addEventListener('submit', function(event) {
                    if (form.checkValidity() === false) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        }, false);
    })();

    // Tooltips
    $(function() {
        $('[data-toggle="tooltip"]').tooltip();
    });

    // Initialize Select2 if available
    if ($.fn.select2) {
        $('.select2').select2({
            theme: 'bootstrap4',
        });
    }

    // Charts - if Chart.js is available
    if (typeof Chart !== 'undefined') {
        // Example chart - Revenue
        var ctx = document.getElementById("revenueChart");
        if (ctx) {
            var myLineChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
                    datasets: [{
                        label: "Revenue",
                        lineTension: 0.3,
                        backgroundColor: "rgba(46, 78, 29, 0.05)",
                        borderColor: "rgba(46, 78, 29, 1)",
                        pointRadius: 3,
                        pointBackgroundColor: "rgba(46, 78, 29, 1)",
                        pointBorderColor: "rgba(46, 78, 29, 1)",
                        pointHoverRadius: 3,
                        pointHoverBackgroundColor: "rgba(46, 78, 29, 1)",
                        pointHoverBorderColor: "rgba(46, 78, 29, 1)",
                        pointHitRadius: 10,
                        pointBorderWidth: 2,
                        data: [0, 10000, 5000, 15000, 10000, 20000, 15000, 25000, 20000, 30000, 25000, 40000],
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    layout: {
                        padding: {
                            left: 10,
                            right: 25,
                            top: 25,
                            bottom: 0
                        }
                    },
                    scales: {
                        xAxes: [{
                            time: {
                                unit: 'date'
                            },
                            gridLines: {
                                display: false,
                                drawBorder: false
                            },
                            ticks: {
                                maxTicksLimit: 7
                            }
                        }],
                        yAxes: [{
                            ticks: {
                                maxTicksLimit: 5,
                                padding: 10,
                                callback: function(value, index, values) {
                                    return '$' + value;
                                }
                            },
                            gridLines: {
                                color: "rgb(234, 236, 244)",
                                zeroLineColor: "rgb(234, 236, 244)",
                                drawBorder: false,
                                borderDash: [2],
                                zeroLineBorderDash: [2]
                            }
                        }],
                    },
                    legend: {
                        display: false
                    },
                    tooltips: {
                        backgroundColor: "rgb(255,255,255)",
                        bodyFontColor: "#858796",
                        titleMarginBottom: 10,
                        titleFontColor: '#6e707e',
                        titleFontSize: 14,
                        borderColor: '#dddfeb',
                        borderWidth: 1,
                        xPadding: 15,
                        yPadding: 15,
                        displayColors: false,
                        intersect: false,
                        mode: 'index',
                        caretPadding: 10,
                        callbacks: {
                            label: function(tooltipItem, chart) {
                                var datasetLabel = chart.datasets[tooltipItem.datasetIndex].label || '';
                                return datasetLabel + ': $' + tooltipItem.yLabel;
                            }
                        }
                    }
                }
            });
        }

        // Example chart - Products
        var ctx2 = document.getElementById("productsChart");
        if (ctx2) {
            var myPieChart = new Chart(ctx2, {
                type: 'doughnut',
                data: {
                    labels: ["Coffee Beans", "Coffee Makers", "Accessories", "Gift Sets"],
                    datasets: [{
                        data: [55, 30, 10, 5],
                        backgroundColor: ['#2E4E1D', '#62824A', '#D1B788', '#F5F1E8'],
                        hoverBackgroundColor: ['#1d3413', '#496336', '#c2a16e', '#e6ddd0'],
                        hoverBorderColor: "rgba(234, 236, 244, 1)",
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    tooltips: {
                        backgroundColor: "rgb(255,255,255)",
                        bodyFontColor: "#858796",
                        borderColor: '#dddfeb',
                        borderWidth: 1,
                        xPadding: 15,
                        yPadding: 15,
                        displayColors: false,
                        caretPadding: 10,
                    },
                    legend: {
                        display: true,
                        position: 'bottom'
                    },
                    cutoutPercentage: 70,
                },
            });
        }
    }

})(jQuery); // End of use strict 