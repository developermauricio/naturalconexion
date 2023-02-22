"use strict";(self.webpackChunk_wcAdmin_webpackJsonp=self.webpackChunk_wcAdmin_webpackJsonp||[]).push([[4882],{36316:function(e,t,n){n.r(t),n.d(t,{default:function(){return N}});var a=n(69307),r=n(67221),o=n(79205),l=n(65736),c=n(55609),i=n(14599),m=n(87818),s=n(9818),g=n(51455);const u=()=>{const{installedPlugins:e,activatingPlugins:t,activateInstalledPlugin:n}=(()=>{const{installedPlugins:e,activatingPlugins:t}=(0,s.useSelect)((e=>{const{getInstalledPlugins:t,getActivatingPlugins:n}=e(g.L);return{installedPlugins:t(),activatingPlugins:n()}}),[]),{activateInstalledPlugin:n}=(0,s.useDispatch)(g.L);return{installedPlugins:e,activatingPlugins:t,activateInstalledPlugin:n}})();return 0===e.length?null:(0,a.createElement)(m.NP,{header:(0,l.__)("Installed extensions","woocommerce")},e.map(((r,o)=>{return(0,a.createElement)(a.Fragment,{key:r.slug},(0,a.createElement)(m.o_,{icon:(0,a.createElement)(m.wq,{product:r.slug}),name:r.name,description:r.description,button:(s=r,"installed"===s.status?(0,a.createElement)(c.Button,{variant:"secondary",isBusy:t.includes(s.slug),disabled:t.includes(s.slug),onClick:()=>{(0,i.recordEvent)("marketing_installed_activate",{name:s.name}),n(s.slug)}},(0,l.__)("Activate","woocommerce")):"activated"===s.status?(0,a.createElement)(c.Button,{variant:"primary",href:s.settingsUrl,onClick:()=>{(0,i.recordEvent)("marketing_installed_finish_setup",{name:s.name})}},(0,l.__)("Finish setup","woocommerce")):"configured"===s.status?(0,a.createElement)(c.Button,{variant:"secondary",href:s.dashboardUrl||s.settingsUrl,onClick:()=>{(0,i.recordEvent)("marketing_installed_options",{name:s.name,link:"manage"})}},(0,l.__)("Manage","woocommerce")):void 0)}),o!==e.length-1&&(0,a.createElement)(m.xx,null));var s})))};var d=n(62907),p=n(70444),_=(0,a.createElement)(p.SVG,{xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 24 24"},(0,a.createElement)(p.Path,{d:"M3.445 16.505a.75.75 0 001.06.05l5.005-4.55 4.024 3.521 4.716-4.715V14h1.5V8.25H14v1.5h3.19l-3.724 3.723L9.49 9.995l-5.995 5.45a.75.75 0 00-.05 1.06z"})),k=n(86020),E=n(92819),w=n(85883);const v="marketing",h=e=>(0,E.uniqBy)((0,E.flatMapDeep)(e,(e=>e.subcategories)),(e=>e.slug)).map((e=>({name:e.slug,title:e.name}))),P=()=>{const{isLoading:e,plugins:t}=(0,s.useSelect)((e=>{const{getRecommendedPlugins:t,isResolving:n}=e(g.L);return{isLoading:n("getRecommendedPlugins",[v]),plugins:t(v)}}),[v]);return(0,a.createElement)(m.NP,{className:"woocommerce-marketing-discover-tools-card",header:(0,l.__)("Discover more marketing tools","woocommerce")},e?(0,a.createElement)(m.eW,null,(0,a.createElement)(k.Spinner,null)):0===t.length?(0,a.createElement)(m.eW,{className:"woocommerce-marketing-discover-tools-card-body-empty-content"},(0,a.createElement)(d.Z,{icon:_,size:32}),(0,a.createElement)("div",null,(0,l.__)("Continue to reach the right audiences and promote your products in ways that matter to them with our range of marketing solutions.","woocommerce")),(0,a.createElement)(c.Button,{variant:"tertiary",href:"https://woocommerce.com/product-category/woocommerce-extensions/marketing-extensions/",onClick:()=>{(0,i.recordEvent)("marketing_explore_more_extensions")}},(0,l.__)("Explore more marketing extensions","woocommerce"))):(0,a.createElement)(c.TabPanel,{tabs:h(t)},(e=>{const n=t.filter((t=>t.subcategories.some((t=>t.slug===e.name))));return(0,a.createElement)(a.Fragment,null,(0,a.createElement)(m.xx,null),(e=>e.map(((t,n)=>(0,a.createElement)(a.Fragment,{key:t.product},(0,a.createElement)(m.o_,{icon:(0,a.createElement)("img",{src:t.icon,alt:t.title}),name:t.title,pills:t.tags.map((e=>(0,a.createElement)(k.Pill,{key:e.slug},e.name))),description:t.description,button:(0,a.createElement)(c.Button,{variant:"secondary",href:(0,w.C)(t.url),onClick:()=>{(0,i.recordEvent)("marketing_recommended_extension",{name:t.title})}},(0,l.__)("Get started","woocommerce"))}),n!==e.length-1&&(0,a.createElement)(m.xx,null)))))(n))})))},y=()=>(0,a.createElement)("div",{role:"progressbar",className:"woocommerce-marketing-learn-marketing-card__post"},(0,a.createElement)("div",{className:"woocommerce-marketing-learn-marketing-card__post-img woocommerce-marketing-learn-marketing-card__post-img--placeholder"}),(0,a.createElement)("div",{className:"woocommerce-marketing-learn-marketing-card__post-title woocommerce-marketing-learn-marketing-card__post-title--placeholder"}),(0,a.createElement)("div",{className:"woocommerce-marketing-learn-marketing-card__post-description woocommerce-marketing-learn-marketing-card__post-description--placeholder"})),b=e=>{let{post:t}=e;return(0,a.createElement)("a",{className:"woocommerce-marketing-learn-marketing-card__post",href:t.link,target:"_blank",rel:"noopener noreferrer",onClick:()=>{(0,i.recordEvent)("marketing_knowledge_article",{title:t.title})}},(0,a.createElement)("div",{className:"woocommerce-marketing-learn-marketing-card__post-img"},t.image&&(0,a.createElement)("img",{src:t.image,alt:""})),(0,a.createElement)("div",{className:"woocommerce-marketing-learn-marketing-card__post-title"},t.title),(0,a.createElement)("div",{className:"woocommerce-marketing-learn-marketing-card__post-description"},(0,l.sprintf)((0,l.__)("By %s","woocommerce"),t.author_name),t.author_avatar&&(0,a.createElement)("img",{src:t.author_avatar.replace("s=96","s=32"),alt:""})))},f=()=>{const[e,t]=(0,a.useState)(1),{isLoading:n,error:r,posts:o}=(e=>(0,s.useSelect)((t=>{const{getBlogPosts:n,getBlogPostsError:a,isResolving:r}=t(g.L);return{isLoading:r("getBlogPosts",[e]),error:a(e),posts:n(e)}}),[e]))("marketing");return(0,a.createElement)(m.NP,{initialCollapsed:!0,className:"woocommerce-marketing-learn-marketing-card",header:(0,l.__)("Learn about marketing a store","woocommerce"),footer:n?(0,a.createElement)("div",{role:"progressbar",className:"woocommerce-marketing-learn-marketing-card__footer--placeholder"}):r||!o||0===o.length?null:(0,a.createElement)(k.Pagination,{showPagePicker:!1,showPerPagePicker:!1,page:e,perPage:2,total:o.length,onPageChange:e=>{t(e)}})},(0,a.createElement)("div",{className:"woocommerce-marketing-learn-marketing-card__body"},n?[...Array(2).keys()].map((e=>(0,a.createElement)(y,{key:e}))):r?(0,a.createElement)(k.EmptyContent,{title:(0,l.__)("Oops, our posts aren't loading right now","woocommerce"),message:(0,a.createElement)(m.WL,null),illustration:"",actionLabel:""}):0===o.length?(0,a.createElement)(k.EmptyContent,{title:(0,l.__)("No posts yet","woocommerce"),message:(0,a.createElement)(m.WL,null),illustration:"",actionLabel:""}):o.slice(2*(e-1),2*e).map(((e,t)=>(0,a.createElement)(b,{key:t,post:e})))))};n(77975);const N=()=>{const{currentUserCan:e}=(0,r.useUser)(),t=(0,o.O3)("allowMarketplaceSuggestions",!1)&&e("install_plugins");return(0,a.createElement)("div",{className:"woocommerce-marketing-overview-multichannel"},(0,a.createElement)(u,null),t&&(0,a.createElement)(P,null),(0,a.createElement)(f,null))}}}]);