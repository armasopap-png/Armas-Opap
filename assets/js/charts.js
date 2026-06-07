/**
 * ARMAS Chart.js Configurations
 */

// Chart.js default configuration
Chart.defaults.font.family = "'DM Sans', sans-serif";
Chart.defaults.color = '#6B7280';
Chart.defaults.plugins.tooltip.backgroundColor = '#1A3A6B';
Chart.defaults.plugins.tooltip.titleFont = { weight: '600' };
Chart.defaults.plugins.tooltip.padding = 12;
Chart.defaults.plugins.tooltip.cornerRadius = 8;

// Color palette
const chartColors = {
    primary: '#1A3A6B',
    secondary: '#C8A951',
    success: '#27AE60',
    warning: '#F39C12',
    danger: '#E74C3C',
    inProcess: '#2980B9',
    mid: '#6B7280',
    light: '#E8F4FD',
    background: ['#F39C12', '#2980B9', '#27AE60', '#E74C3C', '#C8A951', '#6B7280']
};

// Initialize charts with data
function initCharts(barData, lineData, pieData) {
    initBarChart(barData);
    initLineChart(lineData);
    initPieChart(pieData);
}

// Bar Chart - Cases by Status
function initBarChart(data) {
    const ctx = document.getElementById('barChart');
    if (!ctx) return;
    
    // Destroy existing chart
    const existingChart = Chart.getChart(ctx);
    if (existingChart) existingChart.destroy();
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.map(r => formatStatus(r.status)),
            datasets: [{
                label: 'Cases',
                data: data.map(r => r.count),
                backgroundColor: [
                    '#F39C12', // pending - amber
                    '#2980B9', // in_process - blue
                    '#27AE60', // resolved - green
                    '#E74C3C'  // closed - red
                ],
                borderRadius: 6,
                borderSkipped: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.parsed.y + ' cases';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    },
                    grid: {
                        color: '#E2E8F0'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
}

// Line Chart - Monthly Volume
function initLineChart(data) {
    const ctx = document.getElementById('lineChart');
    if (!ctx) return;
    
    const existingChart = Chart.getChart(ctx);
    if (existingChart) existingChart.destroy();
    
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const lineValues = Array(12).fill(0);
    
    data.forEach(r => {
        if (r.month >= 1 && r.month <= 12) {
            lineValues[r.month - 1] = r.count;
        }
    });
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: months,
            datasets: [{
                label: 'Cases Filed',
                data: lineValues,
                borderColor: chartColors.primary,
                backgroundColor: 'rgba(26, 58, 107, 0.1)',
                fill: true,
                tension: 0.4,
                pointBackgroundColor: chartColors.primary,
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    },
                    grid: {
                        color: '#E2E8F0'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
}

// Pie Chart - Case Type Breakdown
function initPieChart(data) {
    const ctx = document.getElementById('pieChart');
    if (!ctx) return;
    
    const existingChart = Chart.getChart(ctx);
    if (existingChart) existingChart.destroy();
    
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: data.map(r => r.type || 'Unknown'),
            datasets: [{
                data: data.map(r => r.count),
                backgroundColor: chartColors.background,
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
                }
            }
        }
    });
}

// Doughnut Chart - Alternative to pie
function initDoughnutChart(data, label) {
    const ctx = document.getElementById('doughnutChart');
    if (!ctx) return;
    
    const existingChart = Chart.getChart(ctx);
    if (existingChart) existingChart.destroy();
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: data.map(r => r.status || r.type || 'Unknown'),
            datasets: [{
                data: data.map(r => r.count),
                backgroundColor: chartColors.background,
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '60%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
                },
                title: {
                    display: !!label,
                    text: label,
                    font: {
                        size: 16,
                        weight: '600'
                    }
                }
            }
        }
    });
}

// Update charts when agency dropdown changes
function updateCharts(data) {
    // Destroy all existing charts
    Chart.helpers.each(Chart.instances, function(chart) {
        chart.destroy();
    });
    
    // Reinitialize with new data
    if (data.bar) initBarChart(data.bar);
    if (data.line) initLineChart(data.line);
    if (data.pie) initPieChart(data.pie);
}

// Helper function to format status
function formatStatus(status) {
    const statusMap = {
        'pending': 'Pending',
        'in_process': 'In Process',
        'resolved': 'Resolved',
        'closed': 'Closed'
    };
    return statusMap[status] || status;
}

// Initialize charts from PHP data
function initChartsFromPHP(barData, lineData, pieData) {
    if (barData) initBarChart(barData);
    if (lineData) initLineChart(lineData);
    if (pieData) initPieChart(pieData);
}

// Export chart as image
function exportChartAsImage(chartId, filename) {
    const canvas = document.getElementById(chartId);
    if (!canvas) return;
    
    const link = document.createElement('a');
    link.download = filename || 'chart.png';
    link.href = canvas.toDataURL('image/png');
    link.click();
}
