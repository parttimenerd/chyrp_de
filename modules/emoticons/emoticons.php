<?php
    require "lib/emoticons.php";

    class Emoticons extends Modules {
        public function __init() {
           $this->addAlias("markup_text", "emote");
           $this->addAlias("markup_post_text","emote");
           $this->addAlias("preview", "emote");
           $this->addAlias("preview_post", "emote");
        }
        public function emote($text) {
            return convertEmoticons($text);
        }
    }
?>