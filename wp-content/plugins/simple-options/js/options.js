jQuery(function($){
    
    // Find the starting tab
    var start = 0;
    if(window.location.hash != ''){
        console.log(window.location.hash);
        start = $('#simple-options-page .nav-tab-wrapper a[href="'+window.location.hash+'"]').index();
    }
    
    // Start by setting up the tabs
    $('#simple-options-page .nav-tab-wrapper a').click(function(){
        var $$ = $(this);
        var id = $$.attr('href').substr(1);
        $('.options-page-container').hide().filter('#page-'+id).show();
        $('#simple-options-page .nav-tab-wrapper a')
            .removeClass('nav-tab-active');
        $$.addClass('nav-tab-active');
        
        $('#simple-options-form').attr('action', window.location.href.split('#')[0] + $$.attr('href'));
    }).eq(start).click();
    
    $('.simple-options .type-taxonomy_select select, .simple-options .type-post_select select').each(function(){
        var $$ = $(this);
        var order;
        
        $$.chosen({
            disable_search_threshold: 6,
            allow_single_deselect: true
        });
        
        if($$.attr('data-order') != undefined){
            // Add an order field
            order = $('<ol></ol>').addClass('order').appendTo($$.parent());
            order.sortable();
            
            var orderHidden = $$.parent().find('input[type=hidden]');
            
            if(orderHidden.val() != ''){
                var currentOrder = orderHidden.val().split(',');
                for(var i = 0; i < currentOrder.length; i++){
                    // Skip items that dont exist
                    if($$.find('option[value="' + id + '"]') == undefined) continue;
                    
                    var id = currentOrder[i];
                    var text = $$.find('option[value="' + id + '"]').html();
                    var n = $('<li></li>')
                        .attr('data-id', id)
                        .attr('id', $$.attr('id') + '_sortable_' + id)
                        .html(text);
                    order.append(n);
                    order.sortable('refresh');
                }
            }
            
            $$.change(function(){
                // Find what's been added or removed
                var current = [];
                order.find('li').each(function(){
                    current.push(String($(this).attr('data-id')));
                });
                
                var newv = $$.val();
                if(newv == undefined) newv = [];

                var toAdd = newv.diff(current);
                var toRemove = current.diff(newv);

                for(var i = 0; i < toAdd.length; i++){
                    var id = toAdd[i];
                    var text = $$.find('option[value="' + id + '"]').html();
                    var n = $('<li></li>')
                        .attr('data-id', id)
                        .attr('id', $$.attr('id') + '_sortable_'+id)
                        .html(text);
                    order.append(n);
                    order.sortable('refresh');
                }
                
                for (var i = 0; i < toRemove.length; i++) {
                    var n = order.find('#' + $$.attr('id') + '_sortable_' + toRemove[i]);
                    n.remove().sortable('refresh');
                }
            });
            
            $('form#simple-options-form').submit(function(){
                var order = [];
                $$.parent().find('.order li').each(function(){
                    order.push($(this).attr('data-id'));
                });
                orderHidden.val(order.join(','));
            });
        }
    });
    
    $('.simple-options .type-select select').chosen({
        disable_search_threshold: 6,
        allow_single_deselect: true
    });
    
    // Now lets handle show_if and hide_if
    $('tr[data-show-if], tr[data-hide-if]').each(function(){
        var $$ = $(this);

        var cond, type;
        if($$.attr('data-show-if') != undefined){
            type = 'show'
            cond = $$.attr('data-show-if').split('=');
        }
        else {
            type = 'hide'
            cond = $$.attr('data-hide-if').split('=');
        } 
        
        $('*[name=' + cond[0] + ']').change(function(){
            var $f = $(this);

            var val;
            if($f.attr('type') == 'checkbox'){
                val = $f.is(':checked');
                cond[1] = cond[1] == 'true';
            }
            else val = $f.val();
            
            if(type == 'show'){
                if (val == cond[1]) $$.show();
                else $$.hide();
            }
            else{
                if (val == cond[1]) $$.hide();
                else $$.show();
            }
            
        }).change();
    });
})

Array.prototype.unique =
    function () {
        var a = [];
        var l = this.length;
        for (var i = 0; i < l; i++) {
            for (var j = i + 1; j < l; j++) {
                // If this[i] is found later in the array
                if (this[i] === this[j])
                    j = ++i;
            }
            a.push(this[i]);
        }
        return a;
    };

Array.prototype.diff =
    function () {
        var a1 = this;
        var a = a2 = null;
        var n = 0;
        while (n < arguments.length) {
            a = [];
            a2 = arguments[n];
            var l = a1.length;
            var l2 = a2.length;
            var diff = true;
            for (var i = 0; i < l; i++) {
                for (var j = 0; j < l2; j++) {
                    if (a1[i] === a2[j]) {
                        diff = false;
                        break;
                    }
                }
                diff ? a.push(a1[i]) : diff = true;
            }
            a1 = a;
            n++;
        }
        return a.unique();
    };