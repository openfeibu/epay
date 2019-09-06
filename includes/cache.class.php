<?php

	class Cache
	{
		private $cache_path;//path for the cache
		private $cache_expire;//seconds that the cache expires

		public function __construct($exp_time = 3600, $path = "../cache/")
		{
			$this->Cache($exp_time,$path);
		}

		public function Cache($exp_time = 3600, $path = "../cache/") //旧构造器兼容
		{
			$this->cache_expire = $exp_time;
			$this->cache_path   = $path;
		}

		private function fileName($key)
		{
			return $this->cache_path.md5($key).'.php';//缓存文件后辍全改成php
		}

		public function put($key, $data)
		{
			$values   = serialize($data);
			$filename = $this->fileName($key);
			$file     = fopen($filename, 'w');
			//加入安全防犯
			$values   = '<?php exit;?>'.$values;
			if ($file) {//able to create the file
				fwrite($file, $values);
				fclose($file);
			} else return false;
		}

		public function get($key)
		{
			$filename = $this->fileName($key);
			if (!file_exists($filename) || !is_readable($filename)) {//can't read the cache
				return false;
			}
			if (time() < (filemtime($filename) + $this->cache_expire)) {//cache for the key not expired
				$file = fopen($filename, "r");// read data file
				if ($file) {//able to open the file
					$data = fread($file, filesize($filename));
					fclose($file);
					$data=str_replace("<?php exit;?>","",$data); //过滤掉无关字符
					return unserialize($data);//return the values
				} else return false;
			} else return false;
		}
	}

