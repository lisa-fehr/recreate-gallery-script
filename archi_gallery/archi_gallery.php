<?php
/*
Plugin Name: Gallery
Version: v1.05
Description: Old scary plugin that probably doesn't work
*/
add_action('admin_menu', 'archi_gallery_menu');
if (! function_exists('mysqli_connect')) {
    include($_SERVER['DOCUMENT_ROOT'] . '/mysqli.php');
}

/*************************************
 * Adds the Admin menu to the sidebar *
 *************************************/
function archi_gallery_menu()
{

    add_menu_page('My Plugin Options', 'Gallery Admin', 8, __FILE__, 'archi_gallery_options');


    if (get_option('archi_gallery_directory')) {
        add_submenu_page(__FILE__, 'Gallery Admin', 'add images', 8, 'archi_add', 'archi_gallery_options_add');
        add_submenu_page(__FILE__, 'Gallery Admin', 'edit images', 8, 'archi_edit', 'archi_gallery_options_edit');
        //add_management_page('Gallery Admin', 'Gallery Admin', 8, 'archi_add', 'archi_gallery_options');
        //add_options_page('Test Options', 'Test Options', 8, 'testoptions', 'my_plugin_options');
    }
}


/*****************************
 * Main section for the admin *
 *****************************/
function archi_gallery_options()
{

    //global $directory,$db_link;
    $melan_gallery = new melan_gallery(get_option('archi_gallery_directory'));
    $directory = $melan_gallery->get_directory();
    $db_link = $melan_gallery->get_link();
    // set the directory where images can be found
    if ($_POST['dirchange']) {
        update_option('archi_gallery_directory', $_POST['directory']);
    }

    // ask user to set the directory
    if (! $directory) {
        echo "<br /><br /><i>You currently do not have a directory set. You need to pick one to store your images in.</i>";
    }
    ?>
    <br/><br/>
    <b>Set Directory where images can be found:</b><br/>
    <form method="post">
        /<input type="text" name="directory" value="<?= get_option('archi_gallery_directory') ?>"/>/ <input
                name="dirchange" type="submit" value="set"/>
    </form>
    <br/><br/>
    <?
    // show this part only when the directory is set
    if ($directory) {

        if ($_POST['addnew']) {
            mysqli_query($db_link,
                "INSERT INTO uber_tags set name='" . $_POST['name'] . "', display_name='" . $_POST['display_name'] . "', directory='" . $_POST['directory'] . "', parent='" . $_POST['parent'] . "', details='" . $_POST['details'] . "'") or die(mysqli_error($db_link));
            echo "Added Tag";
        }
        if ($_POST['editcat']) {
            foreach ($_POST['parent'] as $key => $parent) {
                if ($_POST['delete'][$key]) {
                    @mysqli_query($db_link, "DELETE FROM uber_tags where id=" . $key);
                } else {
                    @mysqli_query($db_link,
                        "UPDATE uber_tags set display_name='" . $_POST['display_name'][$key] . "', directory='" . $_POST['directory'][$key] . "', parent='" . $_POST['parent'][$key] . "', details='" . $_POST['details'][$key] . "' WHERE id=" . $key);
                }
            }
            echo "Edited tag(s)";
        }
        ?>
        <form method="post">
            <b>Add tags</b>:<br/>
            <table border="0">
                <tr class="tabletop">
                    <td>&nbsp;tag&nbsp;</td>
                    <td>&nbsp;parent&nbsp;</td>
                    <td>&nbsp;directory&nbsp;</td>
                    <td>&nbsp;details&nbsp;</td>
                    <td></td>
                </tr>
                <tr>
                    <td><input type="text" name="name" size="10"/></td>
                    <td><select name="parent">
                            <?
                            $tags = $melan_gallery->archi_gallery_tags();
                            ?>
                        </select></td>
                    <td><input type="text" name="directory" size="35"/></td>
                    <td><textarea cols="24" rows="3" name="details"></textarea></td>
                    <td><input name="addnew" value="Add" type="submit"/></td>
                </tr>
            </table>
        </form>
        <br/><br/>
        <b>Current tags for gallery</b>:<br/>
        <form method="post">
            <table border="0" width="100%">
                <tr>
                    <td colspan="2"><input name="editcat" value="Edit filters" type="submit"/></td>
                </tr>
                <tr>
                    <td>
                        <table border="0">
                            <tr class="tabletop">
                                <td>&nbsp;delete&nbsp;</td>
                                <td>&nbsp;tag&nbsp;</td>
                                <td>&nbsp;display name&nbsp;</td>
                                <td>&nbsp;parent&nbsp;</td>
                                <td>&nbsp;directory&nbsp;</td>
                                <td>&nbsp;description&nbsp;</td>
                            </tr>
                            <?
                            // displays an organized filter list
                            $getFilters = mysqli_query($db_link,
                                "SELECT * FROM uber_tags ORDER by parent,name");
                            for ($i = 0; $info = mysqli_fetch_array($getFilters); $i++) {
                                $main_sql = "FROM uber_gallery AS gallery INNER JOIN uber_tag_assoc AS association INNER JOIN uber_tags as tags ON tags.id = '" . $info['id'] . "' where association.image_id = gallery.id and association.tag_id = tags.id";
                                if ($info['name'] == 'all') {
                                    $main_sql = "FROM uber_gallery AS gallery INNER JOIN uber_tag_assoc AS association INNER JOIN uber_tags as tags ON association.image_id = gallery.id where tags.id = association.tag_id order by gallery.occurred desc";
                                } elseif ($info['children']) {
                                    $children = $melan_gallery->getAllchildren($info['id']);
                                    $main_sql = "FROM uber_gallery AS gallery INNER JOIN uber_tag_assoc AS association INNER JOIN uber_tags as tags ON association.image_id = gallery.id where association.tag_id = tags.id AND tags.id IN (" . implode(',',
                                            $children) . ") order by gallery.occurred desc";
                                }
                                $page_sql = "select count(distinct(gallery.id)) as total " . $main_sql;
                                $count_sql = mysqli_query($db_link, $page_sql);
                                $count = mysqli_fetch_array($count_sql);
                                mysqli_query($db_link,
                                    "UPDATE uber_tags set count='" . $count['total'] . "' where id='" . $info['id'] . "'");
                                ?>
                                <tr>
                                    <td><input type="checkbox" value="<?= $info['id'] ?>"
                                               name="delete[<?= $info['id'] ?>]"/></td>
                                    <td><?= $info['name'] ?> (<?= $count['total'] ?>)</td>
                                    <td><input size="35" type="text" name="display_name[<?= $info['id'] ?>]"
                                               value="<?= $info['display_name']; ?>"/></td>
                                    <td><select name="parent[<?= $info['id'] ?>]"><?
                                            $melan_gallery->archi_gallery_tags($info['parent']); ?></select></td>
                                    <td><input size="35" type="text" name="directory[<?= $info['id'] ?>]"
                                               value="<?= $info['directory']; ?>"/></td>
                                    <td><textarea cols="24" rows="3"
                                                  name="details[<?= $info['id'] ?>]"><?= $info['details']; ?></textarea>
                                    </td>
                                </tr>

                            <?
                            } ?>
                        </table>
                    </td>
                    <td valign="top">'all' is a reserved filter. Calling it will display
                        everything.<br/><br/><b>Parent</b>: If the filter is a subfilter, set the filter it lives
                        inside. <br/><br/>Filters without a root will show all the content of the subfilters inside it
                        as default. Subfilters are the only ones you can add images to.<br/><br/><b>Extends</b>: This is
                        for your subfilters. When you select a filter from here it becomes included in addition to the
                        current filter. Should that extended filter also have an extension, then it becomes appended as
                        well. wee!
                </tr>
                <tr>
                    <td colspan="2"><input name="editcat" value="Edit filters" type="submit"/></td>
                </tr>
            </table>
        </form><br/><br/>
        <?
    }
}

