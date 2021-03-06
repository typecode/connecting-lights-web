(function(window, $, page) {

	function shuffle(array) {
		var i, j, tempi, tempj;
		i = array.length;
		if (i === 0) {
			return false;
		}
		while (--i) {
			j = Math.floor(Math.random() * (i + 1));
			tempi = array[i];
			tempj = array[j];
			array[i] = tempj;
			array[j] = tempi;
		}
	}

	page.classes.SendMessage = function(options) {

		var o, internal, fn, handlers, colorutil = page.classes.colorutil;

		o = $.extend({
			app: null,
			is_live_stream: false,
			is_mobile: false,
			$e: null,
			selector: "",
			$trigger: null,
			use_color_picker: (function() {
				// only use color picker if canvas is supported
				var canvas = document.createElement("canvas");
				return !!(canvas.getContext && canvas.getContext("2d"));
			}()),
			color_picker_src: "",
			bg_desaturation: 0.6,
			prompts: ($.isArray(page.classes.prompts) ? page.classes.prompts : []),
			service_url: ""
		}, options);

		internal = {
			name: "mod.SendMessage",
			$e: (o.$e ? o.$e : $(o.selector)),
			$trigger: o.$trigger,
			is_mobile: o.is_mobile, // refers to phones only
			is_touch: null, // refers to any touch device (including tablets)
			overlay: null,
			merlin: null,
			colorpicker: null,
			prompts: o.prompts,
			prompts_index: null
		};

		fn = {
			init: function() {
				// initializing the color picker as early as possible to
				// decrease lag when the color picker image is still loading
				//fn.init_color_picker();

				internal.is_touch = (("ontouchstart" in window) || (window.DocumentTouch && document instanceof DocumentTouch));

				internal.overlay = new NI.Overlay({
					flavor: "merlin-overlay",
					autoflush: false,
					closeBtn: true,
					isTouchDevice: internal.is_touch,
					onOpen: function() {
						o.app.events.trigger("overlay:opened");
					},
					onClose: function() {
						o.app.events.trigger("overlay:closed");
					}
				});

				internal.overlay.setBody(internal.$e.detach());

				internal.$trigger.click(handlers.trigger_click);

				if (!$.isArray(internal.prompts) || internal.prompts.length === 0) {
					console.warn("Missing prompts for messages");
				} else {
					shuffle(internal.prompts);
					internal.prompts_index = 0;
				}
			},
			init_color_picker: function($colorpicker) {
				if (o.use_color_picker) {
					internal.colorpicker = new page.classes.ColorPicker({
						$e: $colorpicker,
						src: o.color_picker_src
					});
				} else {
					$colorpicker.hide();
				}
			},
			set_prompt: function() {
				var prompt = internal.prompts[internal.prompts_index];
				internal.merlin.internal.steps["compose"].fields["message[message]"].component.set_val(prompt);
			},
			get_bg_css: function(r, g, b) {
				var hsv;

				// the background color is a desaturated version
				// of the rgb color that the user has picked

				hsv = colorutil.rgbToHSV(r, g, b);

				hsv.s = hsv.s * o.bg_desaturation;

				return colorutil.rgbToString(colorutil.hsvToRGB(hsv));
			}
		};

		handlers = {
			trigger_click: function(e, d) {
				e.preventDefault();
				if (! internal.is_touch ) {
					internal.overlay.open();
				}
			},
			load_prompt_click: function(e, d) {
				e.preventDefault();
				if (typeof internal.prompts_index === "number" && internal.prompts.length) {
					internal.prompts_index += 1;
					if (internal.prompts_index > internal.prompts.length - 1) {
						internal.prompts_index = 0;
					}
					fn.set_prompt();
				}
			},
			color_picked: function(e, d) {
				var merlin, r, g, b;

				merlin = internal.merlin;
				r = d.r;
				g = d.g;
				b = d.b;

				merlin.set_val("message[red]", r);
				merlin.set_val("message[green]", g);
				merlin.set_val("message[blue]", b);

				e.data.container.css({
					"background-color": fn.get_bg_css(r, g, b)
				});
			}
		};

		internal.merlin = new NI.Merlin({
			name: internal.name + " Merlin",
			$e: internal.$e,
			controls: {
				prev: ".prev",
				next: ".next"
			},
			extensions: {
				data: new NI.MerlinData({
					uri: o.service_url,
					data: (function() {
						var d = {
							"message[message]": "",
							"message[red]": null,
							"message[green]": null,
							"message[blue]": null
						};
						if (o.is_live_stream) {

							// if we are in live stream mode,
							// attach the geo coords pointing to Steel Rigg

							d["message[latitude]"] = "54.990021";
							d["message[longitude]"] = "-2.603127";
						}
						return d;
					}())
				})
			},
			first_step: "info",
			steps: {
				"info": {
					selector: ".send-message-info",
					next: "compose"
				},
				"compose": {
					selector: ".send-message-compose",
					// next: internal.is_mobile ? "geo" : "dispatch",
					next: "dispatch",
					fields: {
						"message[message]": {
							selector: "textarea[name=m]",
							options: {
								extensions: {
									Validator: {
										validators: ["required", "maxlen=100"]
									},
									Counter: {
										max: 100
									}
								},
								handlers: {
									focus: function(e) {
										// old code for removing trailing ellipsis
										// in a prompt on focus

										/*var field, val;
										field = e.data.me;
										val = field.get_val();
										if (val.substring(val.length - 3) === "...") {
											field.set_val(val.substring(0, val.length - 3) + " ");
										}*/
										$('.control-bar').css('position','absolute');
									},
									blur: function(e) {
										$('.control-bar').css('position','fixed');
									}
								}
							}
						}
					},
					init: function(me) {
						var current_step = me.internal.current_step,
						$colorpicker = current_step.$e.find(".color-picker");

						me.extensions.data.init(me);

						if (internal.is_touch) {
							$colorpicker.on("color:picked", {container: $("body")}, handlers.color_picked);
						} else {
							$colorpicker.on("color:picked", {container: current_step.$e}, handlers.color_picked);
						}

						fn.init_color_picker($colorpicker);

						current_step.$e.find(".load-prompt").on("click", handlers.load_prompt_click);
					},
					visible: function(me) {

						if (internal.colorpicker) {
							internal.colorpicker.reset();
						}

						fn.set_prompt();

						if (internal.is_touch) {
							$("html, body").animate({ scrollTop: 0 }, "slow");
						}

					},
					finish: function(me) {
						me.extensions.data.collect_fields(me);
						
						if (internal.is_touch) {
							$("body").css('background','rgb(34,34,34)');
						}
						
					}
				},
				"geo": {
					selector: ".send-message-geo",
					prev: "compose",
					next: "dispatch",
					init: function(me) {

					}
				},
				"dispatch": {
					selector: ".step.dispatch",
					visible: function(me) {
						me.extensions.data.post_data(function(d) {
							me.show_step("thank-you");
						});
					}
				},
				"thank-you": {
					selector: ".step.thank-you",
					visible: function(me) {
						window.setTimeout(function() {
							internal.overlay.close();
							if (internal.is_touch) {
								location.reload();
							} else {
								me.show_step("compose");
							}
						}, 3000);
					}
				}
			}
		});

		fn.init();
		console.log(internal);
	};

}(this, this.jQuery, this.page));