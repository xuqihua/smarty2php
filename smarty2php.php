<?php

class SmartyToPHP{


    public static $template_path;
    public static $compile_path;
    public static $exclude = array('.', '..', '.svn');

    /**
     * set the path to compile;
     * @param array $config
     */
    public static function config($config = array()) {
        if(isset($config['template_path'])) {
            self::$template_path = $config['template_path'];
        } else {
            self::$template_path = './';
        }

        if(isset($config['compile_path'])) {
            self::$compile_path = $config['compile_path'];
        } else {
            self::$compile_path = './compile/';
        }

        if(!is_dir(self::$compile_path)) {
            mkdir(self::$compile_path, 0777, true);
        }
    }

    public static function fly() {
        $files = self::rscandir(self::$template_path);
        foreach($files as $file) {
            $content = file_get_contents($file);
            $parse_content = self::parse($content);
            $file_path = self::$compile_path.$file;
            $file_dir = dirname($file_path);
            if(!is_dir($file_dir)) {
                mkdir($file_dir, 0777, true);
            }
            file_put_contents($file_path,$parse_content);
        }
    }

    /**
     * get the string after parse
     *
     * @param string $content
     * @return string
     */
    public static function parse($content = '') {
        //comment
        $content = preg_replace('#\{\*(.*?)\*\}#is','',$content);

        //匹配带点的字符串 fix array
        $content = preg_replace_callback('#\$(?:[\w\-\>]+\.)+[\w\-\>]+#i',function($m) {
            $array = explode('.',$m[0]);
            $fix = '';
            foreach($array as $k => $v) {
                if($k == 0) {
                    $fix .= $v;
                } else {
                    $fix .= '[\''.$v.'\']';
                }
            }
            return $fix;
        },$content);

        //if
        $content = preg_replace_callback('#\{if(.*?)\}#is',function($m) {
            return '<?php if('.$m[1].') { ?>';
        },$content);
        //elseif
        $content = preg_replace_callback('#\{elseif\s(.*?)\}#i',function($m) {
            return '<?php } elseif ('.$m[1].') { ?>';
        },$content);
        //foreach
        $content = preg_replace_callback('#\{foreach\s(.*?)\}#i',function($m) {
            $value = explode(" ",trim($m[1]));
            $array = array();
            foreach($value as $v) {
                $temp = explode('=',$v);
                $array[$temp[0]] = $temp[1];
            }
            $key = isset($array['key']) ? '$'.$array['key'].' => ' : '';
            return '<?php foreach('.$array['from'].' as '.$key.' $'.$array['item'].') { ?>';
        },$content);


        //echo var
        $content = preg_replace_callback('#\{\$(.*?)\}#i',function($m) {
            return '<?php echo $'.$m[1].'; ?>';
        },$content);


        //end else if foreach
        $content = str_replace(
            array('{else}', '{/if}', '{/foreach}', '{/literal}', '{literal}'),
            array('<?php } else { ?>', '<?php } ?>', '<?php } ?>', '', ''),
            $content);

        return $content;
    }

    /**
     * scan files
     *
     * @param string $base
     * @param array $data
     * @return array
     */
    public static function rscandir($base = '.', &$data = array()) {
        $ds = '/';
        $base = rtrim($base,$ds).$ds;
        $array = array_diff(scandir($base), self::$exclude);
        foreach ($array as $value) {
            if (is_dir($base . $value)) {
                $data[] = $base . $value . $ds;
                $data = self::rscandir($base . $value . $ds, $data);
            } elseif (is_file($base . $value)) {
                $data[] = $base . $value;
            }
        }
        return $data;
    }
}