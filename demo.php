<?php
include_once("php-queue.php");

// Timing
function microtime_float() {
    list($useg, $seg) = explode(" ", microtime());
    return ((float)$useg + (float)$seg);
}

$start_time = microtime_float();


// Start new queue
$queue = new Php_queue("queue_test");

// Reset queue
$queue->reset();

// Add to queue
for ($i = 0; $i < 300; $i++) {
    $arrTMP = [];
    $arrTMP["value_1"] = base64_encode(uniqid("", true));
    $arrTMP["value_2"] = md5(uniqid());

    // Add array to queue
    $queue->put($arrTMP);
}

// Process queue
$process_estatus = $queue->process(5, function($item) {
    return true;
});

dd($process_estatus);

// Show the queue status
dd($queue->status());



$end_time = microtime_float();
$tiempo = $end_time - $start_time;

dd("Time: {$tiempo}");