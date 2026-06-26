<?php
/**
 * Susie - Email Link Component with Anti-Spam Obfuscation
 * * Generates a clickable mailto link completely obfuscated into HTML decimal entities
 * to prevent automated scrapers from harvesting the email address.
 * * Optional input variables (scoped from parent layout/view):
 * @var string $email_text    Custom text/label for the link. Defaults to obfuscated email.
 * @var string $email_subject Pre-filled subject line for the email (automatically URL encoded).
 * @var string $email_class   Optional space-separated CSS classes to style the anchor tag.
 */

$email_brut = $config['site']['email'] ?? '';

if (!empty($email_brut)) {
    // 1. Append query string arguments if an optional subject line is provided
    $mailto_brut = $email_brut;
    if (isset($email_subject) && !empty($email_subject)) {
        $mailto_brut .= '?subject=' . urlencode($email_subject);
    }

    // 2. Encode the entire mailto target string into HTML decimal entities
    $mailto_masque = '';
    for ($i = 0; $i < strlen($mailto_brut); $i++) {
        $mailto_masque .= '&#' . ord($mailto_brut[$i]) . ';';
    }

    // 3. Resolve the link anchor text/label
    $link_text = isset($email_text) ? $email_text : '';
    if (empty($link_text)) {
        // Fallback: encode the plain email address for standard visual display
        for ($i = 0; $i < strlen($email_brut); $i++) {
            $link_text .= '&#' . ord($email_brut[$i]) . ';';
        }
    }

    // 4. Resolve optional CSS classes
    $link_class = isset($email_class) ? ' class="' . $email_class . '"' : '';

    // Output the fully protected HTML anchor element
    echo '<a href="mailto:' . $mailto_masque . '"' . $link_class . '>' . $link_text . '</a>';

    # Housekeeping: Clear contextual helper states to isolate concurrent component calls
    unset($email_text);
    unset($email_subject);
    unset($email_class);
}
