<?php

function dd($var) {
    print_r("<pre>");
    print_r($var);
    print_r("</pre>");
}

Class File {

    private $file;
    private $pathfile;
    private $file_has_endline;

    function __construct($name, $path) {

        $path = trim($path, "/");
        $name = trim($name, "/");

        $this->pathfile = "{$path}/{$name}";

        if (!file_exists($path)) {
            mkdir($path);
        }

        // Create queue file
        if (!file_exists($this->pathfile)) {
            if($handle = fopen($this->pathfile, 'w')) {
                fclose($handle);
            };
        }

        $this->file = fopen($this->pathfile, "a+");

        // Check endline
        $this->file_has_endline = $this->check_file_endline();
    }

    public function check_size() {
        return filesize($this->pathfile);
    }

    public function check_file_endline() {
        // Set pointer to end
        fseek($this->file, -1, SEEK_END);

        // get the last char
        $lastChar = fread($this->file, 1);

        if ($this->check_size() == 0) {
            return true;
        }
        else{
            return ($lastChar == "\n");
        }
    }

    public function check_num_rows() {

        // Set pointer to start
        rewind($this->file);

        $linecount = 0;
        while(!feof($this->file)) {
            $line = fgets($this->file, 4096);
            $linecount += substr_count($line, "\n");
        }

        return $linecount;
    }

    public function add_row($data = "", $recheck_endline = false) {

        // Recheck if the file end with endline
        if ($recheck_endline) {
            $this->file_has_endline = $this->check_file_endline();
        }

        // If the file has endline, use this and append another endline for next
        if ($this->file_has_endline) {
            $data = "{$data}\n";
        }
        // If the file not has endline, append once to start line and end.
        else{
            $data = "\n{$data}\n";
        }

        return fwrite($this->file, $data);
    }

    public function tail_rows_delete($rows = 1) {

        // Open file
        $linesToRemove = $this->tail_rows($rows);

        // Get size
        $end = ftell($this->file);

        // If the last char is \n
        if ($this->check_file_endline()) {
            $end -= 2;
        }

        // Truncate to size
        $truncateTo = $end - strlen($linesToRemove);

        // truncate
        return ftruncate ( $this->file, $truncateTo );
    }

    /**
     * Slightly modified version of http://www.geekality.net/2011/05/28/php-tail-tackling-large-files/
     * @author Torleif Berger, Lorenzo Stanco
     * @link http://stackoverflow.com/a/15025877/995958
     * @license http://creativecommons.org/licenses/by/3.0/
     */
    public function tail_rows($rows = 1) {
        // Open file
        if ($this->file === false) return false;
        // Sets buffer size, according to the number of lines to retrieve.
        // This gives a performance boost when reading a few lines from the file.
        $buffer = ($rows < 2 ? 64 : ($rows < 10 ? 512 : 4096));
        // Jump to last character
        fseek($this->file, -1, SEEK_END);
        // Read it and adjust line number if necessary
        // (Otherwise the result would be wrong if file doesn't end with a blank line)
        if (fread($this->file, 1) != "\n") $rows -= 1;

        // Start reading
        $output = '';
        $chunk = '';
        // While we would like more
        while (ftell($this->file) > 0 && $rows >= 0) {
            // Figure out how far back we should jump
            $seek = min(ftell($this->file), $buffer);
            // Do the jump (backwards, relative to where we are)
            fseek($this->file, -$seek, SEEK_CUR);
            // Read a chunk and prepend it to our output
            $output = ($chunk = fread($this->file, $seek)) . $output;
            // Jump back to where we started reading
            fseek($this->file, -mb_strlen($chunk, '8bit'), SEEK_CUR);
            // Decrease our line counter
            $rows -= substr_count($chunk, "\n");
        }
        // While we have too many lines
        // (Because of buffer size we might have read too many)
        while ($rows++ < 0) {
            // Find first newline and remove all text before that
            $output = substr($output, strpos($output, "\n") + 1);
        }
        // Close file and return
        return trim($output);
    }

    public function close_file() {
        fclose($this->file);
    }

    public function delete() {
        if (file_exists($this->pathfile)) {
            $this->close_file();
            return unlink($this->pathfile);
        }
        else{
            return true;
        }
    }
}