<?php
/**
 * Rich-text sanitizer for content authored in Quill.
 *
 * Content is sanitized on save AND re-sanitized on render (defense in depth).
 * Only a known whitelist of tags / attributes / URL schemes survives.
 */

const RT_TAGS = [
    'p', 'br', 'hr', 'span', 'div',
    'strong', 'b', 'em', 'i', 'u', 's', 'del', 'ins', 'sub', 'sup', 'mark',
    'blockquote', 'pre', 'code',
    'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
    'ul', 'ol', 'li',
    'a', 'img', 'figure', 'figcaption',
    'iframe', 'video', 'source',
];

// Allowed attributes per tag (besides the global "class").
const RT_ATTRS = [
    'a'      => ['href', 'target', 'rel'],
    'img'    => ['src', 'alt', 'width', 'height', 'style'],
    'span'   => ['style'],
    'p'      => ['style'],
    'iframe' => ['src', 'width', 'height', 'allowfullscreen', 'frameborder', 'allow'],
    'video'  => ['src', 'width', 'height', 'controls', 'poster'],
    'source' => ['src', 'type'],
];

// Hosts permitted in <iframe> embeds (video + maps).
const RT_IFRAME_HOSTS = [
    'www.youtube.com', 'youtube.com', 'www.youtube-nocookie.com',
    'player.vimeo.com', 'yandex.com', 'yandex.ru', 'api-maps.yandex.ru',
];

function sanitize_html(?string $html): string
{
    $html = trim((string) $html);
    if ($html === '') {
        return '';
    }

    $doc = new DOMDocument('1.0', 'UTF-8');
    $prev = libxml_use_internal_errors(true);
    // Wrap so we can extract just the body; force UTF-8.
    $doc->loadHTML(
        '<?xml encoding="UTF-8"><div id="rt-root">' . $html . '</div>',
        LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR | LIBXML_NOWARNING
    );
    libxml_clear_errors();
    libxml_use_internal_errors($prev);

    $root = $doc->getElementById('rt-root');
    if (!$root) {
        return '';
    }

    rt_clean_node($root, $doc);

    $out = '';
    foreach (iterator_to_array($root->childNodes) as $child) {
        $out .= $doc->saveHTML($child);
    }
    return trim($out);
}

/** Recursively clean a node's children in place. */
function rt_clean_node(DOMNode $node, DOMDocument $doc): void
{
    foreach (iterator_to_array($node->childNodes) as $child) {
        if ($child instanceof DOMText || $child instanceof DOMNode && $child->nodeType === XML_TEXT_NODE) {
            continue; // text is safe (serialized escaped)
        }
        if (!($child instanceof DOMElement)) {
            $node->removeChild($child); // comments, PIs, etc.
            continue;
        }

        $tag = strtolower($child->tagName);
        // Dangerous containers: drop the element AND its contents outright.
        if (in_array($tag, ['script', 'style', 'noscript', 'template', 'object', 'embed', 'form'], true)) {
            $node->removeChild($child);
            continue;
        }
        if (!in_array($tag, RT_TAGS, true)) {
            // Disallowed tag: keep its (cleaned) children, drop the wrapper.
            rt_clean_node($child, $doc);
            while ($child->firstChild) {
                $node->insertBefore($child->firstChild, $child);
            }
            $node->removeChild($child);
            continue;
        }

        rt_clean_attributes($child, $tag);
        rt_clean_node($child, $doc); // recurse
    }
}

function rt_clean_attributes(DOMElement $el, string $tag): void
{
    $allowed = RT_ATTRS[$tag] ?? [];

    foreach (iterator_to_array($el->attributes) as $attr) {
        $name = strtolower($attr->name);

        if ($name === 'class') {
            $clean = rt_filter_classes($attr->value);
            if ($clean === '') {
                $el->removeAttribute('class');
            } else {
                $el->setAttribute('class', $clean);
            }
            continue;
        }
        if (!in_array($name, $allowed, true)) {
            $el->removeAttribute($attr->name);
            continue;
        }

        // Per-attribute value validation.
        if ($name === 'href') {
            if (!rt_safe_url($attr->value, ['http', 'https', 'mailto', 'tel'], true)) {
                $el->removeAttribute('href');
            } else {
                $el->setAttribute('rel', 'noopener noreferrer nofollow');
            }
        } elseif ($name === 'src') {
            $schemes = ['http', 'https'];
            if (!rt_safe_url($attr->value, $schemes, true)) {
                $el->removeAttribute('src');
            } elseif ($tag === 'iframe' && !rt_iframe_host_ok($attr->value)) {
                $el->removeAttribute('src');
            }
        } elseif ($name === 'style') {
            $clean = rt_filter_style($attr->value);
            if ($clean === '') {
                $el->removeAttribute('style');
            } else {
                $el->setAttribute('style', $clean);
            }
        } elseif (in_array($name, ['width', 'height'], true)) {
            if (!preg_match('/^\d{1,5}$/', $attr->value)) {
                $el->removeAttribute($name);
            }
        }
    }
}

/** Keep only Quill-style formatting classes + "spoiler". */
function rt_filter_classes(string $value): string
{
    $keep = [];
    foreach (preg_split('/\s+/', trim($value)) as $c) {
        if ($c !== '' && (str_starts_with($c, 'ql-') || $c === 'spoiler')) {
            $keep[] = $c;
        }
    }
    return implode(' ', array_slice($keep, 0, 6));
}

/** Allow only width/height/text-align in inline styles. */
function rt_filter_style(string $value): string
{
    $out = [];
    foreach (explode(';', $value) as $decl) {
        if (!str_contains($decl, ':')) {
            continue;
        }
        [$prop, $val] = array_map('trim', explode(':', $decl, 2));
        $prop = strtolower($prop);
        if (in_array($prop, ['width', 'height'], true) && preg_match('/^\d{1,5}(px|%)$/', $val)) {
            $out[] = "$prop: $val";
        } elseif ($prop === 'text-align' && in_array(strtolower($val), ['left', 'right', 'center', 'justify'], true)) {
            $out[] = "text-align: " . strtolower($val);
        }
    }
    return implode('; ', $out);
}

function rt_safe_url(string $url, array $schemes, bool $allowRelative = true): bool
{
    $url = trim($url);
    if ($url === '') {
        return false;
    }
    // Reject control chars / javascript: tricks.
    if (preg_match('/[\x00-\x1f]/', $url)) {
        return false;
    }
    if (str_starts_with($url, '#') || str_starts_with($url, '/') || str_starts_with($url, './') || str_starts_with($url, '../')) {
        return $allowRelative;
    }
    $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
    return $scheme !== '' && in_array($scheme, $schemes, true);
}

function rt_iframe_host_ok(string $url): bool
{
    $host = strtolower((string) parse_url($url, PHP_URL_HOST));
    return $host !== '' && in_array($host, RT_IFRAME_HOSTS, true);
}

/** Render stored content to the page (re-sanitized). */
function render_html(?string $html): string
{
    return sanitize_html($html);
}

/** Plain-text excerpt for list previews. */
function html_excerpt(?string $html, int $len = 120): string
{
    $text = trim(preg_replace('/\s+/', ' ', strip_tags((string) $html)));
    if (mb_strlen($text) <= $len) {
        return $text;
    }
    return mb_substr($text, 0, $len - 1) . '…';
}
