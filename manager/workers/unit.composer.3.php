<?php

$fields = Get::state_field($segment);

Weapon::fire('unit_composer_3_before', array($segment, $fields));

if( ! isset($default->fields)) {
    $default->fields = array();
}

if( ! empty($fields)) {
    $html = "";
    $field = Guardian::wayback('fields', Mecha::A($default->fields));
    foreach($fields as $key => $value) {
        if(Notify::errors()) {
            $field[$key] = isset($field[$key]['value']) ? $field[$key]['value'] : "";
        }
        $type = $value['type'][0];
        if(strpos(',' . $value['scope'] . ',', ',' . $segment . ',') !== false) {
            $title = $value['title'] . (isset($value['description']) && trim($value['description']) !== "" ? ' <span class="text-info help" title="' . Text::parse($value['description'], '->encoded_html') . '">' . Jot::icon('question-circle') . '</span>' : "");
            $html .= Form::hidden('fields[' . $key . '][type]', $type);
            if($type === 't') {
                $html .= '<label class="grid-group grid-group-text">';
                $html .= '<span class="grid span-2 form-label">' . $title . '</span>';
                $html .= '<span class="grid span-4">';
                $html .= Form::text('fields[' . $key . '][value]', isset($field[$key]) ? $field[$key] : $value['value'], $value['value'], array(
                    'class' => 'input-block'
                ));
                $html .= '</span>';
                $html .= '</label>';
            } else if($type === 'b') {
                $html .= '<div class="grid-group grid-group-boolean">';
                $html .= '<span class="grid span-2"></span>';
                $html .= '<span class="grid span-4">';
                $html .= Form::checkbox('fields[' . $key . '][value]', ! empty($value['value']) ? $value['value'] : '1', isset($field[$key]) && ! empty($field[$key]), $title);
                $html .= '</span>';
                $html .= '</div>';
            } else if($type === 'o') {
                $html .= '<label class="grid-group grid-group-option">';
                $html .= '<span class="grid span-2 form-label">' . $title . '</span>';
                $html .= '<span class="grid span-4">';
                $options = array();
                $selected = isset($field[$key]) ? isset($field[$key]) : "";
                foreach(explode("\n", $value['value']) as $v) {
                    $v = trim($v);
                    if(strpos($v, ':') !== false) {
                        $v = explode(':', $v, 2);
                        $options[trim($v[0])] = trim($v[1]);
                    } else {
                        $options[$v] = $v;
                    }
                }
                $html .= Form::select('fields[' . $key . '][value]', $options, $selected, array(
                    'class' => 'select-block'
                ));
                $html .= '</span>';
                $html .= '</label>';
            } else if($type === 'f') {
                $v = isset($field[$key]) && $field[$key] !== "" ? $field[$key] : false;
                $has_asset = $v !== false && file_exists(SUBSTANCE . DS . $v) && is_file(SUBSTANCE . DS . $v);
                $html .= '<div class="grid-group grid-group-file' . ($has_asset ? ' grid-group-boolean' : "") . '">';
                $html .= ! $has_asset ? '<span class="grid span-2 form-label">' . $title . '</span>' : '<span class="grid span-2"></span>';
                $html .= '<span class="grid span-4">';
                if( ! $has_asset) {
                    $html .= Form::file($key);
                    $e = strtolower(str_replace(' ', "", $v));
                    $html .= $v !== false ? Form::hidden('fields[' . $key . '][accept]', $e) . '<br><small class="text-info"><strong>' . $speak->accept . ':</strong> <code>' . str_replace(',', '</code>, <code>', $e) . '</code></small>' : "";
                } else {
                    $html .= Form::hidden('fields[' . $key . '][value]', $v);
                    $html .= '<span title="' . strip_tags($value['title']) . '">' . Form::checkbox('fields[' . $key . '][remove]', $v, false, $speak->delete . ' <code>' . $v . '</code>') . '</span>';
                }
                $html .= '</span>';
                $html .= '</div>';
            } else { // if($value['type'][0] === 's') {
                $html .= '<label class="grid-group grid-group-summary">';
                $html .= '<span class="grid span-2 form-label">' . $title . '</span>';
                $html .= '<span class="grid span-4">';
                $html .= Form::textarea('fields[' . $key . '][value]', isset($field[$key]) ? $field[$key] : $value['value'], $value['value'], array(
                    'class' => 'input-block'
                ));
                $html .= '</span>';
                $html .= '</label>';
            }
        }
    }
    echo ! empty($html) ? $html : Cell::p(Config::speak('notify_empty', strtolower($speak->fields)));
} else {
    echo Cell::p(Config::speak('notify_empty', strtolower($speak->fields)));
}

Weapon::fire('unit_composer_3_after', array($segment, $fields));