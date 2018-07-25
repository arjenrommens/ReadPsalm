<?php
ini_set('max_execution_time', 0); // exec can take long
ini_set('implicit_flush', 1);
ini_set("zlib.output_compression", 0); 
        

/**
 * 2018-7-24 v0.1 - By Arjen Rommens
 * 
 * Vimeo/Psalm Interpreter and Pretty Output 
 * Process Psalm Output and display in table which can be sorted and filtered
 * 
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
    
    /**
     * Absolute path to ReadPsalm project directory
     * @var type
     */
    private $_project_dir_abs = "/mnt/hgfs/www_host/IPS_TP/"; 
    private $_psalm_bin = "../IPS_TP/vendor/bin/psalm";    
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
        echo $this->array2table($this->_errors);
    }

    // TODO
    public function RunPsalm()
    {        
        $command = "{$this->_psalm_bin} -m --root={$this->_project_dir_abs} --debug > {$this->_psalm_out_file}";
        
        echo $command;
        echo '<br />Please wait...';
        
        
        flush();
        ob_flush();
        
        exec($command);
        echo '<meta http-equiv="refresh" content="0; URL=index.php">';
    }

    private function array2table($array)
    {
        if(count($array) == 0 )
            return ;
        
        $out = '';
        
        $out .= '<table id="psalm">';

        $out .= '<tr>';

        foreach ($array[0] as $field => $val)
        {
            $out .= '<td>' . $field . '</td>';
        }

        $out .= '</tr>';

        foreach ($array as $row)
        {
            $out .= '<tr>';
            foreach ($row as $field)
            {
                $out .= '<td>' . $field . '</td>';
            }
            $out .= '</tr>';
        }

        $out .= '</table>';
        
        return $out;
    }
}
?>
<html>
    <body>
        
        <a href="?run=1">RUN</a>

        
        <?php $pr->Draw(); ?>
        <script src="js/tablefilter/tablefilter.js"></script>

        <script data-config>
            var filtersConfig = {
                base_path: 'js/tablefilter/',
                col_0: 'select',
                col_1: 'select',
                col_2: 'select',
                col_3: 'select',
                alternate_rows: true,
                rows_counter: true,
                btn_reset: true,
                loader: true,
                status_bar: false,
                mark_active_columns: true,
                highlight_keywords: true,
                col_types: [
                    'string', 'string', 'number',
                    'number', 'number', 'number',
                    'number', 'number', 'number'
                ],
                /*custom_options: {
                    cols:[3],
                    texts: [[
                        '0 - 25 000',
                        '100 000 - 1 500 000'
                    ]],
                    values: [[
                        '>0 && <=25000',
                        '>100000 && <=1500000'
                    ]],
                    sorts: [false]
                },
                /*col_widths: [
                    '150px', '100px', '100px',
                    '100px', '100px', '100px',
                    '70px', '60px', '60px'
                ],*/
                extensions:[{ name: 'sort' }]
            };

            var tf = new TableFilter('psalm', filtersConfig);
            tf.init();

        </script>
    </body>
</html>
