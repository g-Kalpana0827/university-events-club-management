<?php
if (PHP_SAPI !== 'cli') exit('CLI only');
require dirname(__DIR__).'/vendor_autoload.php';
$_SESSION=[];
$_SERVER['SCRIPT_NAME']='/UoV_Campus_Connect/public/index.php';
$tests=[
    'html escaping'=> e('<script>')==='&lt;script&gt;',
    'quote escaping'=>e('" onerror="x')==='&quot; onerror=&quot;x',
    'slug normalization'=>slugify('ITCS Open Source Club!')==='itcs-open-source-club',
    'route build'=>url('event',['id'=>17])==='/UoV_Campus_Connect/public/index.php?r=event&id=17',
    'stored notice routing'=>message_url('event&id=17')===url('event',['id'=>17]),
    'valid int'=>valid_int('12')===12 && valid_int('-1')===0,
    'CSRF length'=>strlen(csrf_token())===64,
    'faculties'=>count(faculty_options())===4,
];
$bad=0;
foreach($tests as $name=>$pass){echo ($pass?'PASS':'FAIL').' '.$name.PHP_EOL;if(!$pass)$bad++;}
echo '\n'.(count($tests)-$bad).'/'.count($tests).' passed'.PHP_EOL;
exit($bad?1:0);
