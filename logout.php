<?php
session_unset();
session_destroy();

echo '<script language=javascript>window.location.href="/index.php"</script>';

?>