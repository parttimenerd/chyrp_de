<?php
    class MyHashtag extends Modules
	{
		public function __init()
		{
			$this->addAlias("markup_text", "checktag", 9);
			$this->addAlias("markup_post_text", "checktag", 9);
			$this->addAlias("preview", "checktag", 9);
			$this->addAlias("preview_post", "checktag", 9);
		}
		
		public function checktag($text)
		{
			$config = Config::current();
			
			return preg_replace("/#([A-Za-z0-9_]+)/", "<a href='" . Config::current()->chyrp_url . "/search/$1'>#$1</a>", $text);
		}
	}
?>