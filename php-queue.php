<?php

include_once ("lib/file.php");


/**
 * Class Php_queue
 */
Class Php_queue {

    private $file = null;
    private $name = "";
    private $path = "";


    /**
     * Php_queue constructor.
     * @param string $unique_name, Unique name for queue.
     * @param string $path, Path for locate the file for queue.
     */
    function __construct($unique_name, $path = "_temp") {
        $work_dir = getcwd();
        $this->name = "{$unique_name}.queue";
        $this->path = "{$work_dir}/{$path}/";
        $this->file = new File($this->name, $this->path);
    }

    /**
     * Return the status of queue
     * @return array
     */
    public function status() {
        $arrStatus = [];
        $arrStatus["pending"] = $this->file->check_num_rows();
        return $arrStatus;
    }


    /**
     * Return the last works pending in queue
     * @param int $works, number of pending works
     * @return string
     */
    public function get($works = 10) {
        return $this->file->tail_rows($works);
    }


    /**
     * Process the last works pending
     * @param int $works, Works for process
     * @param $callback, Function to send works for process.
     * @return array
     */
    public function process($works = 10, $callback) {

        // Save the process status
        $processed = [];

        // If have works
        if($worksForProcess = $this->get($works)){

            // Explode by endline
            $worksArray = explode("\n", $worksForProcess);

            // Each to works
            foreach ($worksArray as $work) {

                // If the callback is a function
                if (is_callable($callback)) {

                    // Decode work
                    if ($work_decode = @json_decode($work)) {
                        $work = $work_decode;
                    }

                    // Call process callback
                    if ($process_status = call_user_func($callback, $work)) {
                        $processed["success"][] = $work;
                    }
                    else {
                        $processed["fail"][] = $work;
                    }
                }
            }
        };

        return $processed;
    }


    /**
     * Add one work to the queue
     * @param array $item, array for add
     */
    public function put($item = []) {
        if (is_array($item) || is_object($item)){
            $item = json_encode($item);
        }
        $this->file->add_row(trim($item));
    }


    /**
     * Reset the queue to empty state
     * @return bool
     */
    public function reset() {
        if ($this->file->delete()) {
            $this->file = new File($this->name, $this->path);
        }
        else{
            return false;
        }
    }
}