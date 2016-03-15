<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


if (isset($_POST['input-charsets'])) {

    include ('RandString.php');
    $oRS = new b_and_p\RandStrNS\RandString();


    $length = filter_input(INPUT_POST, 'input-length', FILTER_SANITIZE_NUMBER_INT);
    $parts = filter_input(INPUT_POST, 'input-parts', FILTER_SANITIZE_NUMBER_INT);
    $delim = filter_input(INPUT_POST, 'input-delim', FILTER_SANITIZE_STRING);
    $prefix = filter_input(INPUT_POST, 'input-prefix', FILTER_SANITIZE_STRING);
    $suffix = filter_input(INPUT_POST, 'input-suffix', FILTER_SANITIZE_STRING);
    $count = filter_input(INPUT_POST, 'input-count', FILTER_SANITIZE_NUMBER_INT);
    $unique = isset($_POST['input-unique']) ? true : false;

    $oRS->part_length = $length; // INT
    $oRS->parts = $parts;   // INT
    $oRS->delim = $delim;   // STR
    $oRS->prefix = $prefix;   // STR
    $oRS->suffix = $suffix;   // STR
    $oRS->count = $count;   // STR
    $oRS->unique = $unique; //BOOL

    $flags = 0;
    if (isset($_POST['input-charsets'])) {
        foreach ($_POST['input-charsets'] as $item) {
            $flags += (int) $item;
        }
    }

    $oRS->flags = $flags;

    $result = $oRS->generate();
}
?>
<!doctype html>
<html>
    <head>
        <title>Random String generator</title>

        <style type="text/css">
            body {
                font-family: calibri, sans-serif
            }
            .form label {
                display:inline-block;
                width: 140px;
                font-weight: bold;
            }

            .form-group {
                margin:4px;
                padding:4px;
                border: solid 1px #ccc;
                border-radius: 6px;
                width: 400px;
                height: 1.6em;
            }
            .form-group input {
                float:right;
                padding:4px;
                margin-bottom: 4px;
            }

            #output {
                width:400px;
            }

            #output textarea {
                width:100%;
                height: 200px;
            }

        </style>

    </head>
    <body>
        <form method="post" enctype="multipart/form-data">
            <div class="form">
                <div class="form-group">
                    <label for="input-length">Part length</label>
<?php
$val = isset($length) ? $length : 2;
?>
                    <input type="number" min="1" name="input-length" value="<?php echo $val; ?>">
                </div>
                <div class="form-group">
                    <label for="input-parts">Parts</label>
<?php
$val = isset($parts) ? $parts : 2;
?>
                    <input type="number" min="1" name="input-parts" value="<?php echo $val; ?>">
                </div>
                <div class="form-group">
                    <label for="input-delim">Delimiter</label>
<?php
$val = isset($delim) ? $delim : '-';
?>
                    <input type="text"  name="input-delim" value="<?php echo $val; ?>">
                </div>

                <div class="form-group">
                    <label for="input-prefix">Prefix</label>
<?php
$val = isset($prefix) ? $prefix : '';
?>
                    <input type="text"  name="input-prefix" value="<?php echo $val; ?>">
                </div>


                <div class="form-group">
                    <label for="input-suffix">Suffix</label>
<?php
$val = isset($suffix) ? $suffix : '';
?>
                    <input type="text"  name="input-suffix" value="<?php echo $val; ?>">
                </div>

                <div class="form-group">

<?php
$bitmask = isset($flags) ? $flags : 0;
?>

                    <label for="input-charset1">Alpha lower case</label>
                    <?php
                    $checked = ($bitmask & (1 << 0)) ? 'checked' : '';
                    ?>
                    <input type="checkbox" id ="input-charset1" name="input-charsets[]" value="1" <?php echo $checked; ?> >
                </div>
                <div class="form-group">
                    <label for="input-charset2">Alpha upper case</label>
<?php
$checked = ($bitmask & (1 << 1)) ? 'checked' : '';
?>
                    <input type="checkbox"  id ="input-charset2" name="input-charsets[]" value="2"  <?php echo $checked; ?> >
                </div>
                <div class="form-group">
                    <label for="input-charset3">Numbers</label>
<?php
$checked = ($bitmask & (1 << 2)) ? 'checked' : '';
?>
                    <input type="checkbox"  id ="input-charset3" name="input-charsets[]" value="4"  <?php echo $checked; ?> >
                </div>
                <div class="form-group">
                    <label for="input-charset4">Special characters</label>
<?php
$checked = ($bitmask & (1 << 3)) ? 'checked' : '';
?>
                    <input type="checkbox"  id ="input-charset4" name="input-charsets[]" value="8"  <?php echo $checked; ?> >
                </div>

                <div class="form-group">
                    <label for="input-length">Count</label>
<?php
$val = isset($count) ? $count : 2;
?>
                    <input type="number" min="1" name="input-count" value="<?php echo $val; ?>">
                </div>

                <div class="form-group">
                    <label for="input-length">Unique</label>
<?php
$checked = isset($unique) && $unique;
$checked_str = $checked ? 'checked' : '';
?>
                    <input type="checkbox"  name="input-unique" <?php echo $checked_str; ?>>
                </div>
            </div>
            <button type="submit">Submit</button>
        </form>

        <div id="output">
            <textarea readonly="true">
<?php
if (isset($oRS)) {
    if (!$oRS->error) {
        foreach ($result as $string) {
            echo $string . "\n";
        }
    } else {
        echo $oRS->error;
    }
}
?>
            </textarea>
                <?php
                if (isset($oRS)) {
                    echo 'DEBUG INFO:<br>';
                    echo 'Duplicates found in generation cycle<br>';
                    var_dump($oRS->duplicates);
                    echo 'History - all items in generation cycle<br>';
                    var_dump($oRS->history);
                }
                ?>
        </div>
    </body>
</html>