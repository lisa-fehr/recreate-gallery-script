<?php
/*********************************
 * Gallery that is old and scary - how is it working on my server?!
 *********************************/
class Archi_Gallery
{

    // singleton instance
    private $clm;
    private $filter;
    private $parent_filter;
    private $Path;
    private $fullPath;
    private $thumbPath;
    private $query_s;
    private $db_link;
    private $id;
    private $parent_id;
    private $children;
    private $directory;
    private $per_page;
    private $current_page;
    private $start;
    private $language;
    private $baseLink;
    private $error;

    // private constructor function
    // to prevent external instantiation
    function __construct()
    {
        //defaults
        $this->clm = 3;
        $this->per_page = 24;
        $this->set_page(1);
        $this->Path = "";
        $this->fullPath = $_SERVER['DOCUMENT_ROOT'] . "/" . $this->Path;
        $this->thumbPath = "t";
        $this->query_s = $_SERVER['REQUEST_URI'];
        $this->dbConnect();
        $this->filter = 'all';
        $this->parent_filter = '';
        $this->id = 0;
        $this->set_language();
        $this->error = false;
        $this->children = false;
        $main_sql = "FROM uber_gallery AS gallery INNER JOIN uber_tag_assoc AS association INNER JOIN uber_tags as tags ON tags.id = ? where association.image_id = gallery.id and association.tag_id = tags.id";
        $this->page_sql = "select count(gallery.id) as total " . $main_sql;
        $this->sql = "select gallery.id,gallery.occurred,gallery.img,gallery.thumb,gallery.type,gallery.text,tags.directory " . $main_sql . " order by gallery.occurred desc limit ?, ?";
    }

    private function dbConnect()
    {
        $this->db_link = new mysqli("localhost", "lisa", "****", "sushi");
    }

    public function dbConnect_close()
    {
        $this->db_link->close();
    }

    private function dbPrepare($sql)
    {
        $stmt = $this->db_link->prepare($sql);
        if ($stmt === false) {
            die(mysqli_error($this->db_link));
        }
        return $stmt;
    }

    // must set this first, or it will select all
    public function name($set_filter)
    {
        $sql = "select children from uber_tags where name=?";
        $stmt = $this->dbPrepare($sql);
        $stmt->bind_param('s', $set_filter);
        $stmt->execute();
        $stmt->bind_result($children);
        if (count($stmt) == 0) {
            $this->error = "Gallery '$set_filter' does not exist";
        }
        $this->filter = $set_filter;
        $stmt->fetch();
        $this->children = $children;
        $stmt->close();
        $this->set_id();

        if ($this->children > 0) {
            $this->getAllChildren($this->id);
        }
        if ($this->filter == 'all') {
            $main_sql = "FROM uber_gallery AS gallery INNER JOIN uber_tag_assoc AS association INNER JOIN uber_tags as tags ON association.image_id = gallery.id where tags.id = association.tag_id order by gallery.occurred desc";
            $this->page_sql = "select count(gallery.id) as total " . $main_sql;
            $this->sql = "select gallery.id,gallery.occurred,gallery.img,gallery.thumb,gallery.type,gallery.text,tags.directory " . $main_sql . " limit ?, ?";
        } elseif ($this->children) {
            $main_sql = "FROM uber_gallery AS gallery INNER JOIN uber_tag_assoc AS association INNER JOIN uber_tags as tags ON association.image_id = gallery.id where association.tag_id = tags.id AND tags.id IN (" . implode(',',
                    $this->all_children) . ") order by gallery.occurred desc";
            $this->page_sql = "select count(distinct(gallery.id)) as total " . $main_sql;
            $this->sql = "select distinct(gallery.id),gallery.occurred,gallery.img,gallery.thumb,gallery.type,gallery.text,tags.directory " . $main_sql . " limit ?, ?";
        }

    }

    public function parent_name()
    {
        $sql = "select name from uber_tags where id='" . $this->parent_id . "'";
        $stmt = $this->dbPrepare($sql);
        $stmt->execute();
        $stmt->bind_result($name);
        if (count($stmt)) {
            $data = $stmt->fetch();
            $this->parent_filter = $name;
        }
        $stmt->close();
    }

