"use strict";
function main($userAgent) {
    if (typeof jQuery == 'undefined' || typeof jQuery.noConflict != 'function') {
        return;
    }

    jQuery.noConflict();

    var $ = jQuery;

    var width;
    var height;
    var multiple = 5;

    $(function() {
        // from
        $("#start_date").datepicker({
            changeYear : true,
            changeMonth : true,
            dateFormat : 'yy-mm-dd',
            showButtonPanel: true,
        });

        // to
        $("#end_date").datepicker({
            changeYear : true,
            changeMonth : true,
            dateFormat : 'yy-mm-dd',
            showButtonPanel: true,
        });

        // 一行置きに背景色を変える
        $("table#summary-list tr:even").addClass('even-row');

        // 画像拡大
        $(".thumbnail").hover(
            function(){
                var $this = $(this);
                width = $this.width();
                height = $this.height();
                $this.parent().parent().css("z-index", 2);
                if($userAgent.search(/safari/i) != -1 && $userAgent.search(/chrome/i) == -1) {
                	// Safariの場合
                	$this.parent().parent().css("position", "relative");
                } else {
                	// Safari以外
                	$this.parent().parent().css("position", "absolute");
                }
                
                if(width > 60) {
                    width = 60;
                }
                if(height > 45) {
                    height = 45;
                }
                $this.stop().animate({ 
                    marginTop:  '-200px', 
                    marginLeft: '-200px', 
                    height: height+height*multiple +'px', 
                    width:    width+width*multiple +'px', 
                }, 300);
            },
            function(){
                var $this = $(this);
                $this.stop().animate({ 
                	marginTop:  0, 
                	marginLeft: 0, 
                    height: height+'px', 
                    width:    width+'px' 
                }, 300, function() {
                    $this.parent().parent().css("z-index", 1);
                    $this.parent().parent().css("position", "relative");
                });
            }
        );
    });
}