<?php
$smallViewCss = '';
$smallViewCssFile = 'css/youtube-smallview.css';
if(file_exists($smallViewCssFile)){
    $smallViewCss = file_get_contents($smallViewCssFile);
}
$markup = <<<MARKUP
<style type="text/css">
{$smallViewCss}
</style>
<div class="bd">
<h1 class="heading"><yml:a view="YahooFullView">Youtube Videos</yml:a></h1>
<div class="image-prev">
<yml:a view="YahooFullView">
<img src="images/baby_bowling.png" width="250" height="190"></img>
</yml:a>
</div>
<div class="info">168,498 views 3:20</div>
<div class="info">BABY BOWLING! by SHAYTARDS </div>
<div class="info">
<yml:a view="YahooFullView">
Watch Youtube Vidoes
</yml:a>
</div>
</div>
MARKUP;
echo $markup;
?>