    public function set_language($set_language = '')
    {
        $link = '';
        if ($set_language) {
            $link .= $set_language;
        }
        $this->baseLink = $link;
    }

    public function get_language()
    {
        return $this->language;
    }

    public function get_name()
    {
        return $this->filter;
    }

    public function get_page()
    {
        $query_s = $_SERVER['REQUEST_URI'];
        $page = 1;
        if (preg_match("/[?][0-9]*$/", $query_s)) {
            $page = preg_replace('/^.*[?]([0-9]*)$/', '\\1', $query_s);
            $this->set_page($page);
        }
        return $page;
    }

    public function testTotalPages()
    {
        $stmt = $this->dbPrepare($this->page_sql);
        if ($this->filter != 'all' && ! $this->children) {
            $stmt->bind_param('i', $this->id);
        }
        $stmt->execute();

        $stmt->bind_result($total);
        $data = $stmt->fetch();
        $result = $total;
        $stmt->close();
        return $result;
    }

    public function testSqlTotalPages()
    {
        $getRet = mysqli_query($this->db_link,
            "SELECT * FROM uber_tag_assoc where tag_id='" . $this->id . "'");
        return mysqli_num_rows($getRet);
    }

    public function get_main_filter()
    {
        //********************************************************************************************
        // Grab info from the url
        //********************************************************************************************
        $query_s = $_SERVER['REQUEST_URI'];
        //********************************************************************************************
        // ACCEPTS id=name or !name
        // ACCEPTS filter=name or ?name or ?name:page#
        //********************************************************************************************

        if (preg_match("/[!]/", $query_s) || isset($_GET['id'])) {
            if (! $_GET['id']) {
                $id = preg_replace("/^.*[?][!](.*)$/", "\\1", $query_s);
                $id = preg_replace("/&.*$/", "", $id);
            } else {
                $id = $_GET['id'];
            }
        } else {
            if (isset($_GET['filter'])) {
                $filter = $_GET['filter'];
            } else {
                if (preg_match("/[?]/", $query_s)) {
                    $filter = preg_replace("/[?].*$/", "", $query_s);
                    $_GET['p'] = preg_replace("/^.*[?]/", "", $query_s);
                    $_GET['p'] = preg_replace("/^.*:/", "", $_GET[p]);
                } else {
                    $_GET['p'] = 1;
                    $filter = $query_s;
                }
            }
        }

        $filter = preg_replace("/\/wrdp\//", "", $filter);
        $filter = preg_replace("/\//", "", $filter);
        if (empty($_GET['p']) || $_GET['p'] <= 0) {
            $_GET['p'] = 1;
        }
        if ($this->language) {
            $filter = preg_replace("/^" . $this->language . "/", "", $filter);
        }
        if ($filter == 'portfolio') {
            $filter = 'all';
        }
        $filter = preg_replace("/" . basename($_SERVER['PHP_SELF']) . "/", '', $filter);
        return empty($filter) ? 'all' : $filter;
    }

    // change page
    public function set_page($page)
    {
        $this->current_page = $page;
        if ($page > 1) {
            $this->start = ($page - 1) * $this->per_page;
        } else {
            $this->start = $page - 1;
        }

    }

