<?php
// Local development entry point; application source stays outside the web entry directory.
header('Location: public/index.php?r=home', true, 302);
exit;
