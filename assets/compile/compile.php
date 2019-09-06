<?php
//清空src文件
//if(isset($_REQUEST['clean']) && $_REQUEST['clean'] == 'yes'){
//    require_once __DIR__.DIRECTORY_SEPARATOR."../../includes/api/autoload.php";
//    \epay\file::delete(__DIR__.DIRECTORY_SEPARATOR."src/","/.*.md/");
//    return;
//}
date_default_timezone_set("Asia/Shanghai");

$root = __DIR__;
include($root.DIRECTORY_SEPARATOR.'Dir.php');
include($root.DIRECTORY_SEPARATOR.'Parser.php');
include($root.DIRECTORY_SEPARATOR.'Parsedown.php');
require_once __DIR__.DIRECTORY_SEPARATOR."config.php";

$srcDir = $root.DIRECTORY_SEPARATOR.'src'; //待编译目录
$disDir = __DIR__.DIRECTORY_SEPARATOR.'../doc'; //编译后文件存放目录
$htmlDir = $root.DIRECTORY_SEPARATOR.'html'; //html公共头文件目录

//==============获取上次编译时间
$compileLog = array();
$lastCompileTimeFile = $root.DIRECTORY_SEPARATOR.'last_compile_time.log'; //存放了上次编译时间
if (file_exists($lastCompileTimeFile)) {
    $lastCompileTime = json_decode(file_get_contents($lastCompileTimeFile), TRUE);
} else {
    $lastCompileTime = array();
}

//==============获取html的header, footer, 拼装到编译后的html前后组装成完整的html页面
$headerHtml = file_get_contents($htmlDir.DIRECTORY_SEPARATOR.'header.html');
$footerHtml = file_get_contents($htmlDir.DIRECTORY_SEPARATOR.'footer.html');

$headerHtml = file_get_contents($htmlDir.DIRECTORY_SEPARATOR.'header.php');
$footerHtml = file_get_contents($htmlDir.DIRECTORY_SEPARATOR.'footer.php');
$headerHtml = str_replace('{$title}',"接口开发文档",$headerHtml);
$headerHtml = str_replace('{$website_urls}',$website_urls,$headerHtml);

//$parser = new Parser(); //sf官方的解析类, 对于单行多个 <br> 的情况, 会间隔识别
$parser = new Parsedown(); // http://parsedown.org/
$srcFileList = Dir::ini($srcDir)->extension('md');
$fileList = $srcFileList->fileList;

//==============编译md文件
$currentTime = time();
$compiledFile = array(); //本次编译的文件
foreach ($fileList as $k => $srcPathName) {
	
    $fileInfo = new SplFileInfo($srcPathName);
    //获取文件最新更新时间
	$srcFileModifyTime = $fileInfo->getMTime(); //修改时间
    $srcFileCreateTime = $fileInfo->getCTime(); //创建时间, 复制生成的文件其创建时间 > 修改时间
    $srcFileAccessTime = $fileInfo->getATime(); //文件上次访问时间
    $lastModifyTime = $srcFileCreateTime > $srcFileModifyTime ? $srcFileCreateTime : $srcFileModifyTime;
    
    // 如果上次没有编译这个文件, 或者最新修改时间大于上次编译时间就重新编译, 并记录本次编译的时间
	if ((empty($lastCompileTime[$srcPathName]) || $lastCompileTime[$srcPathName] < $lastModifyTime)) {
		$text = file_get_contents($srcPathName);
		//替换markdown中的变量
        $text = str_replace('{$website_urls}',$website_urls,$text);
//		$markdown = $parser->makeHtml($text);
		$markdown = $parser->text($text);
        
        $footerHtml = str_replace('{modify_time}', date('Y-m-d H:i:s', $currentTime), $footerHtml); //记录修改时间
		$content = $headerHtml. $markdown. $footerHtml;
		
		//$disPathName = str_replace(array($srcDir, '.md'), array($disDir, '.html'), $srcPathName);
		$disPathName = str_replace(array($srcDir, '.md'), array($disDir, ".{$extension}"), $srcPathName);
		$dirName = dirname($disPathName);
		if (!file_exists($dirName)) {
			mkdir($dirName, 0777, true);
		}
		
		file_put_contents($disPathName, $content);
		$compiledFile[] = $srcPathName;
        
        $compileLog[$srcPathName] = $currentTime;
	} else {
	    $compileLog[$srcPathName] = $lastCompileTime[$srcPathName];
    }
}

