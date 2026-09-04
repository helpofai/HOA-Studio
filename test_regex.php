<?php
$html = '<img alt="expert here" src="expert.jpg">expert';
$kw = trim(preg_quote('expert', '/'));
try {
    echo preg_replace("/\b({$kw})\b(?![^<]*>)/i", "<mark>$1</mark>", $html);
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