/***********************************
 * Section to add images to filters *
 ***********************************/
function archi_gallery_options_add()
{

    //global $directory,$db_link;
    $melan_gallery = new melan_gallery(get_option('archi_gallery_directory'));
    $directory = $melan_gallery->get_directory();
    $db_link = $melan_gallery->get_link();
    // show this only if the directory set for images exists
    if (file_exists($directory)) {

        // add image and info to the gallery
        for ($i = 0; $i < count($_FILES["image"]['tmp_name']); $i++) {
            if ($_FILES["image"]["name"][$i]) {
                if ($_POST["name"][$i]) {
                    $filename = $melan_gallery->getFilename($_POST["name"][$i] . "." . $_POST['type'][$i]);
                } else {
                    $filename = $melan_gallery->getFilename($_FILES["image"]["name"][$i]);
                }
                $name = preg_replace('/.{4}$/', '', $filename);
                $getFilters = mysqli_query($db_link,
                    "SELECT * FROM uber_tags WHERE id=" . $_POST['parent'] . " limit 1");
                $tag_dir = mysqli_fetch_array($getFilters);
                $originals = $directory . "/" . $tag_dir['directory'] . "/originals";
                if (! file_exists($directory . "/" . $tag_dir['directory'])) {
                    mkdir($directory . "/" . $tag_dir['directory']);
                    mkdir($directory . "/" . $tag_dir['directory'] . "/t2");
                }
                if (! file_exists($originals)) {
                    mkdir($originals);
                    chmod($originals, '0600');
                }
                $original = $directory . "/" . $tag_dir['directory'] . "/originals/" . $filename;
                $source = $directory . "/" . $tag_dir['directory'] . "/" . $filename;
                if (move_uploaded_file($_FILES["image"]["tmp_name"][$i], $original)) { // save original
                    $melan_gallery->resize_image($original, $source, 650);
                    $melan_gallery->store_image($name, $_POST['date'][$i], $_POST['type'][$i], $_POST['parent']);
                }
            }
        }
        $date = date("Y-m-d");
        ?>
        <script type="text/javascript">
            var i = 0;

            function add_images() {
                var fields = document.getElementById('copy');
                var form = document.getElementById('paste');
                var newdiv = document.createElement('div');
                newdiv.innerHTML = fields.innerHTML;
                var divname = 'div' + i++;
                newdiv.setAttribute('id', divname);
                form.appendChild(newdiv);
                return false;
            }
        </script>
        <br/><br/>
        <b>Upload images</b> <a href="#" onclick="add_images()">+ Add</a><br/><br/>
        <form method="post" name="gallery" id="add_form" enctype="multipart/form-data">
            <select name="parent" class="parent"><?
                $melan_gallery->archi_gallery_tags(); ?></select><br/><br/>
            <div id="copy">
                <input type="text" name="date[]" placeholder="date" value="<?= $date ?>"/>
                <input type="text" name="name[]" placeholder="name"/>
                <select name="type[]">
                    <option>jpg</option>
                    <option>swf</option>
                </select>
                <input type="file" name="image[]"/>
            </div>
            <div id="paste"></div>
            <input type="submit" value="Add"/>
        </form><br/><br/>
        <?
    } else {
        echo "<br /><br />/$directory/ does not exist, please change this setting or create the directory.";
    }

}

