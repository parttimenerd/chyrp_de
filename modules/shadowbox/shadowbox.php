<?php

    class Shadowbox extends Modules {
        static function __install() {
            Like::install();
        }

        static function __uninstall($confirm) {
            if ($confirm)
                Like::uninstall();
        }
        static function admin_sb_settings($admin) {
            if (!Visitor::current()->group->can("change_settings"))
                show_403(__("Access Denied"), __("You do not have sufficient privileges to change settings."));

            if (empty($_POST))
                return $admin->display("sb_settings");

            if (!isset($_POST['hash']) or $_POST['hash'] != Config::current()->secure_hashkey)
                show_403(__("Access Denied"), __("Invalid security key."));

            $config = Config::current();
            $initSetup = str_replace(array("\n", "\r", "\t", " "), '', $_POST['initSetup']);
            if (empty($initSetup))
                $initSetup = 'handleOversize: "drag",modal: true';
            $set = array($config->set("module_sb",
                                array("initSetup" => $initSetup)
                               )
                        );

            if (!in_array(false, $set))
                Flash::notice(__("Settings updated."), "/admin/?action=sb_settings");
        }
        static function settings_nav($navs) {
            if (Visitor::current()->group->can("change_settings"))
                $navs["sb_settings"] = array("title" => __("Shadowbox", "shadowbox"));

            return $navs;
        }

        public function head() {
            $config = Config::current();
?>
        <link rel="stylesheet" href="<?php echo $config->chyrp_url; ?>/modules/shadowbox/src/shadowbox.css" type="text/css" media="screen" />
        <script type="text/javascript" src="<?php echo $config->chyrp_url; ?>/modules/shadowbox/src/shadowbox.js"></script>
        <script type="text/javascript">
        <?php $this->initJS(); ?>
        </script>
<?php
        }

        public static function initJS(){
        	$config = Config::current();
        ?>//<script>
        	console.log("shadowbox loaded");
            $(document).ready(function(){
                //add attr to img linkwhich width>100
                $("#content img").each(function(e){
                    var a = $(this).parent();
                    if (a.attr("class")!="liked" && a.attr("class")!="like")
                       a.attr("rel","shadowbox");
                });
                $("#content a").each(function(e){
                    var img = $("img",this)[0];
                });
                //init shadowbox
                Shadowbox.init({
                    <?php 
                         echo $config->module_sb["initSetup"];
                     ?>
                });
            });
        <?php
        }
    }
    