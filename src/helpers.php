<?php

use App\Session;
use Dotenv\Exception\InvalidFileException;

function can(string $permission, ?int $department_id = null)
{
    $user = Session::getInstance()->get('user');

    if (empty($user)) {
        return false;
    }

    $isSuperUser = $user['is_super'] ?? false;
    $isSuperDepartment = $user['department']['is_super'] ?? false;
    $isOwner = $user['id'] == ($user['department']['owner_id'] ?? false) && $department_id == ($user['department']['id'] ?? false);

    $permissions = [
        'create' => $isSuperUser || $isSuperDepartment,
        'update' => $isSuperUser || $isSuperDepartment || $isOwner,
        'destroy' => $isSuperUser || $isSuperDepartment,
    ];

    if ($permission === '*') {
        return $permissions;
    }

    return $permissions[$permission] ?? false;
}

function get_colors(bool $only_keys = false)
{
    $colors = [
        'is-warning' => 'amarelo',
        'is-info' => 'azul',
        'is-link' => 'indigo',
        'is-dark' => 'preto',
        'is-danger' => 'rosa',
        'is-primary' => 'turquesa',
        'is-success' => 'verde',
    ];

    if ($only_keys) {
        return array_keys($colors);
    }

    return $colors;
}

function is_color(string $color)
{
    return array_key_exists($color, get_colors());
}

function get_statuses(bool $only_keys = false): array
{
    $statuses = [
        'draft' => 'Rascunho',
        'published' => 'Publicado',
    ];

    if ($only_keys) {
        return array_keys($statuses);
    }

    return $statuses;
}

function get_status(string $key): string
{
    if (is_status($key) == false) {
        return $key;
    }

    return get_statuses()[$key];
}

function is_status(string $status): bool
{
    return array_key_exists($status, get_statuses());
}

function switch_vars($a, $b, $expected): array
{
    if ($b == $expected) {
        return [$a, $expected];
    }

    return [$b, $a];
}

function bcrypt(string $password): string|false|null
{
    return password_hash($password, PASSWORD_DEFAULT);
}

function filter_protected(array $data, array $protected): array
{
    return array_filter($data, fn ($key) => !in_array($key, $protected), ARRAY_FILTER_USE_KEY);
}

function now(): string|false
{
    return date('Y-m-d H:i:s');
}

function today(string $str = null): string|false
{
    $format = 'Y-m-d';

    if ($str) {
        return date($format, strtotime($str));
    }

    return date($format);
}

function slugify($string)
{
    $string = htmlentities($string, ENT_QUOTES, 'UTF-8');
    $string = preg_replace('~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', $string);
    $string = html_entity_decode($string, ENT_QUOTES, 'UTF-8');
    $string = preg_replace('~[^0-9a-z]+~i', '-', $string);
    $string = trim($string, '-');
    $string = strtolower($string);

    return $string;
}

/** 
 * Truncates text.
 *
 * Cuts a string to the length of $length and replaces the last characters
 * with the ending if the text is longer than length.
 *
 * @param string $text String to truncate.
 * @param integer $length Length of returned string, including ellipsis.
 * @param string $ending Ending to be appended to the trimmed string.
 * @param boolean $exact If false, $text will not be cut mid-word
 * @param boolean $considerHtml If true, HTML tags would be handled correctly
 * @return string Trimmed string.
 */
function truncate($text, $length = 100, $ending = '...', $exact = true, $considerHtml = false)
{
    if ($considerHtml) {
        // if the plain text is shorter than the maximum length, return the whole text
        if (strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
            return $text;
        }

        // splits all html-tags to scanable lines
        preg_match_all('/(<.+?>)?([^<>]*)/s', $text, $lines, PREG_SET_ORDER);

        $total_length = strlen($ending);
        $open_tags = array();
        $truncate = '';

        foreach ($lines as $line_matchings) {
            // if there is any html-tag in this line, handle it and add it (uncounted) to the output
            if (!empty($line_matchings[1])) {
                // if it’s an “empty element” with or without xhtml-conform closing slash (f.e.)
                if (preg_match('/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is', $line_matchings[1])) {
                    // do nothing
                    // if tag is a closing tag (f.e.)
                } else if (preg_match('/^<\s*\/([^\s]+?)\s*>$/s', $line_matchings[1], $tag_matchings)) {
                    // delete tag from $open_tags list
                    $pos = array_search($tag_matchings[1], $open_tags);

                    if ($pos !== false) {
                        unset($open_tags[$pos]);
                    }
                    // if tag is an opening tag (f.e. )
                } else if (preg_match('/^<\s*([^\s>!]+).*?>$/s', $line_matchings[1], $tag_matchings)) {
                    // add tag to the beginning of $open_tags list
                    array_unshift($open_tags, strtolower($tag_matchings[1]));
                }

                // add html-tag to $truncate’d text
                $truncate .= $line_matchings[1];
            }

            // calculate the length of the plain text part of the line; handle entities as one character
            $content_length = strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', ' ', $line_matchings[2]));
            if ($total_length + $content_length > $length) {
                // the number of characters which are left
                $left = $length - $total_length;
                $entities_length = 0;

                // search for html entities
                if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', $line_matchings[2], $entities, PREG_OFFSET_CAPTURE)) {
                    // calculate the real length of all entities in the legal range
                    foreach ($entities[0] as $entity) {
                        if ($entity[1] + 1 - $entities_length <= $left) {
                            $left--;
                            $entities_length += strlen($entity[0]);
                        } else {
                            // no more characters left
                            break;
                        }
                    }
                }
                $truncate .= substr($line_matchings[2], 0, $left + $entities_length);
                // maximum lenght is reached, so get off the loop
                break;
            } else {
                $truncate .= $line_matchings[2];
                $total_length += $content_length;
            }

            // if the maximum length is reached, get off the loop
            if ($total_length >= $length) {
                break;
            }
        }
    } else {
        if (strlen($text) <= $length) {
            return $text;
        } else {
            $truncate = substr($text, 0, $length - strlen($ending));
        }
    }

    // if the words shouldn't be cut in the middle...
    if (!$exact) {
        // ...search the last occurance of a space...
        $spacepos = strrpos($truncate, ' ');
        if (isset($spacepos)) {
            // ...and cut the text in this position
            $truncate = substr($truncate, 0, $spacepos);
        }
    }

    // add the defined ending to the text
    $truncate .= $ending;

    if ($considerHtml) {
        // close all unclosed html-tags
        foreach ($open_tags as $tag) {
            $truncate .= '';
        }
    }

    return $truncate;
}

function upload_image(string $tmp_name, int $width, int $height = -1)
{
    if ($height < 1) {
        $height = $width;
    }

    if (file_exists($tmp_name) == false) {
        throw new InvalidFileException;
    }

    [
        $originWidth,
        $originHeight
    ] = getimagesize($tmp_name);

    $data = file_get_contents($tmp_name);
    $origin = imagecreatefromstring($data);
    $final = imagecreatetruecolor($width, $height);
    $filename = bin2hex(random_bytes(12));
    $filename = "uploads/{$filename}.png";

    imagecopyresized($final, $origin, 0, 0, 0, 0, $width, $height, $originWidth, $originHeight);
    imagepng($final, $filename);

    return $filename;
}