//===============记录本次编译时间
file_put_contents($lastCompileTimeFile, json_encode($compileLog, JSON_UNESCAPED_UNICODE));

//===============生成dtree.js所用的数据
$js = array();
$js[] = array('currentId' => 0, 'preId' => -1, 'name' => "<a href='{$website_urls}assets/doc.php' style='color: blue'>开发文档</a>"); //dtree.js 要求根元素的父id必须是-1, 从调试来看, 根元素的id必须是0才能正确高亮显示

$GLOBALS['node_index'] = array('zhangzhibin' => 0); //已生成目录的编号
$GLOBALS['is_new'] = 0;

//返回或生成目录的编号
function nodeId($node)
{
    $maxId = max($GLOBALS['node_index']); //当前最大值
    
    if (empty($GLOBALS['node_index'][$node])) {
        $GLOBALS['is_new'] = 1;
        $GLOBALS['node_index'][$node] = $maxId + 1;
    } else {
        $GLOBALS['is_new'] = 0;
    }
    
    return $GLOBALS['node_index'][$node];
}

foreach ($fileList as $k => $srcPathName) {
    $array1 = array($srcDir, '\\', '.md');
    //$array2 = array("", "/", '.html');
    $array2 = array("", "/", ".{$extension}");
    //var_dump($array1);
    //var_dump($array2);
	$url = str_replace($array1, $array2, $srcPathName);
	//var_dump($url);
	$arrNode = explode('/', ltrim($url, '/'));
	//var_dump($arrNode);
	
	if (count($arrNode) == 1) { //只有一层, 肯定是跟目录下的文件
        $nodeName = reset($arrNode);
		$js[] = array('currentId' => nodeId($nodeName), 'preId' => 0, 'name' => pathinfo($nodeName, PATHINFO_FILENAME), 'url' => $urls.$url.'?id='.nodeId($nodeName));
	} else {
		//一级目录, 父节点固定为1
		$nodeName = array_shift($arrNode);
		$nodeId = nodeId($nodeName);
        $GLOBALS['is_new'] && $js[] = array('currentId' => $nodeId, 'preId' => 0, 'name' => $nodeName);
        $preId = $nodeId;
		
		//最后一个元素是html文件, 取出来
		$endNode = array_pop($arrNode);
		
		//中间目录, 父节点是上级目录的id
		if (!empty($arrNode)) {
			foreach ($arrNode as $node) {
                $nodeName .= $node;
                $nodeId = nodeId($nodeName);
                $GLOBALS['is_new'] && $js[] = array('currentId' => $nodeId, 'preId' => $preId, 'name' => $node);
				$preId = $nodeId;
			}
		}
		
		//最后一个节点(文件名)
        $fileName = pathinfo($endNode, PATHINFO_FILENAME);
		$nodeId = nodeId($nodeName.$fileName);
		$js[] = array('currentId' => $nodeId, 'preId' => $preId, 'name' => $fileName, 'url' => $urls.$url.'?id='.$nodeId);
	}
}

$jsonDir = $disDir.DIRECTORY_SEPARATOR.'dtree/data.js';
$data = 'var data = '.json_encode($js, JSON_UNESCAPED_UNICODE).';';
file_put_contents($jsonDir, $data);

//=================删掉目标文件夹多余的文件
$disFileList = Dir::ini($disDir)->extension('html')->replace(array($disDir.DIRECTORY_SEPARATOR, '.html'), array('', ''));
$srcFileList = $srcFileList->replace(array($srcDir.DIRECTORY_SEPARATOR, '.md'), array('', ''));

$diff = array_diff($disFileList->fileList, $srcFileList->fileList); //找到_book中多余的文件

print_r($diff);

foreach ($diff as $v) {
    $diffFile = $disDir.DIRECTORY_SEPARATOR.$v.'.html';
    unlink($diffFile); //删除目录文件夹中多余的文件
}

//删除空文件夹
//todo

//==================打印本次编译的文件列表
echo "本次编译的文件: ".PHP_EOL;
print_r($compiledFile);

echo 'over~';