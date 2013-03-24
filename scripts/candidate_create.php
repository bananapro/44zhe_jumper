<?php
require './common.php';

$task_name = @$argv[1];

if(!$task_name){
    echo 'Please enter target name!';
    br();die();
}

$path = '/Users/jon/Kupan.localized/candidate/';

$file = file_get_contents($path . $task_name);
if(!$file){
    echo 'task is not exist!';
    br();die();
}

$lines = explode("\n", $file);
$new_id = array();
$output = '';
$i = 0;
foreach ($lines as $line){
    
    $line = trim($line);
    if(!preg_match('/^[a-z\_0-9]+$/i', $line)){
        continue;
    }
    
    if(strlen($line)<6){
        continue;
    }
    
    $line = low($line);
    $line[rand(0, strlen($line)-1)] = getRandom(1);
    $new = $line.rand(0,9);
    
    if(!isset($new_id['username'])){
        
        $new_id = array();
        $new_id['username'] = $new;
        continue;
    }else{
        
        $new_id['email'] = getRandom(2,1) . $new .rand(10,99) . '@163.com';
        if(strlen($new_id['email'])>25){
            unset($new_id['email']);
            continue;
        }
        
        $i++;
        $output .= "insert into user_candidate(username,email) values('{$new_id['username']}', '{$new_id['email']}');\n";
        unset($new_id);
    }    
}

file_put_contents($path . 'out_'.$task_name, $output);
echo "Total: " . $i . " id created!";
br();

?>