<?php

// Deleting substance(s)
if(isset($task_connect->fields) && is_object($task_connect->fields)) {
    foreach($task_connect->fields as $field) {
        $file = SUBSTANCE . DS . $field;
        if(file_exists($file) && is_file($file)) {
            File::open($file)->delete();
        }
    }
}