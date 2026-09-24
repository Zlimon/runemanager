<?php

namespace App\Services\News;

use Mews\Purifier\Facades\Purifier;

/**
 * The single gate every article body passes through before it is stored.
 *
 * News bodies are the only user-supplied HTML the site renders with `v-html`,
 * so unsanitised editor output would be stored XSS: a script saved once runs in
 * every visitor's browser, and "only managers can write posts" is not a defence
 * — a manager's session can be hijacked, and a demoted manager's posts stay on
 * the site. Sanitising on save rather than on render means a body already in the
 * database is safe no matter which page later displays it.
 *
 * The allowlist lives in the `news` profile in `config/purifier.php`.
 */
class SanitizeArticleHtml
{
    public const PROFILE = 'news';

    public function clean(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        return (string) Purifier::clean($html, self::PROFILE);
    }

    /**
     * The body as plain text, for anywhere HTML would show through as markup —
     * a meta description, a webhook, or the in-game announcement.
     */
    public function toPlainText(?string $html): string
    {
        return trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags((string) $html))) ?? '');
    }
}