/**
 * Edit the signature on an image that has an original stored
 */
function archi_gallery_options_edit_sig()
{

    $melan_gallery = new melan_gallery(get_option('archi_gallery_directory'));
    $directory = $melan_gallery->get_directory();
    $db_link = $melan_gallery->get_link();
    $id = $_GET['id'];
    $img = $melan_gallery->getImage($id);
    $filename = $melan_gallery->getFilename($img['img'] . "." . $img['type']);
    $source = $directory . "/" . $img['directory'] . "/" . $filename;
    $original = $directory . "/" . $img['directory'] . "/originals/" . $filename;
    if (file_exists($original)) {

        if ($_POST['offset']) {
            $offset = explode('x', $_POST['offset']);
            $melan_gallery->resize_image($original, $source, 650, $offset[0], $offset[1]);
        }
        $size = GetImageSize($source);

        $image = "../../memory/" . $img['directory'] . "/originals/" . $filename;
        $sig = "../../memory/sig.png";
        ?>
        <script type="text/javascript">
            var canvas;
            var context;

            document.addEventListener('DOMContentLoaded', init, false);

            function make_base() {
                base_image = new Image();
                base_image.src = '<?=$image?>';
                base_image.onload = function() {
                    context.drawImage(base_image, 0, 0 <?=$size[0]?>,<?=$size[1]?>);
                }
            }

            function make_sig(x, y) {
                context.clearRect(0, 0<?=$size[0]?>,<?=$size[1]?>);
                make_base();
                base_sig = new Image();
                base_sig.src = '<?=$sig?>';
                base_sig.onload = function() {
                    context.drawImage(base_sig, x, y);
                }
            }

            function init() {
                canvas = document.getElementById('canvas');
                canvas.addEventListener('mousedown', getPosition, false);
                context = canvas.getContext('2d');

                make_base();
            }

            function getPosition(event) {
                var doc = document.documentElement;
                var left = (window.pageXOffset || doc.scrollLeft) - (doc.clientLeft || 0);
                var top = (window.pageYOffset || doc.scrollTop) - (doc.clientTop || 0);
                var x = new Number();
                var y = new Number();

                if (event.x != undefined && event.y != undefined) {
                    x = event.x - 181 + left;
                    y = event.y - 32 + top;
                } else // Firefox method to get the position
                {
                    x = event.clientX + document.body.scrollLeft + document.documentElement.scrollLeft;
                    y = event.clientY + document.body.scrollTop + document.documentElement.scrollTop;
                }

                x -= canvas.offsetLeft;
                y -= canvas.offsetTop;
                make_sig(x, y, context);
                var offset = document.getElementById('offset');
                offset.value = x + 'x' + y;
            }

        </script>

        <form method="POST"><input type="text" id="offset" name="offset"/><input type="submit"/></form><br/>
        <canvas id="canvas" <?= $size[3] ?>></canvas>
        <?
    } else {
        echo "Cannot add a sig to this image." . $original;
    }
}

