<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Tahoma', sans-serif; color: #333333; font-size: 14px; }
        .header { background: #286F51; color: #ffffff; padding: 16px 24px; }
        .header h1 { margin: 0; font-size: 20px; }
        .content { padding: 24px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        table td { padding: 8px 0; border-bottom: 1px solid #e0e0e0; }
        table td.label { color: #666666; width: 40%; }
        table td.value { font-weight: 600; }
    </style>
</head>
<body>
    <div class="header">
        <h1>ใบยืนยันการสมัครแพ็กเกจ</h1>
    </div>
    <div class="content">
        <p>เรียน คุณ{{ $userName }}</p>
        <p>การชำระเงินของคุณได้รับการยืนยันแล้ว รายละเอียดมีดังนี้</p>
        <table>
            <tr>
                <td class="label">แพ็กเกจ</td>
                <td class="value">{{ $plan }}</td>
            </tr>
            <tr>
                <td class="label">ราคา</td>
                <td class="value">{{ $price }} บาท</td>
            </tr>
            <tr>
                <td class="label">วันที่เริ่มต้น</td>
                <td class="value">{{ $startDate }}</td>
            </tr>
            <tr>
                <td class="label">วันที่หมดอายุ</td>
                <td class="value">{{ $endDate }}</td>
            </tr>
        </table>
    </div>
</body>
</html>
