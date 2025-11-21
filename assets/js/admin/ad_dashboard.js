const genderLabels = window.genderLabels || [];
const manData = window.manData || [];
const womanData = window.womanData || [];

console.log(genderLabels, manData, womanData); // test

const ctx = document.getElementById('genderLineChart').getContext('2d');

const genderLineChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: genderLabels,
        datasets: [{
            label: 'ผู้ชาย',
            data: manData,
            borderColor: 'rgba(54, 162, 235, 1)', // ฟ้า ตาม AdminLTE
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            borderWidth: 2,
            tension: 0.4,
            fill: false
        },
        {
            label: 'ผู้หญิง',
            data: womanData,
            borderColor: 'rgba(255, 99, 132, 1)', // ชมพูแดง
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            borderWidth: 2,
            tension: 0.4,
            fill: false
        }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'จำนวนคน'
                }
            },
            x: {
                title: {
                    display: true,
                    text: 'เดือนที่เข้าพัก'
                }
            }
        },
        plugins: {
            legend: {
                display: true
            },
            tooltip: {
                enabled: true
            }
        }
    }
});