/*************************************
 * Edit images stored in the db       *
 *************************************/
function archi_gallery_options_edit()
{

    //global $db_link,$directory;
    $melan_gallery = new melan_gallery(get_option('archi_gallery_directory'));
    $directory = $melan_gallery->get_directory();
    $db_link = $melan_gallery->get_link();
    if ($_GET['id']) {
        archi_gallery_options_edit_sig();
    } else {
        // delete multiple entries
        if (is_array($_POST['archi_delete'])) {
            while (list($key, $val) = each($_POST['archi_delete'])) {
                $img = $melan_gallery->getImage($val);
                @unlink($directory . "/" . $img['directory'] . "/originals/" . $melan_gallery->getFilename($img['img'] . '.' . $img['type']));
                @unlink($directory . "/" . $img['directory'] . "/" . $melan_gallery->getFilename($img['img'] . '.' . $img['type']));
                @unlink($directory . "/" . $img['directory'] . "/t2/" . $img['thumb']);
                mysqli_query($db_link, "DELETE FROM uber_tag_assoc WHERE image_id='$val'");
                mysqli_query($db_link, "DELETE FROM uber_gallery WHERE id='$val'");

            }
        }

        // update multiple entries
        if (is_array($_POST['archi_id'])) {

            while (list($i, $val) = each($_POST['archi_id'])) {
                $img = $_POST['archi_img'][$i];
                $thumb = $_POST['archi_thumb'][$i];
                $filter = $_POST['archi_tag'][$i];
                $type = $_POST['archi_type'][$i];
                $text = $_POST['archi_text'][$i];
                //update image size and filesize
                mysqli_query($db_link,
                    "UPDATE uber_gallery set type='$type',thumb='$thumb',text='$text' WHERE id='$val'");
                mysqli_query($db_link, "UPDATE uber_tag_assoc set tag_id='$filter' WHERE image_id='$val'");
                $message[$i] = "<td>&nbsp;<i>updated.</i>&nbsp;</td>";

            }
        }

        $getRet = mysqli_query($db_link, "SELECT * FROM uber_tag_assoc");
        $ret = mysqli_num_rows($getRet);
        if (! $_GET[p]) {
            $p = 1;
        } else {
            $p = $_GET[p];
        }
        $number = 15;
        $max = $p * $number;
        $min = $max - $number;
        $pages = ceil($ret / $number);
        //echo $ret." $_GET[p] max:$max min:$min";

        if ($pages > 1) {
            $back = $p - 1;
            $forward = $p + 1;
            for ($count = 1; $count <= $pages; $count++) {
                $pagelist .= "<a href=\"?page=$_GET[page]&p=$count\">";
                if ($p == $count) {
                    $pagelist .= "[<b><font color=\"#808080\">";
                }

                $pagelist .= "$count";
                if ($p == $count) {
                    $pagelist .= "</font></b>]";
                }

                $pagelist .= "</a> | ";

            }
        }

        echo "<br /><br />" . $pagelist;

        ?>
        <form method="post">
            <input type="submit"/>
            <table border="0">
                <tr class="tabletop">
                    <td>&nbsp;<b>delete</b>&nbsp;</td>
                    <td>&nbsp;<b>thumb</b>&nbsp;</td>
                    <td>&nbsp;<b>img id</b>&nbsp;</td>
                    <td>&nbsp;<b>filter</b>&nbsp;</td>
                    <td>&nbsp;<b>extension</b>&nbsp;</td>
                    <td>&nbsp;<b>message</b>&nbsp;</td>
                </tr>
                <?
                $list = mysqli_query($db_link,
                    "SELECT distinct(tag_assoc.image_id), tag_assoc.tag_id,gallery.*,tag.name, tag.directory, tag.details, tag.parent FROM uber_tags tag, uber_tag_assoc tag_assoc, uber_gallery gallery where tag.id = tag_assoc.tag_id and tag_assoc.image_id = gallery.id ORDER by tag.name,gallery.img limit $min,15");
                for ($i = 0; $info = @mysqli_fetch_array($list); $i++){
                    if ($i % 2 == 0) {
                        $class = "highlight1";
                    } else {
                        $class = "highlight2";
                    }
                    $thumb = "<img src=\"/memory/archi_thumb.php?id=$info[id]\" border=\"0\" />";

                ?>
                <tr class="<?= $class ?>">
                    <td>
                        &nbsp;<input type="hidden" name="archi_id[<?= $i ?>]" value="<?= $info['id'] ?>"/><input
                                type="checkbox" value="<?= $info['id'] ?>" name="archi_delete[<?= $i ?>]"/>&nbsp;
                    </td>
                    <td><?= $thumb ?></td>
                    <td>&nbsp;<input type="hidden" name="archi_img[<?= $i ?>]"
                                     value="<?= $info['img'] ?>"/><?= $info['img'] ?>&nbsp;<br/>
                        <input size="20" type="text" name="archi_thumb[<?= $i ?>]" value="<?= $info['thumb'] ?>"/><br/>
                        <textarea colspan='10' rowspan='2'
                                  name="archi_text[<?= $i ?>]"><?= $info['text'] ?></textarea><br/>
                        <?
                        if ($melan_gallery->hasOriginal($info[1], $info['img'] . '.' . $info['type'])) {
                            ?><a href="?page=archi_edit&id=<?= $info['id'] ?>">Edit Sig</a><?
                        } ?>
                    </td>
                    <td><select name='archi_tag[<?= $i ?>]'>
                            <?
                            $melan_gallery->archi_gallery_tags($info[1]);
                            ?>
                        </select></td>
                    <td><input type="text" name="archi_type[<?= $i ?>]" value="<?= $info['type'] ?>" size="5"/></td>
                    <?
                        if (! $message[$i]) {
                            echo "<td>&nbsp;<i>none.</i>&nbsp;</td>";
                        } else {
                            echo $message[$i];
                        }
                        echo "</tr>";

                    }


                    ?>
            </table>
            <input type="submit"/>
        </form>
        <?
        echo $pagelist;
    }
}

