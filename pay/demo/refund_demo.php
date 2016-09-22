<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>GS Query Demo</title>
</head>
<body style="text-align:center">
    <p>Refund Demo </p>
    <form action="../refund.php" method="post" >
    <table style="margin:0 auto">
        <tr><td>GSOrdId:</td><td><input type="text" name="GSOrdId" ></td></tr> 
        <tr><td>MerOrdId: </td><td><input type="text" name="MerOrdId" value="2016082222564628"></td></tr> 
        <tr><td>refund_amount: </td><td><input type="text" name="refund_amount" value="000000024000"></td></tr>
        <tr><td>order_date: </td><td><input type="text" name="order_date" value="20160823"></td></tr>
        <tr><td>priv1: </td><td><input type="text" name="priv1" value="000000003844"></td></tr>
        <tr><td></td><td><input type="submit"  value="Refund Submit"></td></tr>
    </table>
    </form>
   
</body>
</html>