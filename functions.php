<?php 

if(!function_exists("file_put_contents")){
    /**
     * file_put_contents PHP4 replace
     *
     * @param string $file
     * @param string $data
     * @return boolean
     */
    function file_put_contents($file,$data){
        $fp = fopen($file,"w");
        if(!$fp){
            return false;
        }
        fwrite($fp,$data);
        fclose($fp);
        return true;
    }
}


if(!function_exists("file_get_contents")){
    /**
     * file_put_contents PHP4 replace
     *
     * @param string $file
     * @return mixed
     */
    function file_get_contents($file){
        $fp = fopen($file,"r");
        if(!$fp){
            return false;
        }
        $result = "";
        while (!feof($fp)) {
            $result .= fread($fp,1024);    
        }
        fclose($fp);
        return $result;
    }
}

/**
 * parses update.ver 
 * 
 * @param string $db 
 * @return array 
 */ 
function parseDB($db){
    $result = array();
    $last_section = "";
    $lines = explode("\n",$db);

    foreach ($lines as $line){
        $line = trim($line);
        if(!empty($line)){
            if(@$line[0] == "[" and $line[strlen($line)-1]=="]"){
                $last_section = trim($line,"[]");
                $result[$last_section] = array();
            }else{
                @list($var,$val) = explode("=",$line);
                $result[$last_section][$var] = $val;
            }
        }
    }

    return $result;
}

/**
 * Creates update.ver from array 
 * 
 * @param unknown_type $arr 
 * @return unknown 
 */ 
function createDB($arr){
    $return = "";
    foreach ($arr as $section=>$params){
        $return .= "[{$section}]\n";
        foreach ($params as $key=>$value){
            $return .= "{$key}={$value}\n";
        }
    }
    return $return;
}

/**
 * Small function to help parse HTTP Headers 
 * 
 * @param unknown_type $array 
 * @return unknown 
 */ 
function parseHeader($array){
    $result = array();
    foreach ($array as $value){
        if(substr_count($value,":")){
            $data = explode(":",$value);
            $result[trim($data[0])] = trim($data[1]);
        }
    }
    return $result;
}

/**
 * Downloads file from given host 
 * 
 * @param string $host HTTP Host 
 * @param string $file File on host to download 
 * @param string $save If not empty - save to file 
 * @param string $user HTTP Auth User 
 * @param string $password HTTP Auth Password 
 * @return mixed 
 */ 
function getHTTPFile($host,$file,$save="",$user="",$password=""){
    $host = trim(str_replace("http://","",$host),"/");

    $data = "";
    $last_percent = 0;
    $user_password = ($user)?"$user".(($password)?":{$password}":"")."@":"";

    $open_url = "http://{$user_password}{$host}/{$file}";

    $fp = fopen($open_url,"r",false,stream_context_create(array('http'=>array('user_agent'=>'WGET'))));


    if($fp){

        if($save){
            echo "Creating file {$save}\n";
            $sp = fopen($save,"w+",false,stream_context_create(array('ftp' => array('overwrite' => true))));

            if(!$sp){
                echo "Error: Failed to create file!!!\n"; return false;
            }
        }

        echo "Downloading {$open_url}: ";
        $params = stream_get_meta_data($fp);
        $params = parseHeader($params['wrapper_data']);
        $length = $params['Content-Length'];

        while (!feof($fp)) {
            $percent = round(ftell($fp)/$length*100);
            if($last_percent < $percent and $percent%10==0){
                $last_percent = $percent;
                echo "...{$percent}%";
            }
            if(!isset($sp)){
                $data .= fread($fp,128);
            }else{
                fwrite($sp,fread($fp,128));
            }
        }
        echo "...OK \n";
    }else{
        echo "Failed to download {$open_url}!!!\n";
    }
    fclose($fp);
    if(!$save){
        return $data;
    }
    fclose($sp);
}
?>