    // get next forward buttons
    public function pagelist()
    {
        $pageurl = preg_replace("/\/?([?].*)?$/", "", $_SERVER['REQUEST_URI']);
        // get page count
        $stmt = $this->dbPrepare($this->page_sql);
        if ($this->filter != 'all' && ! $this->children) {
            $stmt->bind_param('i', $this->id);
        }
        $stmt->execute();
        $stmt->bind_result($total);
        $back = $this->current_page - 1;
        $forward = $this->current_page + 1;

        $data = $stmt->fetch();
        $pages = 0;
        if ($total > 0) {
            $pages = ceil($total / $this->per_page);
        }

        if ($pages >= 1) {
            $pagelist = '<table class="pagelist"><tr>';
            $pagelist .= '<td class="prev">';
            if ($this->current_page > 1) {
                $pagelist .= '<a href="' . $pageurl . '/?' . $back . '"><img width="70" height="20" src="prev.gif" alt="prev" /></a>';
            } else {
                $pagelist .= '<img width="70" height="20" src="prev.gif" class="disabled" alt="prev" />';
            }
            $pagelist .= '</td>';
            $pagelist .= '<td class="center"><form>page:<select name="p" onchange="turnPage(\'' . $pageurl . '\',this.form.p)">';

            for ($count = 1; $count <= $pages; $count++) {
                $pagelist .= '<option value="' . $count . '"';
                if ($this->current_page == $count) {
                    $pagelist .= ' selected="selected"';
                }
                $pagelist .= ">$count</option>";
            }
            $pagelist .= '</select></form></td>';
            $pagelist .= '<td class="next">';
            if ($forward <= $pages) {
                $pagelist .= '<a href="' . $pageurl . '/?' . $forward . '"> <img width="70" height="20" src="next.gif" alt="next" /></a>';
            } else {
                $pagelist .= '<img width="70" height="20" src="next.gif" class="disabled" alt="next" />';
            }
            $pagelist .= '</td>';
            $pagelist .= '</tr></table>';
        }
        $stmt->close();
        return $pagelist;
    }

    public function set_id()
    {
        $sql = "SELECT id,parent,directory FROM uber_tags WHERE name=?";
        $stmt = $this->dbPrepare($sql);
        $stmt->bind_param('s', $this->filter);
        $stmt->execute();
        $stmt->bind_result($id, $parent, $directory);
        $result = $stmt->fetch();
        if (count($stmt) > 0) {
            $this->id = $id;
            $this->parent_id = $parent;
            $this->directory = $directory;
        } else {
            $this->id = 0;
            $this->parent_id = 0;
        }
        $stmt->close();
        if ($this->parent_id > 0) {
            $this->parent_name();
        }
    }

    public function get_id()
    {
        return $this->id;
    }

    // get the count for each category folder
    public function get_count($filter)
    {
        $sql = "SELECT count FROM uber_tags WHERE name=?";
        $stmt = $this->dbPrepare($sql);
        $stmt->bind_param('s', $filter);
        $stmt->execute();
        $stmt->bind_result($count);
        $result = $stmt->fetch();
        $stmt->close();
        return isset($count) ? $count : 0;
    }

    public function error()
    {
        return $this->error;
    }

    // get filter list
    public function get_filters()
    {
        $break = $this->clm;
        $filter = $this->filter;
        $back = $this->parent_filter;
        $baseLink = $this->baseLink;
        $count_all = $this->get_count("$filter"); //get amount
        if ($filter != "all") {
            $sql = "SELECT name,display_name,count FROM uber_tags WHERE parent = ? order by name ASC";
            $stmt = $this->dbPrepare($sql);
            $stmt->bind_param('i', $this->id);
        } else {
            $sql = "SELECT name,display_name,count FROM uber_tags WHERE parent='0' AND name!='all' order by name ASC";
            $stmt = $this->dbPrepare($sql);
        }
        $stmt->execute();
        $stmt->bind_result($name, $dispay_name, $count);
        $stmt->store_result();
        $list = "";

        if ($back && $this->filter != $back) {
            $list .= "			<img src='prev-arrow.gif' width='20' height='10' alt='back' /> <a href=\"";
            $list .= $this->baseLink . "/$back\">back to $back</a><br class='clear' />\n";
            $show = 1;
        } elseif (! $back && $filter != $back && $filter != 'all') {
            $list .= "			<img src='prev-arrow.gif' width='20' height='10' alt='back' /> <a href=\"";
            $list .= $this->baseLink . "/portfolio\">back to portfolio</a><br class='clear' />\n";
            $show = 1;

        }

        $list .= '<ul class="portfolio_nav">';
        for ($i = 0; $stmt->fetch(); $i++) {
            if ($i == 0) {
                $content = "			<li><img src='filter1.gif' width='16' height='14' alt='filter' /> all($count_all)</li>\n";
            } else {
                $content = "";
            }
            if ($stmt->num_rows > 1) {
                if (preg_match('/[0-9]{4}$/', $name)) {
                    $filtersLink = preg_replace('/^(.*)([0-9]{4})$/', '\\1/\\2', $name);
                } else {
                    $filtersLink = $name;
                }
                $content = "$content			<li><img src='filter.gif' width='16' height='14' alt='filter' /> <a href='$baseLink/$filtersLink'>";
                if ($display_name) {
                    $content .= $display_name;
                } else {
                    $content .= $name;
                }
                $content .= "</a>($count)</li>\n";
                $list .= "$content";
            }

        }
        $list .= '</ul><br class="clear" />' . "\n";

        if ($i == 0 && ! $show) {
            $list = "";
        }
        //mysqli_free_result($stmt);
        $stmt->close();
        return $list;


    }

