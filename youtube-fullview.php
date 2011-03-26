<?php
//Set include paths
$clientLibraryPath = '/home/ravikira/public_html/drupal/sites/all/hacks/youtube-yap-app/googledata/ZendGdata-1.11.3/library/';
$oldPath = set_include_path(get_include_path() . PATH_SEPARATOR . $clientLibraryPath);

//Include Lib Files
require_once('/home/ravikira/public_html/drupal/sites/all/hacks/youtube-yap-app/googledata/ZendGdata-1.11.3/library/Zend/Loader.php');
//Load Zend_Gdata_YouTube
Zend_Loader::loadClass('Zend_Gdata_YouTube');
class YoutubeApp {
    //Private Variables
    private $_SESSION;
    private $yt;
    private $ytUrl = '';

    function __construct($args = array()){
        $this->_SESSION['devKey'] = 'AI39si5MfQ4tKW5gqwUnW54F6AmXe-KDxoSgbVDWWocl9j7_ffaibvqO1DWDhmltc8Tupfl_eEgdQib2WbV0jbwllVfCdbPjkA'; 
        $this->yt = new Zend_Gdata_YouTube();
        $this->yt->setMajorProtocolVersion(2);
        $this->vidUrls = array(
                               'mostViewed' => 'http://gdata.youtube.com/feeds/api/standardfeeds/most_viewed?time=today',
                               'topFavorites' => 'http://gdata.youtube.com/feeds/api/standardfeeds/top_favorites?time=today',
                               'topRated' => 'http://gdata.youtube.com/feeds/api/standardfeeds/top_rated?time=today',
                               'trendingVideos' => 'http://gdata.youtube.com/feeds/api/standardfeeds/on_the_web'
                         );
    }

    function prepareContent() {
        //Get Most Viewed Data
        $mostViewedData = $this->getVideoFeedData($this->vidUrls['mostViewed']);
        //Get Top Favorites Data
        $topFavoritesData = $this->getVideoFeedData($this->vidUrls['topFavorites']); 
        //Get Top Rated Data
        $topRatedData = $this->getVideoFeedData($this->vidUrls['topRated']);
        //Get Trending Videos Data
        $trendingVideosData = $this->getVideoFeedData($this->vidUrls['trendingVideos']);
        $videoData = array(
                    'mostViewed' =>$mostViewedData,
                    'topFav' => $topFavoritesData,
                    'topRated' => $topRatedData,
                    'trendVid' => $trendingVideosData
                  );
        return $videoData;
    }

    function prepareMarkup($data = array()) {
        $markup = null;
        foreach($data as $vidType => $vidData){
            $vt =$vidType;
            for($i = 0 ; $i < count($vidData); $i++) {
                if($i == 12) {
                    break;
                }
                $v = $vidData[$i];
                if($i % 4 == 0) {
                    $row = $i / 4;
                    if($i == 0) {
                        $markup[$vidType] .= "<ul class='vid-row' id='{$vt}-vid-row-{$row}'>";
                    }else {
                        $markup[$vidType] .= "</ul> <ul class='vid-row' id='{$vt}=vid-row-{$row}'>";
                    }
                }
                $mins = (int)($v['duration'] / 60);
                $secs = (int) ($v['duration'] % 60);
                if($secs < 10){
                    $secs = '0'.$secs;
                }
                $duration = $mins . ':' . $secs;
                $views = number_format((float)$v['views'], 0, '.', ',');
                $title = $v['title'];
                if(strlen($title) > 50) {
                    $title = substr($title, 0, 46) . "...";
                }
                $escapedTitle = addslashes($v['title']);
                $vidUrl = 'http://www.youtube.com/watch?v='.$v['id'];
                $markup[$vidType] .= <<<MARKUP
<li class="vid-thumbnail" id="{$vt}_vid_thumbnail_{$v['id']}">
    <a class="thumbnail-link" href="{$vidUrl}"><img src="{$v['thumbnail']['url']}" height="{$v['thumbnail']['height']}" width="{$v['thumbnail']['width']}" /></a>
    <div class="vid-info">
        <p class="views small">{$views} views</p>
        <p class="duration small">{$duration}</p>
        <a href="{$vidUrl}" class="vid-link small">{$title}</a>
        <span class="vid-author small">{$v['author']}</span>
    </div>
</li>                
MARKUP;
            $markup[$vidType.'Show'] .= <<<MARKUP
<li class="video-show-markup" id="{$vt}_video_show_markup_{$v['id']}">
    <a href='javascript:void(null)' class='back-to-vids'>Back To Videos</a>
    <div class='video-show-header'>
        <a class='video-show-title' target='_blank' href='http://www.youtube.com/watch?v={$v['id']}'>{$v['title']}</a>
        <a class='video-show-author' target='_blank' href='http://www.youtube.com/user/{$v['author']}'>{$v['author']}</a>
    </div>    
    <div class='video-show-flash'>
        <yml:swf src="http://www.youtube.com/v/{$v['id']}?version=3" height="390" width="640"/>
    </div>
    <div class='video-show-info'> 
        <p class='video-show-views'>{$views} views</p>
        <p class='video-show-description'>{$v['description']}</p>
    </div> 
</li>
MARKUP;
            }
            $markup[$vidType] .= "</ul>";
        }
        return $markup;
    }
    function getVideoFeedData($url) {
        $videoFeedData = $this->yt->getVideoFeed($url);
        return $this->parseVideoFeedData($videoFeedData);
    }
     
