
shopinterest.modules.time_countdown = function() {
    var module_name = 'time_countdown';
    var utils = shopinterest.common.utils;
    var substitute = utils.substitute;
    var id = utils.getModuleId(module_name);
    var container = null;
    var _this = this;
    var seconds = 0;
    var update_interval = 1000;
    var callback = null;
    var interval_handler = null;
    
    var countdown = function() {
        var times = [0, 0, 0, 0];
        if(seconds > 0){
            times[0] = Math.floor(seconds / (60*60*24)); // day
            times[1] = Math.floor((seconds % (60*60*24)) / (60*60)); // hour
            times[2] = Math.floor((seconds % (60*60)) / 60); // min
            times[3] = Math.floor(seconds % (60)); // sec
        } else {
            if(interval_handler) {
                clearInterval(interval_handler);
            }
            callback();
        }
        container.find("#"+id+"_day").text(times[0]);
        container.find("#"+id+"_hour").text(times[1]);
        container.find("#"+id+"_min").text(times[2]);
        container.find("#"+id+"_sec").text(times[3]);
        seconds = seconds - update_interval / 1000;
    };

    var str2date = function(str) {
        var reggie = /(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/;
        var dateArray = reggie.exec(str);
        var dateObject = new Date(
            (+dateArray[1]),
            (+dateArray[2])-1, // Careful, month starts at 0!
            (+dateArray[3]),
            (+dateArray[4]),
            (+dateArray[5]),
            (+dateArray[6])
        );
        return dateObject;
    };
    
    _this.render = function(tgt, _time_end, update, cb, _time_start) {
        update_interval = update;
        callback = cb;
        var template = shopinterest.templates.time_countdown;
        var html = substitute(template, {id: id}); 
        tgt.html(html);
        container = $('#'+id);
        var time_end = str2date(_time_end).getTime()/1000;
        if(_time_start) {
            seconds = time_end - (str2date(_time_start).getTime() / 1000);
        } else {
            seconds = time_end - (new Date().getTime() / 1000);
        }
        countdown();
        interval_handler = setInterval(countdown, update_interval);
    };

};
