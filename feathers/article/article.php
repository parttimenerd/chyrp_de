<?php
    class Article extends Feathers implements Feather {
        public function __init() {
            $this->setField(array("attr" => "headline",
                                  "type" => "text",
                                  "label" => __("Headline", "article")));
            $this->setField(array("attr" => "byline",
                                  "type" => "text",
                                  "label" => __("Byline", "article"),
                                  "optional" => true));
            $this->setField(array("attr" => "author",
                                  "type" => "text",
                                  "label" => __("Author", "article"),
                                  "optional" => true));
            $this->setField(array("attr" => "body",
                                  "type" => "text_block",
                                  "label" => __("Body", "article"),
                                  "preview" => true));
            $this->setField(array("attr" => "image",
                                  "type" => "file",
                                  "label" => __("Hero image", "article"),
                                  "optional" => true,
                                  "note" => "<small>(Max. file size: ".ini_get('upload_max_filesize').")</small>"));
            $this->setField(array("attr" => "attribution",
                                  "type" => "text",
                                  "label" => __("Image attribution", "article"),
                                  "optional" => true));

            $this->setFilter("headline", array("markup_title", "markup_post_title"));
            $this->setFilter("byline", array("markup_title", "markup_post_title"));
            $this->setFilter("body", array("markup_text", "markup_post_text"));

            $this->respondTo("delete_post", "delete_file");
            $this->respondTo("post_options", "add_option");

        }

        public function submit() {
            if (empty($_POST['headline']))
                error(__("Error"), __("Headline can't be blank.", "article"));

            if (empty($_POST['body']))
                error(__("Error"), __("Body can't be blank."));

            if (isset($_FILES['image']) and $_FILES['image']['error'] == 0)
                $hero = upload($_FILES['image'], array("jpg", "jpeg", "png", "gif", "bmp"));

            fallback($_POST['slug'], sanitize($_POST['headline']));

            return Post::add(array("headline" => $_POST['headline'],
                                   "byline" => $_POST['byline'],
                                   "author" => $_POST['author'],
                                   "body" => $_POST['body'],
                                   "hero" => $hero,
                                   "attribution" => $_POST['attribution']),
                                   $_POST['slug'],
                                   Post::check_url($_POST['slug']));
        }

        public function update($post) {
            if (empty($_POST['headline']))
                error(__("Error"), __("Headline can't be blank.", "article"));

            if (empty($_POST['body']))
                error(__("Error"), __("Body can't be blank."));

            if (isset($_FILES['image']) and $_FILES['image']['error'] == 0) {
                $this->delete_file($post);
                $hero = upload($_FILES['image'], array("jpg", "jpeg", "png", "gif", "tiff", "bmp"));
            } else
                $hero = $post->hero;

            $post->update(array("headline" => $_POST['headline'],
                                "byline" => $_POST['byline'],
                                "author" => $_POST['author'],
                                "body" => $_POST['body'],
                                "hero" => $hero,
                                "attribution" => $_POST['attribution']));
        }

        public function title($post) {
            return $post->headline;
        }

        public function excerpt($post) {
            return $post->byline;
        }

        public function hero_tag($post, $max_width = 500, $max_height = null, $more_args = "quality=100") {
            $config = Config::current();
            $hero = $post->hero;
            $alt = !empty($post->alt_text) ? fix($post->alt_text, true) : $hero;
            $title = !empty($post->attribution) ? fix($post->attribution, true) : $hero;
            return '<img src="'.$config->chyrp_url.'/includes/thumb.php?file=..'.$config->uploads_path.urlencode($hero).'&amp;max_width='.$max_width.'&amp;max_height='.$max_height.'&amp;'.$more_args.'" alt="'.$alt.'" title="'.$title.'" />';
        }

        public function hero_url($post) {
            $config = Config::current();
            return $config->chyrp_url.$config->uploads_path.$post->hero;
        }

        public function attribution($post) {
            return $post->attribution;
        }

        public function feed_content($post) {
            $body = "<h1>";
            $body.= $post->headline;
            $body.= "</h1>\n";
            $body.= $post->byline;
            return $body;
        }

        public function delete_file($post) {
            if ($post->feather != "article") return;
            unlink(MAIN_DIR.Config::current()->uploads_path.$post->hero);
        }

        public function add_option($options, $post = null) {
            if (isset($post) and $post->feather != "article") return;
            elseif (Route::current()->action == "write_post")
                if (!isset($_GET['feather']) and Config::current()->enabled_feathers[0] != "article"
                or isset($_GET['feather']) and $_GET['feather'] != "article") return;

            $options[] = array("attr" => "option[alt_text]",
                               "label" => __("Alt-Text", "article"),
                               "type" => "text",
                               "value" => oneof(@$post->alt_text, ""));
            return $options;
      }
    }
