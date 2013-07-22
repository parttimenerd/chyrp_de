<?php
    class Piwik extends Modules {
        static function __install() {
            Group::add_permission("piwik_exclude_group", "Exclude from Piwik");
            $config = Config::current();
            $config->set("piwik_url", "127.0.0.1/piwik/");
            $config->set("piwik_siteid", 1);
            $config->set("piwik_script_position", "head");
        }

        static function __uninstall($confirm) {
	    if ($confirm)
                Group::remove_permission("piwik_exclude_group");

	    $config = Config::current();
            $config->remove("piwik_url");
            $config->remove("piwik_siteid");
            $config->remove("piwik_script_position");
        }

        static function admin_piwik_settings($admin) {
            if (!Visitor::current()->group->can("change_settings"))
                show_403(__("Access Denied"), __("You do not have sufficient privileges to change settings."));

            if (empty($_POST))
                return $admin->display("piwik_settings");

            $config = Config::current();
            if (!isset($_POST['hash']) or $_POST['hash'] != $config->secure_hashkey)
                show_403(__("Access Denied"), __("Invalid security key."));

            if (!empty($_POST['piwik_url']) and !empty($_POST['piwik_siteid'])) {
                $config->set("piwik_url", $_POST['piwik_url']);
                $config->set("piwik_siteid", $_POST['piwik_siteid']);
                $config->set("piwik_script_position", $_POST['piwik_script_position']);

                Flash::notice(__("Settings updated."), "/admin/?action=piwik_settings");
            } else
                Flash::warning(__("Please enter the url to your piwik setup (the base folder)."), "/admin/?action=piwik_settings");
        }

        static function settings_nav($navs) {
            if (Visitor::current()->group->can("change_settings"))
                $navs["piwik_settings"] = array("title" => __("Piwik", "piwik"));

            return $navs;
        }

	public function head() {
            $config = Config::current();
            $visitor = Visitor::current();
            if (!$config->piwik_url or !$config->piwik_siteid or $config->piwik_script_position != "head" or $visitor->group()->can("piwik_exclude_group"))
                return;

            self::piwik_tracker_code($config->piwik_url, $config->piwik_siteid);
        }

	public function end_content() {
            $config = Config::current();
            $visitor = Visitor::current();
            if (!$config->piwik_url or !$config->piwik_siteid or $config->piwik_script_position != "body" or $visitor->group()->can("piwik_exclude_group"))
                return;

            self::piwik_tracker_code($config->piwik_url, $config->piwik_siteid);
        }

        public function piwik_tracker_code($piwik_url, $piwik_siteid) {
?>
        <script type="text/javascript">
          var _paq = _paq || [];
          (function(){ var u=(('https:' == document.location.protocol ? 'https://' : 'http://') + '<?php echo $piwik_url?>');
          _paq.push(['setSiteId', <?php echo $piwik_siteid ?>]);
          _paq.push(['setTrackerUrl', u +'piwik.php']);
          _paq.push(['trackPageView']);
          _paq.push(['enableLinkTracking']);
          var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0]; g.type='text/javascript'; g.defer=true; g.async=true; g.src=u+'piwik.js';
          s.parentNode.insertBefore(g,s); })();
        </script>
<?php
        }
    }

?>
