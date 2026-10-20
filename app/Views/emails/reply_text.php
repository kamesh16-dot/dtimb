<?php
/** Plain-text copy of an Inbox reply. Same variables as emails/reply.php. */
echo trim($body), "\n\n-- \n";
if ($sender !== '') {
    echo $sender, "\n";
}
echo $site->name, ' — ', $site->tagline, "\n", $email, ' · ', $website, "\n";
if ($quote !== null) {
    echo "\n", $quoteHead, "\n";
    foreach (preg_split('/\R/', $quote) ?: [] as $line) {
        echo '> ', $line, "\n";
    }
}
