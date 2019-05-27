<?php

function dd($var) {
    print_r("<pre>");
    print_r($var);
    print_r("</pre>");
}

Class File {

    private $file;

    function __construct($filepath) {
        $this->file = fopen($filepath, "c+");
    }

    function add_row() {

    }

    function tail_rows_delete($rows = 1) {

        // Open file
        $linesToRemove = $this->tail_rows($rows);

        // Set pointer to end
        fseek($this->file, -1, SEEK_END);

        // get the last char
        $lastChar = fread($this->file, 1);

        // Set pointer to end
        fseek($this->file, 0, SEEK_END);

        // Get size
        $end = ftell($this->file);

        // If the last char is \n
        if ($lastChar == "\n") {
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

    function close_file() {
        fclose($this->file);
    }
}