    public function image($img)
    {
        $sql = "select gallery.id, gallery.occurred, gallery.img, gallery.thumb, gallery.type, gallery.text, tags.directory FROM uber_gallery AS gallery INNER JOIN uber_tag_assoc AS association INNER JOIN uber_tags as tags ON association.image_id = gallery.id where gallery.img = ? AND tags.id = association.tag_id";
        $stmt = $this->dbPrepare($sql);
        $stmt->bind_param('s', $img);
        $stmt->execute();
        $stmt->bind_result($id, $occurred, $img, $thumb, $type, $text, $directory);
        $result = $stmt->fetch();
        // Check if id is actually a folder for a webdesign and forward
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . "/v/$img") && $img) {
            $design_url = "http://mysite.net/v/$img/";
        } elseif (is_dir("$_SERVER[DOCUMENT_ROOT]/$img") && $img) {
            $design_url = "http://mysite.net/$img/";
        }
        //********************************************************************************************
        // Display image or shockwave file
        //********************************************************************************************
        $im = $directory . "/" . $img . "." . $type;
        $time = date(YmdHis);
        $title = ucfirst($img);
        $container = "/" . $im . "?$time";
        $size = GetImageSize($this->fullPath . "/" . $im);
        $gallerylist = "";

        if ($type == "swf") {
            $gallerylist .= <<<EOT
<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" id="image" width="$size[0]" height="$size[1]" align="middle">
<param name="allowScriptAccess" value="sameDomain" />
<param name="movie" value="$container" />
<param name="loop" value="false" />
<param name="menu" value="false" />
<param name="quality" value="high" />
<param name="scale" value="noscale" />
<param name="salign" value="lt" />
<param name="bgcolor" value="#000000" />
<embed src="$container" loop="false" menu="false" quality="high" scale="noscale" salign="lt" bgcolor="#000000" width="$size[0]" height="$size[1]" swLiveConnect=true id="image" name="image" align="middle" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
</object>
EOT;
        } else {
            if ($design_url) {
                $url = '<a href="' . $design_url . '" title="launch design in new window" target="_new"><img src="/trans.gif" width="' . $size[0] . '" height="' . $size[1] . '" /></a>';
            } else {
                $url = '<img src="/trans.gif" width="' . $size[0] . '" height="' . $size[1] . '" />';
            }
            $gallerylist .= <<<EOT
	<div style="position:absolute;z-index:555;top:0;left:0;width:$size[0]px; height:$size[1]px">$url</div>
	<div style="position:absolute;z-index:35;top:0px;left:0;width:$size[0]px; height:$size[1]px"><img src="$container" $size[3] /></div>
	<div style="position:relative;z-index:36;top:0;left:0;width:$size[0]px; height:$size[1]px">&nbsp;</div>
EOT;
        }
        $stmt->close();

        return $gallerylist;

    }

    public function image_search($img)
    {
        $sql = "select gallery.id, gallery.occurred, gallery.img, gallery.thumb, gallery.type, gallery.text FROM uber_gallery AS gallery INNER JOIN uber_tag_assoc AS association ON association.image_id = gallery.id where gallery.img LIKE '%?%'";
        $stmt = db_prepare($sql);
        $stmt->bind_param('s', $img);
        $stmt->execute();
        $stmt->bind_result($id, $occurred, $img, $thumb, $type, $text);
        $result = $stmt->fetch();
        $stmt->close();
        return $result;
    }

    /*
     * Get a list of tag/filter ids related to this parent (up to 6 levels deep)
     */
    public function getAllchildren($id)
    {
        $children = $this->getFirstLevelChildren($id);
        $all_children = [];
        $first_children = [];
        foreach ($children as $child) {
            $first_children = array_merge($first_children, $this->getFirstLevelChildren($child));
            $all_children[] = $child;
        }
        if (! empty($first_children)) {
            $second_children = [];
            foreach ($first_children as $child) {
                $second_children = array_merge($second_children, $this->getFirstLevelChildren($child));
                $all_children[] = $child;
            }
        }
        if (! empty($second_children)) {
            $third_children = [];
            foreach ($second_children as $child) {
                $third_children = array_merge($third_children, $this->getFirstLevelChildren($child));
                $all_children[] = $child;
            }
        }
        if (! empty($third_children)) {
            $fourth_children = [];
            foreach ($third_children as $child) {
                $fourth_children = array_merge($fourth_children, $this->getFirstLevelChildren($child));
                $all_children[] = $child;
            }
        }
        if (! empty($fourth_children)) {
            $fifth_children = [];
            foreach ($fourth_children as $child) {
                $fifth_children = array_merge($fifth_children, $this->getFirstLevelChildren($child));
                $all_children[] = $child;
            }
        }
        if (! empty($fifth_children)) {
            $sixth_children = [];
            foreach ($fifth_children as $child) {
                $sixth_children = array_merge($sixth_children, $this->getFirstLevelChildren($child));
                $all_children[] = $child;
            }
        }
        if (! empty($sixth_children)) {
            $seventh_children = [];
            foreach ($sixth_children as $child) {
                $seventh_children = array_merge($seventh_children, $this->getFirstLevelChildren($child));
                $all_children[] = $child;
            }
        }
        $this->all_children = $all_children;
        return $all_children;
    }

    private function getFirstLevelChildren($id)
    {
        $children = [];
        $sql = "select id FROM uber_tags where parent = ?";
        $stmt = $this->dbPrepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->bind_result($id);
        for ($i = 0; $stmt->fetch(); $i++) {
            $children[] = $id;
        }
        $stmt->close();
        return $children;
    }

    public function gallery()
    {
        $stmt = $this->dbPrepare($this->sql);
        if ($this->filter == 'all' || $this->children) {
            $stmt->bind_param('ii', $this->start, $this->per_page);
        } else {
            $stmt->bind_param('iii', $this->id, $this->start, $this->per_page);
        }
        $stmt->execute();
        $result = $stmt->bind_result($id, $occurred, $img, $thumb, $type, $text, $directory);
        $gallerylist = "";
        for ($i = 0; $stmt->fetch(); $i++) {
            if (! isset($directory)) {
                $directory = $this->directory;
            }
            $space = '';
            $size = @GetImageSize($this->fullPath . "/" . $directory . "/$img.$type");
            $title = preg_replace("/_/", " ", $img);

            if ($size) {
                $gallerylist .= '<a href="#gallery" data-type="' . $type . '" id="' . $img . '" class="fadeNext" onmouseover="window.status=\'' . $title . ' ' . $size[0] . 'x' . $size[1] . '\';return true;" onmouseout="window.status=\'\'; return true">';

                $gallerylist .= '<img width="125" height="175" src="/archi_thumb.php?id=' . $id . '" alt="' . $title . '" title="' . $title . '" class="thumbnail" />';
                $gallerylist .= "</a>";
            } else { // thumbnail is missing but file data is present
                $gallerylist .= '<img width="125" height="175" src="/archi_thumb.php?id=' . $id . '" alt="' . $title . '" title="' . $title . '" class="thumbnail" />';
            }
        }
        if ($i == 0) {
            $gallerylist = "<br class='clear' />No results.";
        }
        $stmt->close();
        return $gallerylist;
    }
}


/* Usage */
$gallery = new Archi_Gallery();
$filter = $gallery->get_main_filter();
$gallery->name($filter);
//echo $gallery->get_name();
//echo $gallery->get_id();
echo $filter;
if ($gallery->error()) {
    echo $gallery->error();
} else {
    echo $gallery->get_filters();
    echo $gallery->gallery();
    $gallery->pagelist();
}
?>
