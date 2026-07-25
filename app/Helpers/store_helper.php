<?php

use App\Models\Store;
use Illuminate\Support\Str;

/**
 * Format stored phone (881303391262) to display format (+880 1303-391262)
 */
function phone_display($phone)
{
    if (!$phone) return null;
    
    // Remove non-digits
    $digits = preg_replace('/[^0-9]/', '', $phone);
    
    // If starts with 88 (BD country code without +)
    if (str_starts_with($digits, '88') && strlen($digits) >= 11) {
        $local = substr($digits, 2);
        // Add leading 0 back if it's a BD number (starts with 1 after 88)
        if (strlen($local) === 10) {
            $local = '0' . $local;
        }
        return '<img src="https://flagcdn.com/16x12/bd.png" class="me-1" style="width:16px;height:12px"> +880 ' . 
               substr($local, 0, 4) . '-' . substr($local, 4);
    }
    
    // For other country codes, just add +
    if (!str_starts_with($digits, '+')) {
        $digits = '+' . $digits;
    }
    
    return $digits;
}

/**
 * Get the current store from session.
 * If no store is selected, returns the default store.
 */
function getCurrentStore()
{
    $storeId = session('current_store_id');
    
    if ($storeId) {
        $store = Store::active()->find($storeId);
        if ($store) {
            return $store;
        }
    }
    
    // Return default store if no store selected or store not found
    return Store::getDefault();
}

/**
 * Get the current store ID from session.
 */
function getCurrentStoreId()
{
    $store = getCurrentStore();
    return $store ? $store->id : null;
}

/**
 * Check if multi-store feature is active.
 * Returns true if there are multiple stores.
 */
function isMultiStoreEnabled()
{
    return Store::count() > 1;
}

/**
 * Get all active stores.
 */
function getActiveStores()
{
    return Store::getActiveStores();
}

/**
 * Format a raw product description into readable HTML.
 *
 * Handles common import issues: &nbsp; entities, missing spaces after
 * periods, concatenated words (LengthWidth), missing spaces between
 * digits and units (18Ltr), and section labels without proper line breaks.
 */
function format_product_description(?string $text, bool $wrapAsParagraph = true): string
{
    if (empty($text)) {
        return '';
    }

    // 1. Replace HTML entities & non-breaking spaces
    $text = str_replace(['&nbsp;', '&amp;'], [' ', '&'], $text);
    $text = preg_replace('/\xA0/u', ' ', $text);

    // 2. Normalize line endings
    $text = str_replace(["\r\n", "\r"], "\n", $text);
    $text = trim($text);

    // 3. Insert space after period followed by capital letter (sentence boundary)
    $text = preg_replace('/(\.)([A-Z])/u', '. $2', $text);

    // 4. Insert space after ] ) ! ? followed by capital letter
    $text = preg_replace('/([)\]!?])([A-Z])/u', '$1 $2', $text);

    // 5. Insert space between digit followed by uppercase letter
    //    (e.g. "18Ltr" -> "18 Ltr", "4Step" -> "4 Step")
    $text = preg_replace('/(?<!\d\.\d)(\d)([A-Z])/u', '$1 $2', $text);

    // 6. Insert space between digit followed by lowercase letter (if it looks like
    //    a word boundary, not a unit abbreviation)
    $text = preg_replace('/(?<!\d\.\d)(\d)([a-z])/u', '$1 $2', $text);

    // 7. Insert space between lowercase letter and digit (e.g. "Weight7500" -> "Weight 7500")
    $text = preg_replace('/([a-z])(\d)/u', '$1 $2', $text);

    // 8. Insert space between uppercase letter and digit when the uppercase letter
    //    ends a word (e.g. "H4-12Weight" -> but we want this to be selective).
    //    Match: uppercase-letter followed by digit where there's a lowercase before it:
    //    "Fascinating Design PatternExtraordinary" -> split at "Extraordinary"
    //    Actually the uppercase after lowercase pattern covers this.
    $text = preg_replace('/([a-z])([A-Z])/u', '$1 $2', $text);

    // 9. Also handle uppercase→uppercase→lowercase concatenations
    //    e.g. "H2-12Fascinating" -> the "F" is after "2" but there's no lowercase.
    //    Handle digit→uppercase where the uppercase is start of a word ≥3 chars
    $text = preg_replace('/([0-9])([A-Z][a-z])/u', '$1 $2', $text);
    $text = preg_replace('/([0-9])([A-Z]{2,})/u', '$1 $2', $text);
    //    Also digit→digit→uppercase: "H2-12Fascinating" -> after "12" and "F"
    $text = preg_replace('/(\d)([A-Z])/u', '$1 $2', $text);

    // 10. Add newline before known section labels
    // Uses negative lookbehind to avoid matching inside compound names
    // like "Water Capacity" (the lookbehind ensures "Capacity" isn't preceded
    // by "Water ").
    $labels = [
        // Primary section headers (always break before these)
        'Product code\s*:',
        'Specification\s*:',
        'Model No\.', 'Model\s*:',
        // Secondary labels - use negative lookbehind to avoid false splits
        '(?<!(?i:Water)\s)Capacity\s*:',
        'Measurement\s*:',
        'Brand\s*:',
        'Material\s*:',
        'Product Name\s*:',
        // Measurement dimensions
        'Height\s*[-:]', 'Width\s*[-:]', 'Length\s*[-:]',
        // Weight - but only when followed by a digit (not "Weightless" etc.)
        'Weight\s*(?=\d)',
    ];
    $labelPattern = '/\n?(?<!\n)(' . implode('|', $labels) . ')/iu';
    $text = preg_replace($labelPattern, "\n$1", $text);

    // 11. Collapse multiple newlines, trim leading newline
    $text = preg_replace("/\n{2,}/", "\n", $text);
    $text = preg_replace("/^\n/", '', $text);

    // 12. Collapse multiple spaces
    $text = preg_replace('/[ ]{2,}/', ' ', $text);

    // 13. Clean up spaces around newlines
    $text = preg_replace('/ +\n/', "\n", $text);
    $text = preg_replace('/\n +/', "\n", $text);

    // 14. Remove stray leading period-newline artifacts
    $text = preg_replace("/\n\. /", "\n", $text);

    // 15. Fix ".  " (double space after period)
    $text = preg_replace('/\.  /', '. ', $text);

    // 16. SECOND LABEL PASS - only for labels that may have been created by
    //     word-splitting (e.g. "Specification" in "AvailableSpecification"
    //     split to "Available Specification"). But use a more restricted set
    //     to avoid false positives.
    $secondLabels = [
        'Specification\s*:',
        'Product code\s*:',
        'Product Code\s*:',
        'Model No\.',
    ];
    $secondPattern = '/\n?(?<!\n)(' . implode('|', $secondLabels) . ')/iu';
    $text = preg_replace($secondPattern, "\n$1", $text);
    $text = preg_replace("/\n{2,}/", "\n", $text);
    $text = preg_replace("/^\n/", '', $text);
    $text = preg_replace('/ +\n/', "\n", $text);
    $text = preg_replace('/\n +/', "\n", $text);

    // 18. Convert to HTML: wrap paragraphs (<p>), single newlines become <br>
    $paragraphs = preg_split("/\n{2,}/", $text);
    $parts = [];
    foreach ($paragraphs as $para) {
        $para = trim($para);
        if ($para === '') {
            continue;
        }
        $parts[] = nl2br(e($para));
    }

    if (empty($parts)) {
        return '';
    }

    if ($wrapAsParagraph) {
        return '<p>' . implode("</p>\n<p>", $parts) . '</p>';
    }

    return implode("<br>\n", $parts);
}
