(function(window, $, page) {

	page.classes.LiveStream = function(options) {

		var o, internal, fn, handlers;

		o = $.extend({
			$trigger: null,
			popup_url: null,
			popup_specs: "width=800, height=600"
		}, options);

		internal = {
			name: "mod.LiveStream",
			$trigger: o.$trigger,
			popup_url: o.popup_url,
			popup: null
		};

		fn = {
			init: function() {
				internal.$trigger.click(handlers.trigger_click);
			},
			open: function() {
				internal.popup = window.open(internal.popup_url, "_blank", o.popup_specs);
			}
		};

		handlers = {
			trigger_click: function(e, d) {
				e.preventDefault();
				if (internal.popup && !internal.popup.closed) {
					internal.popup.focus();
				} else {
					fn.open();
				}
			}
		};

		fn.init();
		console.log(internal);
	};

}(this, this.jQuery, this.page));