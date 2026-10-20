<?php
/**
 * Plain-text copy of the submission alert, for mail apps that do not show HTML.
 * Same variables as emails/submission.php.
 */
$width = max(array_map('mb_strlen', array_keys($details)) ?: [0]);
echo strtoupper($heading), "\n";
echo $summary, "\n";
echo 'Received ', $when, ' through ', $source, ".\n\n";
foreach ($details as $k => $v) {
    echo str_pad($k . ':', $width + 2), $v, "\n";
}
if ($message !== '') {
    echo "\n", $career ? 'Their note' : 'Their message', ":\n", $message, "\n";
}
echo "\n", 'Reply to this email to answer ', $name, " directly.\n";
if ($cmsUrl) {
    echo 'Open in the CMS: ', $cmsUrl, "\n";
}
echo "\n-- \nAutomatic message from the ", $site->name, ' website · ', $website, "\n";
