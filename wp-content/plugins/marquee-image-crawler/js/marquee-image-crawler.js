/* 
GNU General Public License v2 or later

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

License URI	: http://www.gnu.org/licenses/gpl-2.0.html
*/

function MarqueeImageCrawler(settings){
	if(!document.createElement) return;
	MarqueeImageCrawler.ar.push(settings);
	MarqueeImageCrawler.run(settings.uniqueid);
}

(function(){
	if(!document.createElement) return;
	if(typeof opera === 'undefined'){window.opera = false;}
	MarqueeImageCrawler.ar = [];

	document.write('<style type="text/css">.marquee{white-space:nowrap;overflow:hidden;visibility:hidden;}' +
		'#ImageCrawlerNoBorder{border:none!important;margin:0!important;}<\/style>');
	var c = 0, tTRE = [/^\s*$/, /^\s*/, /\s*$/, /[^\/]+$/],
	req1 = {'position': 'relative', 'overflow': 'hidden'}, defaultconfig = {
	style: {
		'margin': '0 auto'
	},
	direction: 'left',
	speed: 2,
	mouse: 'pause'
	}, dash, ie = false, oldie = 0, ie5 = false, iever = 0;
 
	if(!ie5){
		dash = /-(.)/g;
		function toHump(a, b){return b.toUpperCase();};
		String.prototype.encamel = function(){return this.replace(dash, toHump);};
	}

	if(ie && iever < 8){
		MarqueeImageCrawler.table = [];
		window.attachEvent('onload', function(){
			MarqueeImageCrawler.OK = true;
			var i = -1, limit = MarqueeImageCrawler.table.length;
			while(++i < limit)
			MarqueeImageCrawler.run(MarqueeImageCrawler.table[i]);
		});
	}

	function intable(el){
		while((el = el.parentNode))
		if(el.tagName && el.tagName.toLowerCase() === 'table')
		return true;
		return false;
	};

	MarqueeImageCrawler.run = function(id){
		if(ie && !MarqueeImageCrawler.OK && iever < 8 && intable(document.getElementById(id))){
			MarqueeImageCrawler.table.push(id);
			return;
		}
		if(!document.getElementById(id))
			setTimeout(function(){MarqueeImageCrawler.run(id);}, 300);
		else
			new Marq(c++, document.getElementById(id));
	}

	function trimTags(tag){
		var r = [], i = 0, e;
		while((e = tag.firstChild) && e.nodeType === 3 && tTRE[0].test(e.nodeValue))
			tag.removeChild(e);
		while((e = tag.lastChild) && e.nodeType === 3 && tTRE[0].test(e.nodeValue))
			tag.removeChild(e);
		if((e = tag.firstChild) && e.nodeType === 3)
			e.nodeValue = e.nodeValue.replace(tTRE[1], '');
		if((e = tag.lastChild) && e.nodeType === 3)
			e.nodeValue = e.nodeValue.replace(tTRE[2], '');
		while((e = tag.firstChild))
			r[i++] = tag.removeChild(e);
		return r;
	}

	function randthem(tag){
		var els = oldie? tag.all : tag.getElementsByTagName('*'), i = els.length, childels = [];
		while (--i > -1){
			if(els[i].parentNode === tag){
				childels.push(els[i]);
			}
		}
		childels.sort(function(){return 0.5 - Math.random();});
		i = childels.length;
		while (--i > -1){
			tag.appendChild(childels[i]);
		}
	}

	function Marq(c, tag){
		var p, u, s, a, ims, ic, i, marqContent, cObj = this;
		this.mq = MarqueeImageCrawler.ar[c];
		if(this.mq.random){
			if(tag.getElementsByTagName && tag.getElementsByTagName('tr').length === 1 && tag.childNodes.length === 1){
				randthem(tag.getElementsByTagName('tr')[0]);
			} else {
				randthem(tag);
			}
		}
		
	for (p in defaultconfig)
	if((this.mq.hasOwnProperty && !this.mq.hasOwnProperty(p)) || (!this.mq.hasOwnProperty && !this.mq[p]))
	this.mq[p] = defaultconfig[p];
	this.mq.direction = this.mq.persist && this.cookie.get(this.mq.uniqueid)? this.cookie.get(this.mq.uniqueid).split(':')[2] : this.mq.direction;
	this.mq.style.width = !this.mq.style.width || isNaN(parseInt(this.mq.style.width))? '100%' : this.mq.style.width;
	if(!tag.getElementsByTagName('img')[0])
		this.mq.style.height = !this.mq.style.height || isNaN(parseInt(this.mq.style.height))? tag.offsetHeight + 3 + 'px' : this.mq.style.height;
	else
		this.mq.style.height = !this.mq.style.height || isNaN(parseInt(this.mq.style.height))? 'auto' : this.mq.style.height;
	u = this.mq.style.width.split(/\d/);
	this.cw = this.mq.style.width? [parseInt(this.mq.style.width), u[u.length - 1]] : ['a'];
	marqContent = trimTags(tag);
	tag.className = tag.id = '';
	tag.removeAttribute('class', 0);
	tag.removeAttribute('id', 0);
	if(ie)
	tag.removeAttribute('className', 0);
	tag.appendChild(tag.cloneNode(false));
	tag.className = ['marquee', c].join('');
	tag.style.overflow = 'hidden';
	this.c = tag.firstChild;
	this.c.appendChild(this.c.cloneNode(false));
	this.c.style.visibility = 'hidden';
	a = [[req1, this.c.style], [this.mq.style, this.c.style]];
	for (i = a.length - 1; i > -1; --i)
	for (p in a[i][0])
	if((a[i][0].hasOwnProperty && a[i][0].hasOwnProperty(p)) || (!a[i][0].hasOwnProperty))
	a[i][1][p.encamel()] = a[i][0][p];
	this.m = this.c.firstChild;
	if(this.mq.mouse === 'pause'){
		this.c.onmouseover = function(){cObj.mq.stopped = true;};
		this.c.onmouseout = function(){cObj.mq.stopped = false;};
	}
	this.m.style.position = 'absolute';
	this.m.style.left = '-10000000px';
	this.m.style.whiteSpace = 'nowrap';
	if(ie5) this.c.firstChild.appendChild((this.m = document.createElement('nobr')));
	if(!this.mq.noAddedSpace)
	this.m.appendChild(document.createTextNode('\xa0'));
	for(i = 0; marqContent[i]; ++i)
		this.m.appendChild(marqContent[i]);
	if(ie5) this.m = this.c.firstChild;
		ims = this.m.getElementsByTagName('img');
	if(ims.length){
		for(ic = 0, i = 0; i < ims.length; ++i){
			ims[i].style.display = 'inline';
			if(!ims[i].alt && !this.mq.noAddedAlt){
				ims[i].alt = (tTRE[3].exec(ims[i].src)) || ('Image #' + [i + 1]);
			if(!ims[i].title){ims[i].title = '';}
		}
		ims[i].style.display = 'inline';
		ims[i].style.verticalAlign = ims[i].style.verticalAlign || 'top';
		if(typeof ims[i].complete === 'boolean' && ims[i].complete)
			ic++;
		else {
			ims[i].onload = ims[i].onerror = function(){
			if(++ic === ims.length)
				cObj.setup(c);
			};
		}
		if(ic === ims.length)
			this.setup(c);
		}
	}
		else this.setup(c)
	}

	Marq.prototype.setup = function(c){
		if(this.mq.setup) return;
		this.mq.setup = this;
		var s, w, cObj = this, exit = 10000;
		if(this.c.style.height === 'auto')
		this.c.style.height = this.m.offsetHeight + 4 + 'px';
		this.c.appendChild(this.m.cloneNode(true));
		this.m = [this.m, this.m.nextSibling];
		if(typeof this.mq.initcontent === 'function'){
			this.mq.initcontent.apply(this.mq, [this.m]);
		}
		if(this.mq.mouse === 'cursor driven'){
			this.r = this.mq.neutral || 16;
			this.sinc = this.mq.speed;
			this.c.onmousemove = function(e){cObj.mq.stopped = false; cObj.directspeed(e)};
			if(this.mq.moveatleast){
				this.mq.speed = this.mq.moveatleast;
			if(this.mq.savedirection){
			if(this.mq.savedirection === 'reverse'){
				this.c.onmouseout = function(e){
				if(cObj.contains(e)) return;
				cObj.mq.speed = cObj.mq.moveatleast;
				cObj.mq.direction = cObj.mq.direction === 'right'? 'left' : 'right';};     
			} else {
				this.mq.savedirection = this.mq.direction;
				this.c.onmouseout = function(e){
				if(cObj.contains(e)) return;
				cObj.mq.speed = cObj.mq.moveatleast;
				cObj.mq.direction = cObj.mq.savedirection;};     
			}
			} else
			this.c.onmouseout = function(e){if(!cObj.contains(e)) cObj.mq.speed = cObj.mq.moveatleast;};
		}
		else
			this.c.onmouseout = function(e){if(!cObj.contains(e)) cObj.slowdeath();};
		}
		this.w = this.m[0].offsetWidth;
		this.m[0].style.left = this.mq.persist && this.cookie.get(this.mq.uniqueid)? this.cookie.get(this.mq.uniqueid).split(':')[0] : 0;
		this.c.id = 'ImageCrawlerNoBorder';
		this.m[0].style.top = this.m[1].style.top = Math.floor((this.c.offsetHeight - this.m[0].offsetHeight) / 2 - oldie) + 'px';
		this.c.id = '';
		this.c.removeAttribute('id', 0);
		this.m[1].style.left = this.mq.persist && this.cookie.get(this.mq.uniqueid)? this.cookie.get(this.mq.uniqueid).split(':')[1] : this.w + 'px';
		s = this.mq.moveatleast? Math.max(this.mq.moveatleast, this.sinc) : (this.sinc || this.mq.speed);
		while(this.c.offsetWidth > this.w - s && --exit){
			w = isNaN(this.cw[0])? this.w - s : --this.cw[0];
			if(w < 1 || this.w < Math.max(1, s)){break;}
			this.c.style.width = isNaN(this.cw[0])? this.w - s + 'px' : --this.cw[0] + this.cw[1];
		}
		this.c.style.visibility = 'visible';
		this.runit();
	}

	Marq.prototype.slowdeath = function(){
		var cObj = this;
		if(this.mq.speed){
			this.mq.speed -= 1;
			this.timer = setTimeout(function(){cObj.slowdeath();}, 100);
		}
	}

	Marq.prototype.runit = function(){
		var cObj = this, d = this.mq.direction === 'right'? 1 : -1;
		if(this.mq.stopped || this.mq.stopMarquee){
			setTimeout(function(){cObj.runit();}, 300);
			return;
		}
		if(this.mq.mouse != 'cursor driven')
			this.mq.speed = Math.max(1, this.mq.speed);
		if(d * parseInt(this.m[0].style.left) >= this.w)
			this.m[0].style.left = parseInt(this.m[1].style.left) - d * this.w + 'px';
		if(d * parseInt(this.m[1].style.left) >= this.w)
			this.m[1].style.left = parseInt(this.m[0].style.left) - d * this.w + 'px';
		this.m[0].style.left = parseInt(this.m[0].style.left) + d * this.mq.speed + 'px';
		this.m[1].style.left = parseInt(this.m[1].style.left) + d * this.mq.speed + 'px';
		if(window.opera && this.mq.persist){
			this.cookie.set(this.mq.uniqueid, this.m[0].style.left + ':' + this.m[1].style.left + ':' + this.mq.direction);
		}
		setTimeout(function(){cObj.runit();}, 30 + (this.mq.addDelay || 0));
	}

	Marq.prototype.cookie = {
		set: function(n, v, d){
			if(d){var dt = new Date(); 
			dt.setDate(dt.getDate() + d);
			d = '; expires=' + dt.toGMTString();}
			document.cookie = n + '=' + escape(v) + (d || '') + '; path=/';
			},
			get: function(n){
				var c = document.cookie.match('(^|;)\x20*' + n + '=([^;]*)');
				return c? unescape(c[2]) : null;
			},
			kill: function(n){
				cook.set(n, '', -1);
			},
			killall: function(){
				var cookies = document.cookie.split(';'), i = cookies.length - 1;
				for (i; i > -1; --i){
					cook.kill(cookies[i].split('=')[0]);
				}
			}
	};

	Marq.prototype.directspeed = function(e){
		e = e || window.event;
		if(this.timer) clearTimeout(this.timer);
			var c = this.c, w = c.offsetWidth, l = c.offsetLeft, mp = (typeof e.pageX === 'number'?
		e.pageX : e.clientX + document.body.scrollLeft + document.documentElement.scrollLeft) - l,
		lb = (w - this.r) / 2, rb = (w + this.r) / 2;
		while((c = c.offsetParent)) mp -= c.offsetLeft;
			this.mq.direction = mp > rb? 'left' : 'right';
		this.mq.speed = Math.round((mp > rb? (mp - rb) : mp < lb? (lb - mp) : 0) / lb * this.sinc);
	}

	Marq.prototype.contains = function(e){
		if(e && e.relatedTarget){var c = e.relatedTarget; if(c === this.c) return true;
		while ((c = c.parentNode)) if(c === this.c) return true;}
		return false;
	}

	function resize(){
		for(var s, w, m, i = 0; i < MarqueeImageCrawler.ar.length; ++i){
			if(MarqueeImageCrawler.ar[i] && MarqueeImageCrawler.ar[i].setup){
				m = MarqueeImageCrawler.ar[i].setup;
				s = m.mq.moveatleast? Math.max(m.mq.moveatleast, m.sinc) : (m.sinc || m.mq.speed);
				m.c.style.width = m.mq.style.width;
				m.cw[0] = m.cw.length > 1? parseInt(m.mq.style.width) : 'a';
				while(m.c.offsetWidth > m.w - s){
				w = isNaN(m.cw[0])? m.w - s : --m.cw[0];
				if(w < 1){break;}
					m.c.style.width = isNaN(m.cw[0])? m.w - s + 'px' : --m.cw[0] + m.cw[1];
				}
			}
		}
	}

	function unload(){
		for(var m, i = 0; i < MarqueeImageCrawler.ar.length; ++i){
			if(MarqueeImageCrawler.ar[i] && MarqueeImageCrawler.ar[i].persist && MarqueeImageCrawler.ar[i].setup){
				m = MarqueeImageCrawler.ar[i].setup;
				m.cookie.set(m.mq.uniqueid, m.m[0].style.left + ':' + m.m[1].style.left + ':' + m.mq.direction);
			}
		}
	}

	if (window.addEventListener){
		window.addEventListener('resize', resize, false);
		window.addEventListener('unload', unload, false);
	}
	else if (window.attachEvent){
		window.attachEvent('onresize', resize);
		window.attachEvent('onunload', unload);
	}

})();