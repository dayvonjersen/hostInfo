<?php
// I am a shitlord.
if(isset($_GET['source'])) 
 die(highlight_file(__FILE__,1));
//header('Content-Type: text/plain');

$cpuinfo = $matches = array();
preg_match_all('/([\w\s]+):\s+(.+)\n/', file_get_contents('/proc/cpuinfo'), $matches);
for($i=0;$i<count($matches[1]);$i++)
 $cpuinfo[trim($matches[1][$i])] = trim($matches[2][$i]);

//var_dump($cpuinfo);

$meminfo = $matches = array();

preg_match_all('/(\w+):\s+(\d+) kB\n/', file_get_contents('/proc/meminfo'), $matches);
for($i=0;$i<count($matches[1]);$i++)
 $meminfo[$matches[1][$i]] = $matches[2][$i];

//var_dump($meminfo);

//$keys = array('Filesystem', 'Size', 'Used', 'Available', 'Use%', 'Mounted On');
//$values = array('','','','','','');

function formatdf( $in ) {
 return strtolower(preg_replace('/[0-9\.]+?/','',$in)) . 'B';
}

$keys = $values = array();
foreach(explode("\n", `df -h`) as $k => $hd) {
 $hdd      = explode(' ', preg_replace('/\s+/', ' ', $hd));
 if(count($hdd)!=6)
  continue;
 
 $keys[]   = sprintf('%s (%s)',$hdd[0],$hdd[5]);
 $values[] = sprintf('%.2f%s/%.2f%s (%s)',$hdd[2],formatdf($hdd[2]),$hdd[1],formatdf($hdd[1]),$hdd[4]);
}

$hdd = array_combine($keys,$values);
//var_dump(array_combine($keys,$values));

$specs = array(
	'Processor' => array(
		'Type' => $cpuinfo['vendor_id'] . ' ' . $cpuinfo['model name'],
		'Clockspeed' => $cpuinfo['cpu MHz'] .'mHz',
		'Cache' => formatkB(current($e = explode(' ', $cpuinfo['cache size'])))
		),
	'Memory' => array(
		'Cached RAM' => formatkB($meminfo['Active'] + $meminfo['Cached']),
		'Free RAM' => formatkB($meminfo['MemFree']),
		'Total RAM' => formatkB($meminfo['MemTotal']),
		'Cached Swap' => formatkB($meminfo['SwapCached']),
		'Free Swap' => formatkB($meminfo['SwapFree']),
		'Total Swap' => formatkB($meminfo['SwapTotal'])
		),
	'Storage' => $hdd
);
$uptime = trim(`uptime`);
$hostname = trim(`hostname`);

function formatkB($kB)
{
 $unit = 'kB';
 if($kB >= 1024 && $kB < pow(1024,2))
 {
  $kB = $kB/1024;
  $unit = 'mB';
 }
 if($kB >= pow(1024,2) && $kB < pow(1024,3))
 {
  $kB = $kB/pow(1024,2);
  $unit = 'gB';
 }
 return sprintf('%.1f%s',$kB,$unit);
}

require 'functions.inc.php';
require 'xhtml5.inc.php';

$myStyle = <<<CSS
html { font: 10pt 'Inconsolata', 'Nimbus Sans Mono', 'Courier New', monospace; padding: 1em; background: #fff; color: #222; }
body { width: 100%; }

h1::after {
    content: '_';
    display: inline;
    color: inherit;
    animation: blink 1s steps(1) infinite;
    -webkit-animation: blink 1s steps(1) infinite;
}

@-webkit-keyframes blink { 
    50% { visibility: hidden; }
}

@keyframes blink { 
    50% { visibility: hidden; }
}

h1, h2, h3 { margin: 0; padding: 0 0 0.25em 0; }
h2, h3, div { font-size: 8pt;  }
div { position: fixed; right: 0; bottom: 0; font-style: italic; }

table { border-spacing: 1px; box-shadow: 2px 2px 5px #000; width: 100%; }

blockquote { width: 33%; clear: none; opacity: 0.3;position: fixed; text-shadow: 2px 2px 2px; }
pre { color: #d70751; }
pre span { color: #4F0080; }
div { clear: both; }
a { color: #000; text-decoration: none; }
a:hover { background: #000; color: #fff; }
#processor th { color: #9df;  }

#memory th {  color: #fa0;   }

#storage th { color: #32cd32;}                         

th,td { padding: 0.25em; }
td {width: 50%; }
tr:last-child td {  }
thead th { text-align: left; border: 1px solid; font-weight: bold; background: #000; }
tbody th { text-align: right; border-right: none; }
tbody td { border-left: none; }
tbody th:after { content: ':'; }
tbody tr:nth-child(odd) * { background: #efefef; }

CSS;
$template = array('html' => array('head' => array('title' => $hostname . ' Info')), array('body' => array()));

$head = &$template['html']['head'];
$head['style'] = array('__attributes' => array('type' => 'text/css'), $myStyle);

$body = &$template['html']['body'];
$body['h1'] = $hostname;
$body['h2'] = $uptime;
$body['h3'] = date('l jS F Y G:i:s e');

$logo = <<<LOGO
<pre>         _,met$$$$\$gg.          
      ,g$$$$$$$$$$$$$$\$P.       
    ,g$\$P""       """Y$$.".     
   ,$\$P'              `$$$.     
  ',$\$P       ,ggs.     `$\$b:   
  `d$$'     ,\$P"'   <span>.</span>    $$$    
   $\$P      d$'     <span>,</span>    $\$P    
   $$:      $$.   <span>-</span>    ,d$$'    
   $$\;      Y\$b._   _,d\$P'
   Y$$.    <span>`.</span>`"Y$$$\$P"'
   `$\$b      <span>"-.__</span>
    `Y$$
     `Y$$.
       `$\$b.
         `Y$\$b.
            `"Y\$b._
                `""""</pre>
LOGO;

$body['blockquote'] = array('__raw' => $logo);
$body['table'] = array();

foreach($specs as $category => $data) {
 $myTable = ['__attributes' => ['id' => strtolower($category)],
              'thead'       => 
                    ['tr' => 
                        ['th' => ['__attributes' => ['colspan' => 2], $category]]
                    ],
               'tbody' => ['tr' => []]];

 foreach($data as $k => $v)
  $myTable['tbody']['tr'][] = array('th' => $k, 'td' => array('__raw'=>preg_replace('/G$/', 'gB', $v)));

 $body['table'][] = $myTable;
}

$body['div'] = $_SERVER['SERVER_SOFTWARE'] . ' @ ' . $_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'];
$body['a'] = array('__attributes'=>array('href'=>'?source','title'=>'View Source!','style'=>'font-size: xx-small;'),'php source code');

$xhtml = new xhtml5();
$xhtml->loadArray($template);
echo $xhtml;