    function parseVideoFeedData($data) {
        $response = null;
        $count = 0;
        foreach($data as $entry) {
            $response[$count]['title'] = $entry->getVideoTitle();
            $response[$count]['id'] = $entry->getVideoId();
            $authors = $entry->getAuthor();
            $response[$count]['author'] = $authors[0]->name->text;
            $response[$count]['description'] = $entry->getVideoDescription();
            $response[$count]['flashUrl'] = $entry->getFlashPlayerUrl();
            $response[$count]['duration'] = $entry->getVideoDuration();
            $response[$count]['views'] = $entry->getVideoViewCount();
            $response[$count]['rating'] = $entry->getVideoRatingInfo();
            $videoThumbnails = $entry->getVideoThumbnails();
            if(is_array($videoThumbnails) && count($videoThumbnails) > 0) {
                $response[$count]['thumbnail'] = array_shift($videoThumbnails);
            }else {
                $response[$count]['thumbnail'] = array();
            }
            $count++;
        }
        return $response;
    }

    function searchAndPrint($searchTerms = ''){
        $yt = new Zend_Gdata_YouTube();
        $yt->setMajorProtocolVersion(2);
        $query = $yt->newVideoQuery();
        $query->setOrderBy('relevance');
        $query->setSafeSearch('none');
        $query->setVideoQuery($searchTerms);

        // Note that we need to pass the version number to the query URL function
        // to ensure backward compatibility with version 1 of the API.
        $videoFeed = $yt->getVideoFeed($query->getQueryUrl(2));
        $this->printVideoFeed($videoFeed, 'Search results for: ' . $searchTerms);
    }

    function displayHTML($markup = array()) {
        $mostViewedMarkup = isset($markup['mostViewed']) ? $markup['mostViewed'] : '';
        $topFavMarkup     = isset($markup['topFav']) ? $markup['topFav'] : '';
        $topRatedMarkup   = isset($markup['topRated']) ? $markup['topRated'] : '';
        $trendVidMarkup   = isset($markup['trendVid']) ? $markup['trendVid'] : ''; 

        $mostViewedShowMarkup = isset($markup['mostViewedShow']) ? $markup['mostViewedShow'] : '';
        $topFavShowMarkup     = isset($markup['topFavShow']) ? $markup['topFavShow'] : '';
        $topRatedShowMarkup   = isset($markup['topRatedShow']) ? $markup['topRatedShow'] : '';
        $trendVidShowMarkup   = isset($markup['trendVidShow']) ? $markup['trendVidShow'] : ''; 

        $styles = $this->getStyles();
        $scripts = $this->getScripts();
        $htmlClasses = implode(" ", $this->getHTMLClasses());
        $bodyMarkup = <<<MARKUP
<div id="header"> 
    <div class="logo">
        <img src="http://www.ravikiranj.net/drupal/sites/all/hacks/yap/youtube/images/youtube-64X64.png" height="50" width="50" alt="Youtube Videos" />
        <h1>Youtube Videos</h1>
    </div>
<ul id="youtube-tabs">
    <li id="mostviewed-tab" class="selected tab"><a href="javascript:void(null);">Most Viewed</a></li>
    <li id="topfav-tab" class="tab"><a href="javascript:void(null);">Top Favorites</a></li>
    <li id="toprated-tab" class="tab"><a href="javascript:void(null);">Top Rated</a></li>
    <li id="trendvid-tab" class="tab"><a href="javascript:void(null);">Trending</a></li>
</ul>
</div>

<div id="content">
    <ul class="selected tab-content" id="mostviewed-content">{$mostViewedMarkup}</ul>
    <ul class="tab-content" id="topfav-content">{$topFavMarkup}</ul>
    <ul class="tab-content" id="toprated-content">{$topRatedMarkup}</ul>
    <ul class="tab-content" id="trendvid-content">{$trendVidMarkup}</ul>
</div>
<div id="video-show-content">
    <ul class="show-content" id="mostviewed-show-content">{$mostViewedShowMarkup}</ul>
    <ul class="show-content" id="topfav-show-content">{$topFavShowMarkup}</ul>
    <ul class="show-content" id="toprated-show-content">{$topRatedShowMarkup}</ul>
    <ul class="show-content" id="trendvid-show-content">{$trendVidShowMarkup}</ul>
</div>

MARKUP;
        $html = <<<HTML
<head>
   {$styles} 
</head>
<body>
    <div class="bd {$htmlClasses}" id="youtube-bd">
        {$bodyMarkup}        
    </div>
    {$scripts}
</body>    
HTML;
        echo $html;
    }
        