//add_menu_page('page_title', 'menu_title', 'level_10', 'my_plugin_admin.php', 'my_plugin_options', '');

class melan_gallery
{

    private $folder;
    private $directory;
    private $db_link;

    public function __construct($folder)
    {

        $this->folder = $folder;
        $this->db_link = new mysqli("localhost", "lisa", "******", "sushi");
        $this->directory = $_SERVER['DOCUMENT_ROOT'] . "/" . $this->folder;
    }

    public function get_folder()
    {

        return $this->folder;
    }

    public function get_directory()
    {

        return $this->directory;
    }

    public function get_link()
    {

        return $this->db_link;
    }

    public function hasOriginal($tag_id, $filename)
    {

        $getFilters = mysqli_query($this->db_link,
            "SELECT * FROM uber_tags WHERE id=" . $tag_id . " limit 1");
        $tag_dir = mysqli_fetch_array($getFilters);
        $original = $this->directory . "/" . $tag_dir['directory'] . "/originals/" . $filename;
        return file_exists($original);
    }

    /**
     * Echo the list of filters for this gallery
     * @param int $parent parent id
     */
    public function archi_gallery_tags($parent)
    {

        ?>
        <option value="0"></option>
        <?
        $tags = @mysqli_query($this->db_link, "select * from uber_tags order by name");
        for ($i = 0; $info = mysqli_fetch_array($tags); $i++) {
            ?>
            <option value="<?= $info['id'] ?>"<?
            if ($parent == $info['id']) {
                ?> selected="selected"<?
            } ?>><?= $info['name'] ?></option>
            <?
        }
    }

    /**
     * get the image info from the id
     * @param int $id image id
     * @return array     table info
     */
    public function getImage($id)
    {

        $sql = mysqli_query($this->db_link,
            "select gallery.*, tags.directory FROM uber_gallery AS gallery INNER JOIN uber_tag_assoc AS association INNER JOIN uber_tags as tags ON gallery.id = '" . $id . "' and association.image_id = gallery.id and tags.id = association.tag_id");
        return mysqli_fetch_array($sql);
    }

