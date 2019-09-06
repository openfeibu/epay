<!DOCTYPE HTML>
<html lang="" >
    <head>
        <meta charset="UTF-8">
        <meta content="text/html; charset=utf-8" http-equiv="Content-Type">
        <title>{$title}</title>
		<meta name="description" content="">
		<meta name="keywords" content="">
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="mobile-web-app-capable" content="yes">
		<meta name="apple-mobile-web-app-capable" content="yes">

    </head>
    <body>
	<script>
        var host = '{$website_urls}assets/doc/dtree/';
        var cssDtree = '<link rel="stylesheet" href="' + host + 'dtree.css">';
        var cssMy = '<link rel="stylesheet" href="' + host + 'my.css">';
        var cssHl = '<link rel="stylesheet" href="' + host + 'highlight/agate.css">';
        var scriptDtree = '<scri'+'pt src="' + host + 'dtree.php"></scri'+'pt>';
        var scriptData = '<scri'+'pt src="' + host + 'data.js"></scri'+'pt>';
        var scriptHl = '<scri'+'pt src="' + host + 'highlight/highlight.pack.js"></scri'+'pt>';
        document.write(cssDtree);
        document.write(cssMy);
        document.write(cssHl);
        document.write(scriptDtree);
        document.write(scriptData);
        document.write(scriptHl);
	</script>

	<div style="" id="sidebar" >
	<script>
		d = new dTree('d');
		d.config.useIcons = false;
		d.config.closeSameLevel = true;
		d.config.inOrder = true;
		d.config.useCookies = false;
		d.config.useSelection = false;

		for (var i=0; i<data.length; i++) {
			var info = data[i];
			if (info.hasOwnProperty('url')) {
				d.add(info.currentId, info.preId, info.name, info.url);
			} else {
				d.add(info.currentId, info.preId, info.name);
			}
		}

		document.write(d);

		//别人第一次打开连接, 没有cookie时自动展开
        var currentNode = getQueryString();
        var currentId = parseInt(currentNode['id']);
        currentId = currentId ? currentId : 1;
        d.closeAll();
		d.openTo(currentId);
		document.getElementById("sd" + currentId).className = 'nodeSel'; //高亮当前打开的文档

		//获取url中的参数(网上copy)
		function getQueryString() {
		  var qs = location.search.substr(1), // 获取url中"?"符后的字串
			args = {}, // 保存参数数据的对象
			items = qs.length ? qs.split("&") : [], // 取得每一个参数项,
			item = null,
			len = items.length;

		  for(var i = 0; i < len; i++) {
			item = items[i].split("=");
			var name = decodeURIComponent(item[0]),
			  value = decodeURIComponent(item[1]);
			if(name) {
			  args[name] = value;
			}
		  }
		  return args;
		}

	 </script>
	</div>
	<div style="" id="content">
	<h1 style="text-align:center">{$title}</h1>