    function getScripts(){
        $youtubeFullViewJS = '';
        $youtubeFullViewJSFile = 'js/youtube-fullview-min.js';
        if(file_exists($youtubeFullViewJSFile)) {
            $youtubeFullViewJS = file_get_contents($youtubeFullViewJSFile);
        }
        $scripts = <<<SCRIPTS
<script type="text/javascript" src="http://yui.yahooapis.com/2.8.0/build/yahoo-dom-event/yahoo-dom-event.js"></script>
<!--<script type="text/javascript" src="js/youtube-fullview.js"></script>-->
<script type="text/javascript">
{$youtubeFullViewJS}
</script> 
SCRIPTS;
        return $scripts;
    }

    function getHTMLClasses() {
        $htmlClasses = array();        
        
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
        
            $uA = $_SERVER['HTTP_USER_AGENT'];
            if(is_array($uA)) {
                $uA = implode($uA);
            }
            if (strpos($uA, 'MSIE') !== false) {
            
                $htmlClasses[] = 'ua-ie';
                
                if (strpos($uA, 'MSIE 6') !== false){
                    $htmlClasses[] = 'ua-ie6';
                } else if (strpos($uA, 'MSIE 7') !== false){
                    $htmlClasses[] = 'ua-ie7';
                } else if (strpos($uA, 'MSIE 8') !== false){
                    $htmlClasses[] = 'ua-ie8';
                }
                
            } else if (strpos($uA, 'WebKit') !== false){
            
                $htmlClasses[] = 'ua-wk';
                if (strpos($uA, 'WebKit/419') !== false){
                    $htmlClasses[] = 'ua-wk419';
                } else if (strpos($uA, 'WebKit/522') !== false){
                    $htmlClasses[] = 'ua-wk522';
                }
            
            } else if (strpos($uA, 'Firefox') !== false){
            
                $htmlClasses[] = 'ua-ff';
                if (strpos($uA, 'Firefox/1.5') !== false){
                    $htmlClasses[] = 'ua-ff1_5';
                } else if (strpos($uA, 'Firefox/2') !== false){
                    $htmlClasses[] = 'ua-ff2';
                } else if (strpos($uA, 'Firefox/3') !== false){
                    $htmlClasses[] = 'ua-ff3';
                    //Check if it is 3.5+
                    if(preg_match('/Firefox\/3\.[5-9]/', $uA)) {
                        $htmlClasses[] = 'ua-ff-gt3_5';
                    }
                }
            } else if (strpos($uA, 'Opera') !== false) {
            
                $htmlClasses[] = 'ua-op';
                if (strpos($uA, 'Opera/9.5') !== false){
                    $htmlClasses[] = 'ua-op9_5';
                } else if (strpos($uA, 'Opera/9') !== false){
                    $htmlClasses[] = 'ua-op9';
                } else if (strpos($uA, 'Opera/8.5') !== false){
                    $htmlClasses[] = 'ua-op8_5';
                } else if (strpos($uA, 'Opera/8') !== false){
                    $htmlClasses[] = 'ua-op8';
                }
            }
            
            if (strpos($uA, 'Windows') !== false){
                $htmlClasses[] = 'ua-win';
            } else if (strpos($uA, 'Mac') !== false){
                $htmlClasses[] = 'ua-mac';
                if (strpos($uA, 'iPhone') !== false || strpos($uA, 'iPod') !== false){
                    $htmlClasses[] = 'ua-ip';
                }
            }
            
        }
       return $htmlClasses;    
    }

    function getStyles() {
        $youtubeFullViewCss = '';
        $youtubeFullViewCssFile = 'css/youtube-fullview.css';
        if(file_exists($youtubeFullViewCssFile)) {
            $youtubeFullViewCss = file_get_contents($youtubeFullViewCssFile);

        }
        $style = <<<STYLES
<style type="text/css">
{$youtubeFullViewCss}
</style>        
STYLES;
        return $style;
    }


    function __init() {
        $videoData = $this->prepareContent();
        $markup = $this->prepareMarkup($videoData);
        $this->displayHTML($markup);
    }
}

    $ytube = new YoutubeApp();
    $ytube->__init();
?>

