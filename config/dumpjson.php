<?php

require_once(dirname(__DIR__) . '/vendor/autoload.php');

ob_start();
ob_implicit_flush(false);

function FixedJsonEncode($data)
{
    if (!function_exists('json_encode')) {
        return json_encode($data);
    }
    $type = gettype($data);
    switch ($type) {
        case 'NULL':
            return 'null';
        case 'boolean':
            return ($data ? 'true' : 'false');
        case 'integer':
        case 'double':
        case 'float':
            return $data;
        case 'string':
            return '"' . addslashes($data) . '"';
        case 'object':
            $data = get_object_vars($data);
            return FixedJsonEncode($data);
        case 'array':
            $output_index_count = 0;
            $output_indexed = array();
            $output_associative = array();
            foreach ($data as $key => $value) {
                $output_indexed[] = FixedJsonEncode($value);
                $output_associative[] = FixedJsonEncode($key) . ':' . FixedJsonEncode($value);
                if ($output_index_count !== NULL && $output_index_count++ !== $key) {
                    $output_index_count = NULL;
                }
            }
            if ($output_index_count !== NULL) {
                return '[' . implode(',', $output_indexed) . ']';
            } else {
                return '{' . implode(',', $output_associative) . '}';
            }
        default:
            return ''; // Not supported
    }
}

$infile = !empty($argv[1]) ? trim($argv[1]) : '';
$json_arr = is_file($infile) ? include_once($infile) : [];
unset($json_arr['content'], $json_arr['services'], $json_arr['super'], $json_arr['agent'], $json_arr['parent'], $json_arr['sub']);
$json_str = FixedJsonEncode($json_arr);

ob_end_clean();
echo $json_str;