    /**
     * Clean the filename
     * @param string $name
     * @return string       remove spaces
     */
    public function getFilename($name)
    {

        return preg_replace('/ /', '_', $name);
    }

    /**
     * Save the original and make a smaller sample with signature
     * @param string $original file path of the original image (unaltered)
     * @param string $source file path to save to
     * @param int $desired_width resized width
     * @param int $x position to place a signature
     * @param int $y position to place a signature
     */
    public function resize_image($original, $source, $desired_width = null, $x = null, $y = null)
    {

        $source_image = imagecreatefromjpeg($original);
        $width = imagesx($source_image);
        $height = imagesy($source_image);
        if (! $desired_width) {
            $desired_width = 650;
        }
        $max_height = 650;
        if ($width >= $desired_width || $height > $max_height) { //resize
            if ($desired_width < $width && $width >= $height) {
                $desired_width = $desired_width;
                $desired_height = ($desired_width / $width) * $height;
            } elseif ($max_height < $height && $height >= $width) {
                $desired_height = $max_height;
                $desired_width = ($desired_height / $height) * $width;
            } else {
                $desired_height = $height;
                $desired_width = $width;
            }

            $virtual_image = imagecreatetruecolor($desired_width, $desired_height);

            imagecopyresampled($virtual_image, $source_image, 0, 0, 0, 0, $desired_width, $desired_height, $width,
                $height);
            ImageJPEG($virtual_image, $source, 100);
            imagedestroy($virtual_image);
        } else {
            copy($original, $source);
        }
        $this->make_sig($source, $source, $x, $y);
    }

    /**
     * Create an entry in the database
     * @param string $name image name (without extension)
     * @param string $date Y-m-d
     * @param string $type image extension
     * @param int $parent parent id
     */
    public function store_image($name, $date, $type, $parent)
    {

        $date = date('Y-m-d', strtotime($date));

        $thumb = preg_replace('/-/', '', $date) . $name . ".gif";
        $thumb = preg_replace('/^.{2}/', '', $thumb);
        mysqli_query($this->db_link,
            "INSERT INTO uber_gallery set occurred='" . $date . "',thumb='" . $thumb . "',img='" . $name . "',type='" . $type . "'");
        $getId = mysqli_query($this->db_link,
            "SELECT * FROM uber_gallery where img='" . $name . "' order by id desc limit 1");
        $id = mysqli_fetch_array($getId);
        if ($id) {
            mysqli_query($this->db_link,
                "INSERT INTO uber_tag_assoc set image_id='" . $id['id'] . "', tag_id='" . $parent . "'");
        }
    }

    /**
     * Add signature to resized image
     * @param string $source file path to add the signature to
     * @param string $destination file path to save to
     * @param int $x position to place a signature
     * @param int $y position to place a signature
     */
    public function make_sig($source, $destination, $x = null, $y = null)
    { // needs to be placed on original not the resized version
        $source_image = imagecreatefromjpeg($source);
        $sig_image = imagecreatefrompng($this->directory . "/sig.png");
        imagesavealpha($sig_image, true);
        imagealphablending($sig_image, true);
        if (! $x || ! $y) {
            $x = 15;
            $y = imagesy($source_image) - imagesy($sig_image) - 15;
        }
        imagecopyresampled($source_image, $sig_image, $x, $y, 0, 0, imagesx($sig_image), imagesy($sig_image),
            imagesx($sig_image), imagesy($sig_image));
        ImageJPEG($source_image, $destination, 100);
        imagedestroy($source_image);
        imagedestroy($sig_image);
    }

    public function dbPrepare($sql)
    {

        $stmt = $this->db_link->prepare($sql);
        if ($stmt === false) {
            die(mysql_error());
        }
        return $stmt;
    }

    public function getAllchildren($id)
    {

        $children = $this->getChildren($id);
        $all_children = $children;
        foreach ($children as $child) {
            $all_children = array_merge($all_children, $this->getChildren($child));
        }
        return $all_children;
    }

    private function getChildren($id)
    {

        $sql = "select id FROM uber_tags where parent = ?";
        $stmt = $this->dbPrepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->bind_result($cid);
        $children = [];
        for ($i = 0; $stmt->fetch(); $i++) {
            $children[] = $cid;
        }
        return $children;
    }
}

?>
