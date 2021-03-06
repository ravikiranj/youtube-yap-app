/* Creates a namespace for "youtube"*/
YAHOO.namespace("youtube");

YAHOO.youtube = function(){
    var YUE = YAHOO.util.Event;
    var YUD = YAHOO.util.Dom;
    var selId = '';
    function __init() {
        /*Setup Click handlers*/
        YUE.addListener("youtube-bd", "click", _handleClick);
    }
    function _handleClick(e) {
        var targ = YUE.getTarget(e);
        var target = targ;
        /*Find a target element such as A or INPUT or BUTTON*/
        var tagName = targ.tagName.toUpperCase();
        if(tagName != 'A' && tagName != 'INPUT' && tagName != 'BUTTON' && tagName != 'IMG') {
            return;
        }
        /*Find an ancestor whoose className is not empty*/
        if(!targ.className) {
            while(target){
                target = target.parentNode;
                if(target.className){ 
                    targ = target;
                    break;
                }
            }
            /*If you can't still find anything, return*/
            if(!targ.className) {
                return;
            }
        }
        /*Process for various events based on className*/
        if(YUD.hasClass(targ, "tab")) {
            switchTabContent(targ);
            YUE.preventDefault(e);
        }else if(YUD.hasClass(targ, "thumbnail-link") || YUD.hasClass(targ, "vid-link")) {
            showVideo(targ);
            YUE.preventDefault(e);
        }else if(YUD.hasClass(targ, "back-to-vids")){
            backToVideos(targ);
            YUE.preventDefault(e);
        }
    }
    function switchTabContent(targ) {
        /* Remove any video shows if present and show content*/
        backToVideos();

        /*Switch Tabs*/
        var yTab = YUD.get('youtube-tabs');
        var selectedTab = YUD.getChildrenBy(yTab, function(el){
                                                    if(YUD.hasClass(el, "selected")){return true;}
                                                    return false;
                                                               })[0];
        if(selectedTab) {
            YUD.removeClass(selectedTab, 'selected');
            YUD.addClass(targ, "selected");
        }

        /*Switch Tab Content*/
        var content = YUD.get('content');
        var selectedTabContent = YUD.getChildrenBy(content, function(el){
                                                    if(YUD.hasClass(el, "selected")){return true;}
                                                    return false;
                                                               })[0];
        if(selectedTabContent) {
            YUD.removeClass(selectedTabContent, 'selected');
            var newTabContentId = targ.id.split('-')[0]+'-content';
            var newTabContent = YUD.get(newTabContentId);
            YUD.addClass(newTabContentId, 'selected');
        }
    }
    function backToVideos() {
        /* Remove Video show elements */
        if(selId) {
            YUD.setStyle(YUD.get(selId), 'display', 'none');
            selId = '';
        }

        /* Restore videos */
        var content = YUD.get('content');
        YUD.setStyle(content, 'display', 'block');
    }
    function showVideo(targ) {
        //Hide Content
        var content = YUD.get('content');
        YUD.setStyle(content, 'display', 'none');

        //Prepare markup for new video
        var vid = YUD.getAncestorByClassName(targ, "vid-thumbnail");
        if(!vid){
            return;
        }
        var vt = vid.id.split('_').shift();
        var id = vid.id.split('_').pop();
        var videoId = vt+'_video_show_markup_'+id;
        selId = videoId;
        var selVid = YUD.get(videoId);
        YUD.setStyle(selVid, 'display', 'block');
    }
    /*Call __init*/
    __init();
}();
