<?php

/**
 * 2018-7-24 v0.1 - By Arjen Rommens
 * 
 * Vimeo/Psalm Interpreter and Pretty Output 
 * Process Psalm Output and display in table
 * 
 * Todo: 
 * - filtering on error & file
 * - Run Psalm from this interface
 * 
*/

$pr = new ReadPsalm();

if( ($_GET['run'] ?? FALSE) === '1')
{
    $pr->RunPsalm();
    
    // redirect and reload
    
    exit;
}

class ReadPsalm {
    
    private $_psalm_out_file = "../IPS_TP/psalm.out";
    private $_errors = array();
    
    function __construct()
    {
        $this->Read();
    }
    
    public function Read()
    {
        if( !file_exists($this->_psalm_out_file ) )
        {
            return FALSE;
        }
        
        $data = file_get_contents($this->_psalm_out_file);
        
        if($data == '')
        {
            return FALSE;
        }

        preg_match_all("/ERROR: (.+)/", $data, $output_array);

        if( count($output_array) == 0)
        {
            return FALSE;
        }
        
        foreach($output_array[1] as &$error )
        {
            list($error_code, $file, $description) = explode(" - ", $error );
            list($path, $line, $char) = explode(":", $file);

            array_push($this->_errors, (object)array(
                'ErrorCode' => $error_code,
                'Path' => $path,
                'Line' => $line,
                'Char' => $char,
                'Description' => $description,
            ));
        }

        return true;
    }
    
    public function Draw()
    {
        $this->array2table($this->_errors);
    }

    // TODO
    public function RunPsalm()
    {
        $command = "./vendor/bin/psalm -m --debug > {$this->_psalm_out_file}";
        echo $command;
        exec($command);
    }

    private function array2table($array)
    {
        if(count($array) == 0 )
            return ;
        
        
        echo '<table border="1">';

        echo '<tr>';

        foreach ($array[0] as $field => $val)
        {
            echo '<td>' . $field . '</td>';
        }

        echo '</tr>';

        foreach ($array as $row)
        {
            echo '<tr>';
            foreach ($row as $field)
            {
                echo '<td>' . $field . '</td>';
            }
            echo '</tr>';
        }

        echo '</table>';
        echo count($array);
    }

}
?>
<a href="?run=1">RUN</a>
<?php
$pr->Draw();