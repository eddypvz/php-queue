# PHP Queue
This library makes an LIFO (Last In, First Out) queue stored in a file. 
Is not necessary database or other things to save the items in the queue.
Works with files up to 1gb of size. The library has not been tested with files more bigger.

>If you need storage something (an string or array) in an file, this library can work fine adding only one row to queue and retrive when you need it.

## Installing the library
You need clone the library or download for use it.

### Use Git

```bash
mkdir PL_API
git init
git remote add PL_API git@github.com:eddypvz/php-queue.git
git pull php_queue master
git remote remove php_queue
```

### Use Zip

1. Download and extract in folder: <br/>
    <https://github.com/eoperezpl/php-queue/archive/master.zip>

## Using the library

Here is an example:

```php
// Include the library
include_once("php-queue.php");

// Timing functions
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

// Debug the process status
dd($process_estatus);

// Debug the queue status
dd($queue->status());


// End timing functions
$end_time = microtime_float();
$tiempo = $end_time - $start_time;

dd("Time: {$tiempo}");

```


## License

[The MIT License](http://piecioshka.mit-license.org) @ 2017
