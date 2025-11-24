<?php

/**
 * ALTO File Viewer
 *
 * @package    AltoViewer
 * @author     Dan Field <dof@llgc.org.uk>
 * @copyright  Copyright (c) 2010 National Library of Wales / Llyfrgell Genedlaethol Cymru. 
 * @link       http://www.llgc.org.uk
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License 3
 * @version    $Id$
 * @link       http://www.loc.gov/standards/alto/
 * 
 **/

require_once 'lib/AltoViewer.php';

$vScale = isset($_REQUEST['vScale']) ? (float) $_REQUEST['vScale'] : 0.6;
$hScale = isset($_REQUEST['hScale']) ? (float) $_REQUEST['hScale'] : 0.6;

$config = parse_ini_file('./config/altoview.ini');
if ($config === false) {
    die('Failed to read the configuration file.');
}

$availableImages = AltoViewer::listAvailableFiles($config['altoDir'], $config['imageDir']);
if (count($availableImages) === 0) {
    die('No ALTO files with associated images were found.');
}

$requestedImage = isset($_REQUEST['image']) ? (string) $_REQUEST['image'] : null;
if (empty($requestedImage) || !in_array($requestedImage, $availableImages, true)) {
    $image = $availableImages[0];
} else {
    $image = $requestedImage;
}

$currentIndex = array_search($image, $availableImages, true);
$prevImage = ($currentIndex > 0) ? $availableImages[$currentIndex - 1] : null;
$nextImage = ($currentIndex < count($availableImages) - 1) ? $availableImages[$currentIndex + 1] : null;

$baseQuery = array(
    'vScale' => $vScale,
    'hScale' => $hScale
);

$prevUrl = $prevImage ? ('?' . http_build_query(array_merge($baseQuery, array('image' => $prevImage)))) : null;
$nextUrl = $nextImage ? ('?' . http_build_query(array_merge($baseQuery, array('image' => $nextImage)))) : null;

$altoViewer = new AltoViewer(   $config['altoDir'], 
                                $config['imageDir'], 
                                $image, $vScale, $hScale);
$imageSize = $altoViewer->getImageSize();
$strings = $altoViewer->getStrings();
$textLines = $altoViewer->getTextLines();
$textBlocks = $altoViewer->getTextBlocks();
$printSpace = $altoViewer->getPrintSpace();

$scaledHeight = $imageSize[1] * $vScale;
$scaledWidth = $imageSize[0] * $hScale;

?>

<html>
    <head>
        <link rel="stylesheet" type="text/css" href="css/style.css" />
        <script type="text/javascript" src="js/jquery-1.4.2.min.js"></script>          
        <title>ALTO Viewer - <?php echo $image; ?> - <?php echo $vScale; ?> x <?php echo $hScale; ?> - (<?php echo $imageSize[0]; ?>x<?php echo $imageSize[1]; ?>px)</title>
    </head>
    <body>
        <div class="menu">
            <div class="menuBox nav-controls">
                <span>Navigate pages</span><br />
                <a 
                    class="nav-arrow <?php echo $prevUrl ? '' : 'disabled'; ?>" 
                    <?php if ($prevUrl) { ?>href="<?php echo $prevUrl; ?>"<?php } ?>
                    title="Previous">&larr;</a>
                <span class="nav-status">
                    <?php echo ($currentIndex + 1) . ' / ' . count($availableImages); ?>
                </span>
                <a 
                    class="nav-arrow <?php echo $nextUrl ? '' : 'disabled'; ?>" 
                    <?php if ($nextUrl) { ?>href="<?php echo $nextUrl; ?>"<?php } ?>
                    title="Next">&rarr;</a>
            </div>
            <div class="menuBox" id="toggleBox">
                <span>Toggle Layers</span><br />
                <button id="strings" >Strings</button><br />
                <button id="lines" >TextLine</button><br />
                <button id="blocks" >TextBlock</button><br />
                <button id="printspace" >PrintSpace</button><br />
            </div>
            <div class="menuBox" id="infoBox">
                <span>Selected text</span>
                <div id="regionText">Hover over a region.</div>
            </div>
        </div>
        
        <div id="image">
            <img 
                src="image.php?file=<?php echo urlencode($image); ?>"  
                width="<?php echo $scaledWidth; ?>" 
                height="<?php echo $scaledHeight; ?>" />
            <?php foreach ($strings as $string) { ?>
                <div class="highlighter" id="highlight-string" 
                    style=" left: <?php echo $string->getHPos(); ?>px; 
                            top: <?php echo $string->getVPos(); ?>px; 
                            width: <?php echo $string->getWidth(); ?>px; 
                            height: <?php echo $string->getHeight(); ?>px; 
                            filter: alpha(opacity=50)" 
                    data-content="<?php echo htmlspecialchars($string->getContent(), ENT_QUOTES, 'UTF-8'); ?>">
                </div>
            <?php } ?>
            <script>
                $("button[id*=strings]").click(function () {
                $("div[id*=highlight-string]").toggle();
                });    
            </script>
            
            <?php foreach ($textLines as $textLine) { ?>
                <div class="highlighter" id="highlight-line" 
                    style=" left: <?php echo $textLine->getHPos(); ?>px; 
                            top: <?php echo $textLine->getVPos(); ?>px; 
                            width: <?php echo $textLine->getWidth(); ?>px; 
                            height: <?php echo $textLine->getHeight(); ?>px; 
                            filter: alpha(opacity=50)" 
                    data-content="<?php echo htmlspecialchars($textLine->getContent(), ENT_QUOTES, 'UTF-8'); ?>">
                </div>
            <?php } ?>
            <script>
                $("button[id*=lines]").click(function () {
                $("div[id*=highlight-line]").toggle();
                });    
            </script>
        
            <?php foreach ($textBlocks as $textBlock) { ?>
                <div class="highlighter" id="highlight-block" 
                    style=" left: <?php echo $textBlock->getHPos(); ?>px; 
                            top: <?php echo $textBlock->getVPos(); ?>px; 
                            width: <?php echo $textBlock->getWidth(); ?>px; 
                            height: <?php echo $textBlock->getHeight(); ?>px; 
                            filter: alpha(opacity=50)" 
                    data-content="<?php echo htmlspecialchars($textBlock->getContent(), ENT_QUOTES, 'UTF-8'); ?>">
                </div>
            <?php } ?>
            <script>
                $("button[id*=blocks]").click(function () {
                $("div[id*=highlight-block]").toggle();
                });    
            </script>
            
            <div class="highlighter" id="highlight-printspace" 
                style=" left: <?php echo $printSpace->getHPos(); ?>px; 
                        top: <?php echo $printSpace->getVPos(); ?>px; 
                        width: <?php echo $printSpace->getWidth(); ?>px; 
                        height: <?php echo $printSpace->getHeight(); ?>px; 
                        display: none;
                        filter: alpha(opacity=50)" 
                data-content="<?php echo htmlspecialchars($printSpace->getContent(), ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <script>
                $("button[id*=printspace]").click(function () {
                $("div[id*=highlight-printspace]").toggle();
                });    
            </script>

            <script>
                $(function () {
                    var $regionText = $("#regionText");
                    $(".highlighter").hover(
                        function () {
                            var text = $(this).attr("data-content");
                            if (text && text.length) {
                                $regionText.text(text);
                            } else {
                                $regionText.text("No text available.");
                            }
                        },
                        function () {
                            $regionText.text("Hover over a region.");
                        }
                    );
                });
            </script>
            
                    
        </div>
    </body>
</html>
