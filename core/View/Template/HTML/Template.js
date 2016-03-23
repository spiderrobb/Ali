var App = {
	initialized: false,
	scripts: [],
	styles: [],
	init: function(args, callback) {
		// checking args
		args = args || {};
		args['scripts'] = args['scripts'] || [];
		args['styles'] = args['styles'] || [];
		args['calls'] = args['calls'] || '';
		callback = callback || function(){};
		// initialize the scrips 
		if (!App.initialized) {
			for(i in document.scripts){
				if (typeof document.scripts[i]['src'] != 'undefined'){
					if (document.scripts[i].src != '') {
						App.scripts.push(document.scripts[i].getAttribute('src'));
					}
				}
			}
			var links = document.getElementsByTagName("link");
			for(var i = 0; i < links.length; i++) {
				App.styles.push(links[i].getAttribute('href'));
			}
			App.initialized = true;
		}
		// loading any scripts
		App.loadStyles(args.styles, function(){
			App.loadScripts(args.scripts, function() {
				App.runScript(args.calls, callback);
			});
		});
	},
	loadScripts: function(scripts, callback){
		if(typeof scripts == 'string'){
			scripts = [scripts];
		}
		callback = callback || function(){};
		if(scripts.length == 0){
			callback();
			return;
		}
		for (var i=0; i<App.scripts.length; i++){
			if (scripts[0] == App.scripts[i]) {
				scripts.shift();
				App.loadScripts(scripts, callback);
				return;
			}
		}
		var s = document.createElement('script');
		s.async = true;
		s.type = "text/javascript";
		s.src = scripts[0];
		s.onload = function() {
			App.scripts.push(scripts.shift());
			App.loadScripts(scripts, callback);
		};
		document.body.appendChild(s);
	},
	loadStyles: function(styles,callback){
		if(typeof styles == 'string'){
			styles = [styles];
		}
		callback = callback || function(){};
		if(styles.length == 0){
			callback();
			return true;
		}
		for (var i=0; i<App.styles.length; i++) {
			if (styles[0] == App.styles[i]) {
				styles.shift();
				App.loadStyles(styles,callback);
				return;
			}
		}
		var l = document.createElement('link');
		l.rel = 'stylesheet';
		l.type = 'text/css';
		l.href = styles[0];
		l.async = true;
		l.onload = function() {
			App.styles.push(styles.shift());
			App.loadStyles(styles,callback);
		};
		document.body.appendChild(l);
	},
	runScript: function(calls,callback){
		if(typeof calls != 'string') {
			return;
		}
		if (calls != '') {
			var s = document.createElement('script');
			s.type = 'text/javascript';
			s.innerHTML = calls;
			document.body.appendChild(s);
		}
		callback();
	}
};