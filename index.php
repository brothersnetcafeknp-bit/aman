<!DOCTYPE html>
<html lang="hi">
<head>
<meta charset="UTF-8">
<title>FPS TXN REPOT FINDER</title>
<style>
body { font-family: Arial, sans-serif; margin: 30px; background: #f4f6f8; }
textarea, input { width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #aaa; font-size: 15px; }
button { padding: 10px 25px; border: none; background: #2c3e50; color: white; border-radius: 6px; cursor: pointer; }
button:hover { background: #1a242f; }
h2 { color: #2c3e50; }
table { background: white; box-shadow: 0 0 5px rgba(0,0,0,0.1); width:100%; border-collapse:collapse; margin-top:20px; text-align:center;}
table th, table td { padding:6px; border:1px solid #aaa; }
#progressContainer { width: 100%; background: #ddd; border-radius: 6px; margin-top:20px; }
#progressBar { width: 0%; height: 20px; background: #2c3e50; border-radius: 6px; text-align:center; color:white; }
#counter { margin-top:10px; font-weight:bold; }
</style>
</head>
<body>
<h2>🌾 FPS TXN REPOT FINDER</h2>
<form id="rcForm">
    <label>Enter multiple RC IDs (comma or line separated):</label><br>
    <textarea name="ids" placeholder="Example:1164412780134" required></textarea><br><br>
    <label>Enter Month or Range :</label><br>
    <input type="text" name="month" placeholder="07 or 07-09" maxlength="5" required><br><br>
    <button type="submit">Search All</button>
</form>

<div id="progressContainer" style="display:none;">
    <div id="progressBar">0%</div>
</div>
<div id="counter"></div>

<table id="resultTable" style="display:none;">
    <tr style="background:#2c3e50;color:white;">
        <th>S.No</th>
        <th>Month</th>
        <th>Year</th>
        <th>RC ID</th>
        <th>Name</th>
        <th>Card Type</th>
        <th>Unit</th>
        <th>FPS Name</th>
        <th>Mode</th>
        <th>Date/Time</th>
    </tr>
</table>

<script>
const form = document.getElementById('rcForm');
const table = document.getElementById('resultTable');
const progressContainer = document.getElementById('progressContainer');
const progressBar = document.getElementById('progressBar');
const counter = document.getElementById('counter');

form.addEventListener('submit', function(e){
    e.preventDefault();
    table.style.display = 'table';
    table.querySelectorAll("tr:not(:first-child)").forEach(tr=>tr.remove());
    progressContainer.style.display = 'block';
    progressBar.style.width = '0%';
    progressBar.innerText = '0%';
    counter.innerText = '';

    let ids = form.ids.value.split(/[\s,]+/).filter(x=>x);
    let monthInput = form.month.value.trim();
    let monthParts = monthInput.split('-');
    let months = [];

    if (monthParts.length === 2) {
        let start = parseInt(monthParts[0]);
        let end = parseInt(monthParts[1]);
        for (let m = start; m <= end; m++) {
            months.push(m.toString().padStart(2, '0'));
        }
    } else {
        months.push(monthInput.padStart(2, '0'));
    }

    let total = ids.length * months.length;
    let processed = 0;
    let sno = 1;

    async function processID(rcid, month) {
        let res = await fetch('fetch.php', {
            method: 'POST',
            headers: {'Content-Type':'application/x-www-form-urlencoded'},
            body: 'id='+encodeURIComponent(rcid)+'&month='+encodeURIComponent(month)
        });
        let data = await res.json();
        if (data.rows && data.rows.length) {
            data.rows.forEach(row => {
                let qty = parseFloat(row.quantity_lifted || 0);
                let halfQty = qty ? (qty / 2).toFixed(2) : '';
                let tr = document.createElement('tr');
                tr.innerHTML = `<td>${sno++}</td>
                    <td>${row.MnthName}</td>
                    <td>${row.yr}</td>
                    <td>${row.RC_ID}</td>
                    <td>${row.member_name_ll}</td>
                    <td>${row.cardtype}</td>
                    <td>${halfQty}</td>
                    <td>${row.PortabiliytFPS}</td>
                    <td>${row.Mode}</td>
                    <td>${row.DT}</td>`;
                table.appendChild(tr);
            });
        } else {
            let tr = document.createElement('tr');
            tr.innerHTML = `<td>${sno++}</td>
                            <td>${month}</td><td></td>
                            <td>${rcid}</td>
                            <td></td><td></td>
                            <td></td>
                            <td></td><td></td><td></td>`;
            table.appendChild(tr);
        }

        processed++;
        let percent = Math.round((processed / total) * 100);
        progressBar.style.width = percent + '%';
        progressBar.innerText = percent + '%';
        counter.innerText = `Processed ${processed} of ${total} (${Math.round(percent)}%)`;
    }

    async function startProcess() {
        for (let m of months) {
            for (let id of ids) {
                await processID(id, m);
            }
        }
    }

    startProcess();
});
</script>
</body>
</html>
